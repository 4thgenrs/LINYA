<?php
header('Content-Type: application/json; charset=utf-8');

// ✅ FIX: Ensure PHP uses Philippine time
date_default_timezone_set('Asia/Manila');

include '../linya_db/config.php';

if (!isset($conn)) {
    echo json_encode(['success'=>false, 'error'=>'DB connection missing']);
    exit;
}

// ✅ FIX: Ensure MySQL session uses PH time (affects NOW(), CURRENT_TIMESTAMP, etc.)
if ($conn instanceof mysqli) {
    @ $conn->query("SET time_zone = '+08:00'");
}

$office_id = isset($_POST['office_id']) ? intval($_POST['office_id']) : 0;
$services = isset($_POST['services']) ? $_POST['services'] : [];
$softs = isset($_POST['soft_copy']) ? $_POST['soft_copy'] : [];

// new visitor fields
$customer_name = isset($_POST['customer_name']) ? trim($_POST['customer_name']) : '';
$customer_phone = isset($_POST['customer_phone']) ? trim($_POST['customer_phone']) : '';
$customer_email = isset($_POST['customer_email']) ? trim($_POST['customer_email']) : '';
// optional student fields
$student_number = isset($_POST['student_number']) ? trim($_POST['student_number']) : null;
$course = isset($_POST['course']) ? trim($_POST['course']) : null;
// optional client id (when face-login user)
$client_id = isset($_POST['client_id']) ? intval($_POST['client_id']) : 0;

if (!$office_id || !is_array($services) || empty($services)) {
    echo json_encode(['success'=>false, 'error'=>'Missing data']);
    exit;
}

if (!is_array($softs)) $softs = [];

// ensure arrays align
if (count($softs) && count($softs) !== count($services)) {
    // pad softs with zeros
    while(count($softs) < count($services)) $softs[] = 0;
}

$conn->begin_transaction();

