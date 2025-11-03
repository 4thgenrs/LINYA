<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/queueData.php'; // queueData.php includes config and defines helper functions

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'state';

// default request values (from query)
$window = isset($_REQUEST['window']) ? intval($_REQUEST['window']) : 0;
$office = isset($_REQUEST['office']) ? intval($_REQUEST['office']) : 0;

// if a staff session exists prefer session's office/window
if (isset($_SESSION['user_id']) && isset($_SESSION['user_type']) && intval($_SESSION['user_type']) === 2) {
    $office = isset($_SESSION['office_id']) ? intval($_SESSION['office_id']) : $office;
    // prefer window_no if provided, otherwise window_id
    $window = isset($_SESSION['window_no']) && intval($_SESSION['window_no']) > 0 ? intval($_SESSION['window_no']) : (isset($_SESSION['window_id']) ? intval($_SESSION['window_id']) : $window);
    // note: getNextLine expects a numeric window id/identifier; queueData functions use $window as passed
}

try {
    if ($action === 'next') {
        $res = getNextLine($conn, $office, $window);
        if ($res) {
            echo json_encode(['success' => true, 'data' => $res]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No waiting queue item to call.']);
        }
        exit;
    }

    // Modified finish action: archive finished queue into finished_queues, include services and visitor info,
    // record which staff processed it and which window, then remove the original queue and its queue_services.
    if ($action === 'finish') {
        // get the current on-air queue for this window
        $onAir = displayOnAir($conn, $window);
        if (!$onAir || empty($onAir['queue_id'])) {
            echo json_encode(['success' => false, 'message' => 'No active queue on this window to finish.']);
            exit;
        }
        $queue_id = intval($onAir['queue_id']);

        // lock and fetch the queue row
        $qSt = $conn->prepare("SELECT * FROM queues WHERE queue_id = ? FOR UPDATE");
        $qSt->bind_param('i', $queue_id);
        $qSt->execute();
        $qRes = $qSt->get_result();
        $queueRow = $qRes->fetch_assoc();
        $qSt->close();

        if (!$queueRow) {
            echo json_encode(['success' => false, 'message' => 'Queue not found']);
            exit;
        }

        // collect services for this queue
        $sSt = $conn->prepare("SELECT s.service_description, qs.soft_copy FROM queue_services qs JOIN services s ON qs.service_id = s.service_id WHERE qs.queue_id = ?");
        $sSt->bind_param('i', $queue_id);
        $sSt->execute();
        $sRes = $sSt->get_result();
        $servicesArr = [];
        while ($r = $sRes->fetch_assoc()) {
            $servicesArr[] = $r['service_description'] . ((isset($r['soft_copy']) && intval($r['soft_copy']) === 1) ? ' (PDF)' : '');
        }
        $sSt->close();
        $servicesText = count($servicesArr) ? implode(', ', $servicesArr) : null;

        // prepare archive insert (removed queue_code column)
        $processed_by = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : null;
        $finishedAt = date('Y-m-d H:i:s');

        // transaction: insert into finished_queues, delete queue_services, delete queues
        $conn->begin_transaction();
        try {
            $ins = $conn->prepare(
                "INSERT INTO finished_queues 
                    (original_queue_id, office_id, window_id, processed_by, finished_at, created_at, status, services, customer_name, customer_phone, customer_email)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE 
                    processed_by = VALUES(processed_by),
                    finished_at = VALUES(finished_at),
                    status = VALUES(status),
                    services = VALUES(services),
                    customer_name = VALUES(customer_name),
                    customer_phone = VALUES(customer_phone),
                    customer_email = VALUES(customer_email)"
            );
            // types: 3 ints, then 8 strings = 11 params
            $types = 'iiissssssss';
            $office_id = intval($queueRow['office_id']);
            $window_id = intval($queueRow['window_id']);
            $created_at = $queueRow['created_at'] ?? null;
            $status = 'finished';
            $customer_name = $queueRow['customer_name'] ?? null;
            $customer_phone = $queueRow['customer_phone'] ?? null;
            $customer_email = $queueRow['customer_email'] ?? null;

            $ins->bind_param($types,
                $queue_id,
                $office_id,
                $window_id,
                $processed_by,
                $finishedAt,
                $created_at,
                $status,
                $servicesText,
                $customer_name,
                $customer_phone,
                $customer_email
            );
            $ins->execute();
            $ins->close();

            // remove queue services
            $dqs = $conn->prepare("DELETE FROM queue_services WHERE queue_id = ?");
            $dqs->bind_param('i', $queue_id);
            $dqs->execute();
            $dqs->close();

            // remove the original queue row
            $dq = $conn->prepare("DELETE FROM queues WHERE queue_id = ?");
            $dq->bind_param('i', $queue_id);
            $dq->execute();
            $dq->close();

            $conn->commit();
            echo json_encode(['success' => true]);
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            error_log('Archive finish error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Failed to archive finished queue']);
            exit;
        }
    }

    if ($action === 'break') {
        $isBreak = isset($_REQUEST['isBreak']) ? filter_var($_REQUEST['isBreak'], FILTER_VALIDATE_BOOLEAN) : true;
        $res = setWindowBreak($conn, $window, $isBreak);
        if ($res) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update break status.']);
        }
        exit;
    }

    // Default: state — return queue list and on-air for given office/window
    $queue_list = getOfficeQueue($conn, $office);
    $on_air = displayOnAir($conn, $window);

    echo json_encode([
        'success' => true,
        'data' => [
            'queue_list' => $queue_list,
            'on_air' => $on_air,
            'office' => $office,
            'window' => $window
        ]
    ]);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
    exit;
}