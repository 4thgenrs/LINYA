
<?php
header('Content-Type: application/json; charset=utf-8');
include '../linya_db/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error'=>'Method not allowed']);
    exit;
}

$input = $_POST;
$officeId = isset($input['office_id']) ? intval($input['office_id']) : 0;
$services = isset($_POST['services']) ? (array)$_POST['services'] : [];

if (!$officeId || empty($services)) {
    http_response_code(400);
    echo json_encode(['error'=>'Missing office_id or services']);
    exit;
}

$conn->begin_transaction();

try {
    // get office code
    $stmt = $conn->prepare("SELECT office_code FROM offices WHERE office_id = ?");
    $stmt->bind_param("i", $officeId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $officeCode = $row['office_code'];
    } else throw new Exception('Invalid office');
    $stmt->close();

    // lock or create counter row
    $stmt = $conn->prepare("SELECT last_number FROM queue_counters WHERE office_id = ? FOR UPDATE");
    $stmt->bind_param("i", $officeId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $last = intval($row['last_number']) + 1;
        $stmt->close();
        $stmt = $conn->prepare("UPDATE queue_counters SET last_number = ? WHERE office_id = ?");
        $stmt->bind_param("ii", $last, $officeId);
        $stmt->execute();
        $stmt->close();
    } else {
        // first time for this office
        $last = 1;
        $stmt->close();
        $stmt = $conn->prepare("INSERT INTO queue_counters (office_id, last_number) VALUES (?, ?)");
        $stmt->bind_param("ii", $officeId, $last);
        $stmt->execute();
        $stmt->close();
    }

    $queueCode = strtoupper($officeCode) . '-' . str_pad($last, 3, '0', STR_PAD_LEFT);

    // insert queue
    $stmt = $conn->prepare("INSERT INTO queues (queue_code, office_id) VALUES (?, ?)");
    $stmt->bind_param("si", $queueCode, $officeId);
    $stmt->execute();
    $queueId = $conn->insert_id;
    $stmt->close();

    // insert services requested
    $stmt = $conn->prepare("INSERT INTO queue_services (queue_id, service_id) VALUES (?, ?)");
    foreach ($services as $s) {
        $sid = intval($s);
        $stmt->bind_param("ii", $queueId, $sid);
        $stmt->execute();
    }
    $stmt->close();

    $conn->commit();

    echo json_encode(['success'=>true, 'queue_id'=>$queueId, 'queue_code'=>$queueCode]);
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['error'=>$e->getMessage()]);
}
?>