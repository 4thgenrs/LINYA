<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once __DIR__ . '/../linya_db/config.php'; // provides $conn

$office = isset($_GET['office']) ? intval($_GET['office']) : 1;

// Server-side permission: staff may only view their assigned office
if (isset($_SESSION['user_id']) && isset($_SESSION['user_type']) && intval($_SESSION['user_type']) === 2) {
    $staffId = intval($_SESSION['user_id']);
    $aw = $conn->prepare("SELECT office_id, window_id FROM office_windows WHERE assigned_staff_id = ? LIMIT 1");
    if ($aw) {
        $aw->bind_param('i', $staffId);
        $aw->execute();
        $awr = $aw->get_result();
        $assigned = $awr ? $awr->fetch_assoc() : null;
        $aw->close();

        // If staff has no assigned window/office, deny access
        if (!$assigned) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Forbidden: not assigned to any window/office']);
            exit;
        }

        // Deny if requested office is not the staff's office
        if (intval($assigned['office_id']) !== $office) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Forbidden: access to this office is not allowed']);
            exit;
        }
    }
}

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

    // fetch enabled windows for this office (include assigned staff name)
    $wStmt = $conn->prepare("SELECT ow.window_id, ow.window_no, ow.assigned_staff_id, ow.enabled, a.name AS assigned_staff_name FROM office_windows ow LEFT JOIN accounts a ON ow.assigned_staff_id = a.user_id WHERE ow.office_id = ? AND ow.enabled = 1 ORDER BY ow.window_no ASC");
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
    // accept both 'ongoing' and 'processing' as on-air for display
    $qExact = $conn->prepare("SELECT queue_id, queue_code, customer_name, customer_email, status, office_id, window_id FROM queues WHERE window_id = ? AND status IN ('ongoing','processing') LIMIT 1");
    if (!$qExact) throw new Exception('Failed to prepare exact queue statement');
    $qFallback = $conn->prepare("SELECT queue_id, queue_code, customer_name, customer_email, status, office_id, window_id FROM queues WHERE window_id = ? AND office_id = ? AND status IN ('ongoing','processing') LIMIT 1");
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
            'assigned_staff_name' => isset($w['assigned_staff_name']) && $w['assigned_staff_name'] !== null ? $w['assigned_staff_name'] : null,
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