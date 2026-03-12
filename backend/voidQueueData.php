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
                // voided_queues.office_id will store office name strings; filter by that
                $whereClause = " WHERE v.office_id = '" . $conn->real_escape_string($officeName) . "'";
            }
            $stmt->close();
        }
    }

    // build main query — return all columns from voided_queues and provide window_no alias
    $sql = "SELECT v.*, v.window_id AS window_no FROM voided_queues v";

    $sql .= $whereClause;
    $sql .= " ORDER BY v.process_finished DESC, v.finished_id DESC LIMIT 2000";

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
