<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../linya_db/config.php'; // provides $conn

$office = isset($_GET['office']) ? intval($_GET['office']) : 1;

try {
    // --- get offices for the selector ---
    $offices = [];
    $oStmt = $conn->prepare("SELECT office_id, office_description FROM offices ORDER BY office_description ASC");
    if ($oStmt) {
        $oStmt->execute();
        $oRes = $oStmt->get_result();
        while ($o = $oRes->fetch_assoc()) {
            $offices[] = ['office_id' => intval($o['office_id']), 'office_description' => $o['office_description']];
        }
        $oStmt->close();
    }

    // fetch enabled windows for this office
    $wStmt = $conn->prepare("SELECT window_id, window_no, assigned_staff_id, enabled FROM office_windows WHERE office_id = ? AND enabled = 1 ORDER BY window_no ASC");
    if (!$wStmt) throw new Exception('Failed to prepare windows statement');
    $wStmt->bind_param('i', $office);
    $wStmt->execute();
    $wRes = $wStmt->get_result();
    $windows = [];
    while ($w = $wRes->fetch_assoc()) {
        $windows[] = $w;
    }
    $wStmt->close();

    // prepare services stmt (reused)
    $svcStmt = $conn->prepare("SELECT s.service_description, qs.soft_copy FROM queue_services qs JOIN services s ON qs.service_id = s.service_id WHERE qs.queue_id = ?");
    if (!$svcStmt) throw new Exception('Failed to prepare services statement');

    // --- improved queue lookup: exact by PK, fallback by window_no + office (prevents cross-office matches) ---
    $qExact = $conn->prepare("SELECT queue_id, queue_code, customer_name, customer_email, status, office_id, window_id FROM queues WHERE window_id = ? AND status = 'ongoing' LIMIT 1");
    if (!$qExact) throw new Exception('Failed to prepare exact queue statement');
    $qFallback = $conn->prepare("SELECT queue_id, queue_code, customer_name, customer_email, status, office_id, window_id FROM queues WHERE window_id = ? AND office_id = ? AND status = 'ongoing' LIMIT 1");
    if (!$qFallback) throw new Exception('Failed to prepare fallback queue statement');

    $resultWindows = [];
    foreach ($windows as $w) {
        $window_id = intval($w['window_id']);
        $window_no = intval($w['window_no']);
        $current = null;

        // 1) exact match using the PK window_id
        $qExact->bind_param('i', $window_id);
        $qExact->execute();
        $qRes = $qExact->get_result();
        if ($qRes) {
            $current = $qRes->fetch_assoc() ?: null;
            $qRes->free();
        }

        // 2) fallback: legacy rows where queues.window_id was stored as window_no,
        //    only accept if it belongs to the same office (prevents cross-office leaks)
        if (!$current && $window_no > 0) {
            $qFallback->bind_param('ii', $window_no, $office);
            $qFallback->execute();
            $qRes2 = $qFallback->get_result();
            if ($qRes2) {
                $tmp = $qRes2->fetch_assoc() ?: null;
                if ($tmp) $current = $tmp;
                $qRes2->free();
            }
        }

        if ($current) {
            // fetch services
            $services = [];
            $qid = intval($current['queue_id']);
            $svcStmt->bind_param('i', $qid);
            $svcStmt->execute();
            $sRes = $svcStmt->get_result();
            while ($s = $sRes->fetch_assoc()) {
                $services[] = [
                    'service_description' => $s['service_description'],
                    'soft_copy' => (isset($s['soft_copy']) && intval($s['soft_copy']) === 1) ? 1 : 0
                ];
            }
            if ($sRes) $sRes->free();
            $current['services'] = $services;
        }

        $resultWindows[] = [
            'window_id' => $window_id,
            'window_no' => intval($w['window_no']),
            'assigned_staff_id' => $w['assigned_staff_id'] ? intval($w['assigned_staff_id']) : null,
            'enabled' => intval($w['enabled']) === 1 ? 1 : 0,
            'current_queue' => $current
        ];
    }

    $qExact->close();
    $qFallback->close();
    $svcStmt->close();

    // pending queues for this office (include services)
    $pending = [];
    $pStmt = $conn->prepare("SELECT queue_id, queue_code, customer_name FROM queues WHERE office_id = ? AND status = 'pending' ORDER BY created_at ASC");
    if ($pStmt) {
        $pStmt->bind_param('i', $office);
        $pStmt->execute();
        $pRes = $pStmt->get_result();
        while ($pq = $pRes->fetch_assoc()) {
            $services = [];
            $pid = intval($pq['queue_id']);
            $svcStmt2 = $conn->prepare("SELECT s.service_description, qs.soft_copy FROM queue_services qs JOIN services s ON qs.service_id = s.service_id WHERE qs.queue_id = ?");
            if ($svcStmt2) {
                $svcStmt2->bind_param('i', $pid);
                $svcStmt2->execute();
                $sr = $svcStmt2->get_result();
                while ($s = $sr->fetch_assoc()) {
                    $services[] = [
                        'service_description' => $s['service_description'],
                        'soft_copy' => (isset($s['soft_copy']) && intval($s['soft_copy']) === 1) ? 1 : 0
                    ];
                }
                $svcStmt2->close();
            }
            $pq['services'] = $services;
            $pending[] = $pq;
        }
        $pStmt->close();
    }

    echo json_encode([
        'success' => true,
        'office' => $office,
        'offices' => $offices,          // <-- added
        'windows' => $resultWindows,
        'pending' => $pending
    ]);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}
?>