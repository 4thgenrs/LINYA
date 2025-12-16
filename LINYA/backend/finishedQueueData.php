<?php
header('Content-Type: application/json; charset=utf-8');
include __DIR__ . '/../linya_db/config.php';

$officeFilter = isset($_GET['office_id']) ? intval($_GET['office_id']) : 0;

$response = ['rows'=>[], 'offices'=>[]];

try {
    // load offices for dropdown (id + description)
    $ofR = $conn->query("SELECT office_id, office_description FROM offices ORDER BY office_description ASC");
    while ($o = $ofR->fetch_assoc()) {
        $response['offices'][] = $o;
    }

    // If caller passed an office_id (numeric), resolve it to the office name
    $whereClause = '';
    if ($officeFilter) {
        $stmt = $conn->prepare("SELECT office_description FROM offices WHERE office_id = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('i', $officeFilter);
            $stmt->execute();
            $r = $stmt->get_result();
            if ($r && ($rw = $r->fetch_assoc())) {
                $officeName = $rw['office_description'];
                // finished_queues.office_id will now store office name strings; filter by that
                $whereClause = " WHERE f.office_id = '" . $conn->real_escape_string($officeName) . "'";
            }
            $stmt->close();
        }
    }

    // build main query — finished_queues.office_id now stores office name text
    $sql = "SELECT f.finished_id,
                   f.original_queue_id,
                   f.office_id AS office_description,
                   f.window_id,
                   f.window_id AS window_no,
                   f.processed_by,
                   f.created_at,
                   f.finished_at,
                   f.status,
                   f.services,
                   f.customer_name,
                   f.customer_phone,
                   f.customer_email
            FROM finished_queues f";

    $sql .= $whereClause;
    $sql .= " ORDER BY f.finished_at DESC, f.finished_id DESC LIMIT 2000";

    $res = $conn->query($sql);
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $response['rows'][] = $r;
        }
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error'=>'Server error']);
    exit;
}

echo json_encode($response);
exit;