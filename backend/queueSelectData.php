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
    // prefer the resolved PK first (window_id), then fall back to window_no (legacy)
    $window = (isset($_SESSION['window_id']) && intval($_SESSION['window_id']) > 0)
        ? intval($_SESSION['window_id'])
        : (isset($_SESSION['window_no']) ? intval($_SESSION['window_no']) : $window);
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

        // --- RESOLVE the actual window NUMBER that processed this queue ---
        // prefer session window_no; if only session.window_id (PK) exist, resolve to its window_no;
        // otherwise try to resolve from the queue row's window_id (legacy PK), else fallback to raw value.
        $window_no_to_store = 0;
        if (isset($_SESSION['window_no']) && intval($_SESSION['window_no']) > 0) {
            $window_no_to_store = intval($_SESSION['window_no']);
        } elseif (isset($_SESSION['window_id']) && intval($_SESSION['window_id']) > 0) {
            $tmpWid = intval($_SESSION['window_id']);
            $wr = $conn->prepare("SELECT window_no FROM office_windows WHERE window_id = ? LIMIT 1");
            if ($wr) {
                $wr->bind_param('i', $tmpWid);
                $wr->execute();
                $wres = $wr->get_result();
                if ($wres && ($rw = $wres->fetch_assoc())) {
                    $window_no_to_store = intval($rw['window_no']);
                }
                $wr->close();
            }
        } else {
            // try resolving queueRow.window_id as PK -> window_no
            $maybe = intval($queueRow['window_id'] ?? 0);
            if ($maybe > 0) {
                $wr = $conn->prepare("SELECT window_no FROM office_windows WHERE window_id = ? LIMIT 1");
                if ($wr) {
                    $wr->bind_param('i', $maybe);
                    $wr->execute();
                    $wres = $wr->get_result();
                    if ($wres && ($rw = $wres->fetch_assoc())) {
                        $window_no_to_store = intval($rw['window_no']);
                    }
                    $wr->close();
                }
            }
        }
        // final fallback: use whatever numeric was in queue row
        if ($window_no_to_store <= 0) {
            $window_no_to_store = intval($queueRow['window_id'] ?? 0);
        }
        // --- end resolve ---

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
            // We now store office name (text) in finished_queues.office_id.
            // Resolve office name from session or from queue row's office_id (if numeric), otherwise cast to string.
            $office_name_to_store = null;
            if (isset($_SESSION['office_id']) && intval($_SESSION['office_id']) > 0) {
                $tmp = intval($_SESSION['office_id']);
                $sr = $conn->prepare("SELECT office_description FROM offices WHERE office_id = ? LIMIT 1");
                if ($sr) {
                    $sr->bind_param('i', $tmp);
                    $sr->execute();
                    $res = $sr->get_result();
                    if ($res && ($rw = $res->fetch_assoc())) $office_name_to_store = $rw['office_description'];
                    $sr->close();
                }
            }
            if (!$office_name_to_store) {
                $maybe = $queueRow['office_id'] ?? null;
                if (is_numeric($maybe) && intval($maybe) > 0) {
                    $sr = $conn->prepare("SELECT office_description FROM offices WHERE office_id = ? LIMIT 1");
                    if ($sr) {
                        $mi = intval($maybe);
                        $sr->bind_param('i', $mi);
                        $sr->execute();
                        $res = $sr->get_result();
                        if ($res && ($rw = $res->fetch_assoc())) $office_name_to_store = $rw['office_description'];
                        $sr->close();
                    }
                }
            }
            if (!$office_name_to_store) {
                // fallback: use whatever is in queueRow.office_id as string (could already be name)
                $office_name_to_store = (string)($queueRow['office_id'] ?? '');
            }

            $window_id = $window_no_to_store; // store the resolved window NUMBER here
            $created_at = $queueRow['created_at'] ?? null;
            $status = 'finished';
            $customer_name = $queueRow['customer_name'] ?? null;
            $customer_phone = $queueRow['customer_phone'] ?? null;
            $customer_email = $queueRow['customer_email'] ?? null;

            // types: original_queue_id (int), office_id (string), window_id (int), then 8 strings
            $types = 'isissssssss';

            $ins->bind_param($types,
                $queue_id,
                $office_name_to_store,
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

    // attach services (with soft_copy flag) for each queue item and on-air
    $svcStmt = $conn->prepare(
        "SELECT s.service_description, qs.soft_copy 
         FROM queue_services qs 
         JOIN services s ON qs.service_id = s.service_id 
         WHERE qs.queue_id = ?"
    );
    if ($svcStmt) {
        foreach ($queue_list as &$q) {
            $services = [];
            $id = intval($q['queue_id'] ?? 0);
            if ($id > 0) {
                $svcStmt->bind_param('i', $id);
                $svcStmt->execute();
                $res = $svcStmt->get_result();
                while ($r = $res->fetch_assoc()) {
                    $services[] = [
                        'service_description' => $r['service_description'],
                        'soft_copy' => (isset($r['soft_copy']) && intval($r['soft_copy']) === 1) ? 1 : 0
                    ];
                }
            }
            $q['services'] = $services;
        }
        // on-air
        if ($on_air && !empty($on_air['queue_id'])) {
            $services = [];
            $oid = intval($on_air['queue_id']);
            if ($oid > 0) {
                $svcStmt->bind_param('i', $oid);
                $svcStmt->execute();
                $res = $svcStmt->get_result();
                while ($r = $res->fetch_assoc()) {
                    $services[] = [
                        'service_description' => $r['service_description'],
                        'soft_copy' => (isset($r['soft_copy']) && intval($r['soft_copy']) === 1) ? 1 : 0
                    ];
                }
            }
            $on_air['services'] = $services;
        }
        $svcStmt->close();
    }

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