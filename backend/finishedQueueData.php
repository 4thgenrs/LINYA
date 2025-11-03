
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

    // build main query (map window_id -> window_no)
    $sql = "SELECT f.finished_id,
                   f.original_queue_id,
                   f.office_id,
                   COALESCE(o.office_description, '') AS office_description,
                   f.window_id,
                   COALESCE(ow.window_no, 0) AS window_no,
                   f.processed_by,
                   f.created_at,
                   f.finished_at,
                   f.status,
                   f.services,
                   f.customer_name,
                   f.customer_phone,
                   f.customer_email
            FROM finished_queues f
            LEFT JOIN offices o ON f.office_id = o.office_id
            LEFT JOIN office_windows ow ON f.window_id = ow.window_id";
    if ($officeFilter) $sql .= " WHERE f.office_id = " . intval($officeFilter);
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