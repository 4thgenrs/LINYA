<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
include __DIR__ . '/../linya_db/config.php';

$response = ['rows'=>[], 'offices'=>[]];

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_name'])) {
    http_response_code(401);
    echo json_encode(['error'=>'Unauthorized']);
    exit;
}

$userName = $_SESSION['user_name'];
$officeFilter = isset($_GET['office_id']) ? intval($_GET['office_id']) : 0;

// date filtering: filter_type can be 'day', 'month', or 'range'
$filterType = isset($_GET['filter_type']) ? $_GET['filter_type'] : 'all';
$date = isset($_GET['date']) ? $_GET['date'] : null; // YYYY-MM-DD
$month = isset($_GET['month']) ? $_GET['month'] : null; // YYYY-MM
$start = isset($_GET['start']) ? $_GET['start'] : null; // YYYY-MM-DD
$end = isset($_GET['end']) ? $_GET['end'] : null; // YYYY-MM-DD

try {
    // load offices for dropdown
    $ofR = $conn->query("SELECT office_id, office_description FROM offices ORDER BY office_description ASC");
    if ($ofR) {
        while ($o = $ofR->fetch_assoc()) {
            $response['offices'][] = $o;
        }
    }

    // base select
    $select = "SELECT f.finished_id,
                   f.original_queue_id,
                   f.office_id,
                   COALESCE(o.office_description, '') AS office_description,
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
            FROM finished_queues f
            LEFT JOIN offices o ON f.office_id = o.office_id";

    // build dynamic WHERE clause with secure binding
    $where = [];
    $types = '';
    $params = [];

    // processed_by is required
    // Join accounts table to get user_id, filter by user_id
    $select .= " LEFT JOIN accounts a ON f.processed_by = a.name";
    $where[] = 'a.user_id = ?';
    $types .= 'i';
    $params[] = $_SESSION['user_id'];

    if ($officeFilter) {
        $where[] = 'f.office_id = ?';
        $types .= 'i';
        $params[] = $officeFilter;
    }

    // date filters applied to finished_at
    if ($filterType === 'day' && $date && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $where[] = "DATE(f.finished_at) = ?";
        $types .= 's';
        $params[] = $date;
    } elseif ($filterType === 'month' && $month && preg_match('/^\d{4}-\d{2}$/', $month)) {
        $where[] = "DATE_FORMAT(f.finished_at, '%Y-%m') = ?";
        $types .= 's';
        $params[] = $month;
    } elseif ($filterType === 'range' && $start && $end && preg_match('/^\d{4}-\d{2}-\d{2}$/', $start) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)) {
        // include whole end day
        $where[] = "f.finished_at BETWEEN ? AND ?";
        $types .= 'ss';
        $params[] = $start . ' 00:00:00';
        $params[] = $end . ' 23:59:59';
    }

    $sql = $select . ' WHERE ' . implode(' AND ', $where) . " ORDER BY f.finished_at DESC, f.finished_id DESC LIMIT 2000";

    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);

    // bind params dynamically
    $bindNames = [];
    $bindNames[] = $types;
    for ($i = 0; $i < count($params); $i++) {
        $bindVar = 'bind_' . $i;
        $$bindVar = $params[$i];
        $bindNames[] = &$$bindVar;
    }

    if (!empty($bindNames)) {
        call_user_func_array([$stmt, 'bind_param'], $bindNames);
    }

    $stmt->execute();
    $res = $stmt->get_result();
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $response['rows'][] = $r;
        }
    }
    $stmt->close();

} catch (Exception $e) {
    http_response_code(500);
    error_log('activityLogData error: ' . $e->getMessage());
    echo json_encode(['error'=>'Server error']);
    exit;
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