try {
    // get office code
    $oSt = $conn->prepare("SELECT office_code FROM offices WHERE office_id = ?");
    $oSt->bind_param("i",$office_id);
    $oSt->execute();
    $oRes = $oSt->get_result();
    $office = $oRes->fetch_assoc();
    $oSt->close();

    $office_code = ($office && !empty($office['office_code'])) ? $office['office_code'] : 'OFF';

    // lock/update queue counter
    $cSt = $conn->prepare("SELECT last_number FROM queue_counters WHERE office_id = ? FOR UPDATE");
    $cSt->bind_param("i",$office_id);
    $cSt->execute();
    $cRes = $cSt->get_result();
    $row = $cRes->fetch_assoc();
    $cSt->close();

    if ($row) {
        $last = intval($row['last_number']) + 1;
        $uSt = $conn->prepare("UPDATE queue_counters SET last_number = ? WHERE office_id = ?");
        $uSt->bind_param("ii", $last, $office_id);
        $uSt->execute();
        $uSt->close();
    } else {
        $last = 1;
        $iSt = $conn->prepare("INSERT INTO queue_counters (office_id, last_number) VALUES (?, ?)");
        $iSt->bind_param("ii", $office_id, $last);
        $iSt->execute();
        $iSt->close();
    }

    $queue_code = $office_code . '-' . str_pad($last, 3, '0', STR_PAD_LEFT);

    // ✅ uses Asia/Manila because of date_default_timezone_set above
    $now = date('Y-m-d H:i:s');

    // -------------------------
    // Duplicate / pending queue checks
    // - If client_id provided: ensure there is no queue with status pending/ongoing/processing for that client_id
    // - Otherwise: check for same customer_name OR customer_phone OR customer_email OR student_number existing in pending/ongoing/processing queues for this office
    // -------------------------

    $dupStmt = null;
    if ($client_id && $client_id > 0) {
        $dupSql = "SELECT queue_id, status FROM queues WHERE (status IN ('pending','ongoing','processing')) AND (customer_id = ? OR customer_email IS NOT NULL AND customer_email = (SELECT email FROM clients WHERE client_id = ? LIMIT 1)) LIMIT 1";
        // Note: many installs may not have customer_id column; we'll attempt a safer fallback using client_id stored in clients table
        // First try to check by a dedicated customer_id column
        $hasCustomerIdCol = false;
        $resC = $conn->query("SHOW COLUMNS FROM queues LIKE 'customer_id'");
        if ($resC && $resC->num_rows > 0) {
            $hasCustomerIdCol = true;
        }

        if ($hasCustomerIdCol) {
            $dupStmt = $conn->prepare("SELECT queue_id, status FROM queues WHERE status IN ('pending','ongoing','processing') AND customer_id = ? LIMIT 1");
            if ($dupStmt) {
                $dupStmt->bind_param('i', $client_id);
            }
        } else {
            // fallback: match by client email if available
            $emailStmt = $conn->prepare('SELECT email FROM clients WHERE client_id = ? LIMIT 1');
            $email = null;
            if ($emailStmt) {
                $emailStmt->bind_param('i', $client_id);
                $emailStmt->execute();
                $r = $emailStmt->get_result();
                if ($r && $r->num_rows > 0) {
                    $row = $r->fetch_assoc();
                    $email = $row['email'];
                }
                $emailStmt->close();
            }

            if ($email) {
                $dupStmt = $conn->prepare("SELECT queue_id, status FROM queues WHERE status IN ('pending','ongoing','processing') AND customer_email = ? LIMIT 1");
                if ($dupStmt) $dupStmt->bind_param('s', $email);
            }
        }

        if ($dupStmt) {
            $dupStmt->execute();
            $dupRes = $dupStmt->get_result();
            if ($dupRes && $dupRes->num_rows > 0) {
                $dupRow = $dupRes->fetch_assoc();
                $dupStmt->close();
                throw new Exception('duplicate_client_pending');
            }
            $dupStmt->close();
        }

    } else {
        // Guest flow: check for duplicates by name/phone/email/student_number in pending/ongoing/processing for the same office
        $conds = [];
        $params = [];
        $types = '';
        if ($customer_name !== '') {
            $conds[] = 'customer_name = ?'; $params[] = $customer_name; $types .= 's';
        }
        if ($customer_phone !== '') {
            $conds[] = 'customer_phone = ?'; $params[] = $customer_phone; $types .= 's';
        }
        if ($customer_email !== '') {
            $conds[] = 'customer_email = ?'; $params[] = $customer_email; $types .= 's';
        }
        if ($student_number !== null && $student_number !== '') {
            $conds[] = 'student_number = ?'; $params[] = $student_number; $types .= 's';
        }

        if (count($conds) > 0) {
            $where = implode(' OR ', $conds);
            $sqlDup = "SELECT queue_id FROM queues WHERE status IN ('pending','ongoing','processing') AND office_id = ? AND (".$where.") LIMIT 1";
            $dupStmt = $conn->prepare($sqlDup);
            if ($dupStmt) {
                // bind params: office_id first
                $bindParams = array_merge([$office_id], $params);
                $bindTypes = 'i' . $types;
                // build references for bind_param
                $refs = [];
                $refs[] = & $bindTypes;
                foreach ($bindParams as $k => $v) {
                    $refs[] = & $bindParams[$k];
                }
                call_user_func_array([$dupStmt, 'bind_param'], $refs);
                $dupStmt->execute();
                $dupRes = $dupStmt->get_result();
                if ($dupRes && $dupRes->num_rows > 0) {
                    $dupStmt->close();
                    throw new Exception('duplicate_guest_pending');
                }
                $dupStmt->close();
            }
        }
    }

    // insert into queues including visitor info
    $qSt = $conn->prepare("INSERT INTO queues (queue_code, office_id, created_at, status, customer_name, customer_phone, customer_email, student_number, course) VALUES (?, ?, ?, 'pending', ?, ?, ?, ?, ?)");
    $qSt->bind_param("sissssss", $queue_code, $office_id, $now, $customer_name, $customer_phone, $customer_email, $student_number, $course);
    $qSt->execute();
    $queue_id = $qSt->insert_id;
    $qSt->close();

    if (!$queue_id) throw new Exception('Failed to create queue');

    // insert queue_services
    $qsSt = $conn->prepare("INSERT INTO queue_services (queue_id, service_id, soft_copy, hard_copy) VALUES (?, ?, ?, 1)");
    foreach ($services as $idx => $svc) {
        $svcId = intval($svc);
        $soft = (isset($softs[$idx]) && intval($softs[$idx]) === 1) ? 1 : 0;
        $qsSt->bind_param("iii", $queue_id, $svcId, $soft);
        $qsSt->execute();
    }
    $qsSt->close();

    $conn->commit();

    echo json_encode(['success'=>true, 'queue_id'=>intval($queue_id), 'queue_code'=>$queue_code]);
    exit;

} catch (Exception $e) {
    $conn->rollback();
    $msg = $e->getMessage();
    $error_code = null;
    $error_message = $msg;

    if ($msg === 'duplicate_client_pending') {
        $error_code = 'duplicate_client_pending';
        $error_message = 'You already have an existing queue (pending or in progress). Please wait until it is completed.';
    } elseif ($msg === 'duplicate_guest_pending') {
        $error_code = 'duplicate_guest_pending';
        $error_message = 'A queue with the same name/phone/email/student number already exists. You cannot create a new queue until it is processed.';
    } elseif ($msg === 'Failed to create queue') {
        $error_code = 'create_failed';
        $error_message = 'Failed to create queue. Please try again.';
    }

    // Return structured JSON error (use HTTP 200 to avoid browser showing a network error)
    echo json_encode([
        'success' => false,
        'error' => $error_message,
        'error_code' => $error_code
    ]);
    exit;
}
