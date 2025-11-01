
<?php
header('Content-Type: application/json; charset=utf-8');
include '../linya_db/config.php';

if (!isset($conn)) {
    echo json_encode(['error'=>'DB connection missing']);
    exit;
}

$queueId = isset($_GET['queue_id']) ? intval($_GET['queue_id']) : 0;
if (!$queueId) {
    echo json_encode(['error'=>'Missing queue_id']);
    exit;
}

// main queue info
$q = $conn->prepare("SELECT q.queue_id, q.queue_code, q.office_id, q.created_at, q.status, o.office_description
                     FROM queues q JOIN offices o ON q.office_id = o.office_id
                     WHERE q.queue_id = ?");
$q->bind_param('i',$queueId);
$q->execute();
$res = $q->get_result();
$queue = $res->fetch_assoc();
$q->close();

if (!$queue) {
    echo json_encode(['error'=>'Queue not found']);
    exit;
}

// services for this queue (and soft_copy column if present)
$services = [];
$qs = $conn->prepare("SELECT qs.service_id, s.service_description, qs.soft_copy
                      FROM queue_services qs
                      JOIN services s ON qs.service_id = s.service_id
                      WHERE qs.queue_id = ?");
$qs->bind_param('i',$queueId);
$qs->execute();
$r2 = $qs->get_result();
while ($row = $r2->fetch_assoc()) {
    $services[] = [
        'service_id' => intval($row['service_id']),
        'description' => $row['service_description'],
        'soft_copy' => isset($row['soft_copy']) ? intval($row['soft_copy']) : 0
    ];
}
$qs->close();

// live pending queues for same office
$pending = [];
$pp = $conn->prepare("SELECT queue_id, queue_code, created_at FROM queues WHERE office_id = ? AND status = 'pending' ORDER BY created_at ASC");
$pp->bind_param('i', $queue['office_id']);
$pp->execute();
$r3 = $pp->get_result();
while ($p = $r3->fetch_assoc()) {
    $pending[] = $p;
}
$pp->close();

// build response
$response = [
    'queue' => [
        'queue_id' => intval($queue['queue_id']),
        'queue_code' => $queue['queue_code'],
        'office_id' => intval($queue['office_id']),
        'office_description' => $queue['office_description'],
        'created_at' => $queue['created_at'],
        'status' => $queue['status'],
    ],
    'services' => $services,
    'pending' => $pending
];

echo json_encode($response);