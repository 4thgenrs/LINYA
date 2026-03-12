<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once __DIR__ . '/../linya_db/config.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_name'])) {
    http_response_code(401);
    echo json_encode(['success'=>false,'error'=>'Unauthorized']);
    exit;
}

$user_id = intval($_SESSION['user_id']);
$user_name = $_SESSION['user_name'];
$office = isset($_SESSION['office_id']) ? intval($_SESSION['office_id']) : (isset($_GET['office']) ? intval($_GET['office']) : 0);

$response = [
    'success' => true,
    'user' => [
        'user_id' => $user_id,
        'name' => $user_name,
        'office' => $office,
        // account_office: the `office` value stored on the accounts table (may be NULL/0)
        'account_office' => 0,
        'office_description' => null,
        'office_code' => null
    ],
    'activity' => [],
    'office' => [
        'office_id' => $office,
        'office_description' => null,
        'office_code' => null,
        'windows' => [],
        'pending' => []
    ]
];

try {
    // My Activity (finished_queues processed by this staff)
    $stmt = $conn->prepare("SELECT f.finished_id, f.original_queue_id, f.office_id, f.window_id, f.processed_by, f.finished_at, f.created_at, f.status, f.services, f.customer_name, f.customer_phone, f.customer_email, q.queue_code AS live_queue_code, COALESCE(o_num.office_code, o_name.office_code) AS resolved_office_code FROM finished_queues f LEFT JOIN queues q ON q.queue_id = f.original_queue_id LEFT JOIN offices o_num ON o_num.office_id = f.office_id LEFT JOIN offices o_name ON o_name.office_description = CAST(f.office_id AS CHAR) WHERE f.processed_by = ? ORDER BY f.finished_at DESC LIMIT 500");
    if ($stmt) {
        $stmt->bind_param('s', $user_name);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($r = $res->fetch_assoc()) {
            $queueCode = isset($r['live_queue_code']) ? trim((string)$r['live_queue_code']) : '';
            if ($queueCode === '') {
                $officeCode = isset($r['resolved_office_code']) ? trim((string)$r['resolved_office_code']) : '';
                $originalQueueId = isset($r['original_queue_id']) ? intval($r['original_queue_id']) : 0;
                if ($officeCode !== '' && $originalQueueId > 0) {
                    $queueCode = $officeCode . '-' . str_pad((string)$originalQueueId, 3, '0', STR_PAD_LEFT);
                }
            }
            $r['queue_code'] = $queueCode;
            unset($r['live_queue_code'], $r['resolved_office_code']);
            $response['activity'][] = $r;
        }
        $stmt->close();
    }

    // load office description/code for the staff's office (if any)
    if ($office > 0) {
        $ostmt = $conn->prepare("SELECT office_description, office_code FROM offices WHERE office_id = ? LIMIT 1");
        if ($ostmt) {
            $ostmt->bind_param('i', $office);
            $ostmt->execute();
            $ores = $ostmt->get_result();
            if ($orow = $ores->fetch_assoc()) {
                $response['user']['office_description'] = $orow['office_description'];
                $response['user']['office_code'] = $orow['office_code'];
                $response['office']['office_description'] = $orow['office_description'];
                $response['office']['office_code'] = $orow['office_code'];
            }
            $ostmt->close();
        }
    }

    // Office windows and ongoing queues (only for staff's office)
    if ($office > 0) {
        // load windows for office (include disabled so frontend can show state)
        $wstmt = $conn->prepare("SELECT window_id, window_no, assigned_staff_id, enabled FROM office_windows WHERE office_id = ? ORDER BY window_no ASC");
        if ($wstmt) {
            $wstmt->bind_param('i', $office);
            $wstmt->execute();
            $wres = $wstmt->get_result();
            $windows = [];
            while ($w = $wres->fetch_assoc()) $windows[] = $w;
            $wstmt->close();
        } else {
            $windows = [];
        }

        // prepare service stmt
        $svcStmt = $conn->prepare("SELECT s.service_description, qs.soft_copy FROM queue_services qs JOIN services s ON qs.service_id = s.service_id WHERE qs.queue_id = ?");
        // prefer an exact PK match first, fallback to matching by window_no but only within same office
        $qExact = $conn->prepare("SELECT queue_id, queue_code, customer_name, customer_email, status, office_id, window_id FROM queues WHERE window_id = ? AND status = 'ongoing' LIMIT 1");
        $qFallback = $conn->prepare("SELECT queue_id, queue_code, customer_name, customer_email, status, office_id, window_id FROM queues WHERE window_id = ? AND office_id = ? AND status = 'ongoing' LIMIT 1");

        foreach ($windows as $w) {
            $window_id = intval($w['window_id']);
            $window_no = intval($w['window_no']);
            $current = null;

            // 1) exact PK match
            if ($qExact) {
                $qExact->bind_param('i', $window_id);
                $qExact->execute();
                $qres = $qExact->get_result();
                if ($qres) {
                    $current = $qres->fetch_assoc() ?: null;
                    $qres->free();
                }
            }

            // 2) fallback: legacy rows where queues.window_id stored window_no but ensure same office
            if (!$current && $qFallback && $window_no > 0) {
                $qFallback->bind_param('ii', $window_no, $office);
                $qFallback->execute();
                $qres2 = $qFallback->get_result();
                if ($qres2) {
                    $tmp = $qres2->fetch_assoc() ?: null;
                    if ($tmp) $current = $tmp;
                    $qres2->free();
                }
            }

            if ($current && $svcStmt) {
                $services = [];
                $qid = intval($current['queue_id']);
                $svcStmt->bind_param('i', $qid);
                $svcStmt->execute();
                $sres = $svcStmt->get_result();
                while ($s = $sres->fetch_assoc()) {
                    $services[] = ['service_description'=>$s['service_description'],'soft_copy'=> (isset($s['soft_copy']) && intval($s['soft_copy'])===1)?1:0];
                }
                $current['services'] = $services;
            }

            $response['office']['windows'][] = [
                'window_id'=>$window_id,
                'window_no'=>intval($w['window_no']),
                'assigned_staff_id'=> $w['assigned_staff_id'] ? intval($w['assigned_staff_id']) : null,
                'enabled'=> intval($w['enabled'])===1 ? 1 : 0,
                'current_queue'=>$current
            ];
        }
        if ($qExact) $qExact->close();
        if ($qFallback) $qFallback->close();
        if ($svcStmt) $svcStmt->close();

        // pending queues for this office
        $pstmt = $conn->prepare("SELECT q.queue_id, q.queue_code, q.customer_name FROM queues q WHERE office_id = ? AND status = 'pending' ORDER BY created_at ASC LIMIT 200");
        if ($pstmt) {
            $pstmt->bind_param('i', $office);
            $pstmt->execute();
            $pres = $pstmt->get_result();
            while ($pq = $pres->fetch_assoc()) {
                // attach services
                $services = [];
                $svcStmt2 = $conn->prepare("SELECT s.service_description, qs.soft_copy FROM queue_services qs JOIN services s ON qs.service_id = s.service_id WHERE qs.queue_id = ?");
                if ($svcStmt2) {
                    $pid = intval($pq['queue_id']);
                    $svcStmt2->bind_param('i', $pid);
                    $svcStmt2->execute();
                    $sres = $svcStmt2->get_result();
                    while ($s = $sres->fetch_assoc()) $services[] = ['service_description'=>$s['service_description'],'soft_copy'=> (isset($s['soft_copy']) && intval($s['soft_copy'])===1)?1:0];
                    $svcStmt2->close();
                }
                $pq['services'] = $services;
                $response['office']['pending'][] = $pq;
            }
            $pstmt->close();
        }
    }

    // Determine if the current staff user is assigned to a window (for allowing window control)
    $response['user']['assigned_window_id'] = null;
    $response['user']['assigned_window_no'] = null;
    $response['user']['assigned_office_id'] = null;
    // include account.office in the API so client and server use same source of truth
    $accountOffice = 0;
    $acctStmt = $conn->prepare("SELECT office FROM accounts WHERE user_id = ? LIMIT 1");
    if ($acctStmt) {
        $acctStmt->bind_param('i', $user_id);
        $acctStmt->execute();
        $ares = $acctStmt->get_result();
        if ($ares && ($arow = $ares->fetch_assoc())) {
            $accountOffice = isset($arow['office']) ? intval($arow['office']) : 0;
        }
        $acctStmt->close();
    }
    $response['user']['account_office'] = $accountOffice;
    $aw = $conn->prepare("SELECT window_id, office_id, window_no FROM office_windows WHERE assigned_staff_id = ? LIMIT 1");
    if ($aw) {
        $aw->bind_param('i', $user_id);
        $aw->execute();
        $awr = $aw->get_result();
        if ($awr && ($ar = $awr->fetch_assoc())) {
            $response['user']['assigned_window_id'] = intval($ar['window_id']);
            $response['user']['assigned_window_no'] = intval($ar['window_no']);
            $response['user']['assigned_office_id'] = intval($ar['office_id']);
        }
        $aw->close();
    }
    $response['user']['can_control'] = !empty($response['user']['assigned_window_id']);

    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
    exit;
}
?>