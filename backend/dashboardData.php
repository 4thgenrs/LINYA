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
    $stmt = $conn->prepare("SELECT finished_id, original_queue_id, office_id, window_id, processed_by, finished_at, created_at, status, services, customer_name, customer_phone, customer_email FROM finished_queues WHERE processed_by = ? ORDER BY finished_at DESC LIMIT 500");
    if ($stmt) {
        $stmt->bind_param('s', $user_name);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($r = $res->fetch_assoc()) {
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
        $qStmt = $conn->prepare("SELECT queue_id, queue_code, customer_name, customer_email, status FROM queues WHERE (window_id = ? OR window_id = ?) AND status = 'ongoing' LIMIT 1");

        foreach ($windows as $w) {
            $window_id = intval($w['window_id']);
            $window_no = intval($w['window_no']);
            $current = null;
            if ($qStmt) {
                $qStmt->bind_param('ii', $window_id, $window_no);
                $qStmt->execute();
                $qres = $qStmt->get_result();
                $current = $qres ? $qres->fetch_assoc() : null;
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
        if ($qStmt) $qStmt->close();
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

    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
    exit;
}
?>