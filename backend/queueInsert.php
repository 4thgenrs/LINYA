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

    // insert into queues including visitor info
    $qSt = $conn->prepare("INSERT INTO queues (queue_code, office_id, created_at, status, customer_name, customer_phone, customer_email) VALUES (?, ?, ?, 'pending', ?, ?, ?)");
    $qSt->bind_param("sissss", $queue_code, $office_id, $now, $customer_name, $customer_phone, $customer_email);
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
    echo json_encode(['success'=>false, 'error'=>$e->getMessage()]);
    exit;
}
