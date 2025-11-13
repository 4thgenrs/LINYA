
<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once __DIR__ . '/../linya_db/config.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || intval($_SESSION['user_type']) !== 1) {
    http_response_code(401);
    echo json_encode(['success'=>false,'error'=>'Unauthorized']);
    exit;
}

try {
    // totals
    $totals = [];
    $totals['total_staff_accounts'] = 0;
    $totals['total_active_windows'] = 0;
    $totals['total_active_offices'] = 0;
    $totals['total_pending_queues'] = 0;
    $totals['total_ongoing_queues'] = 0;
    $totals['total_finished_queues'] = 0;

    $r = $conn->query("SELECT COUNT(*) AS c FROM accounts WHERE user_type = 2");
    $totals['total_staff_accounts'] = $r ? intval($r->fetch_assoc()['c']) : 0;

    $r = $conn->query("SELECT COUNT(*) AS c FROM office_windows WHERE enabled = 1");
    $totals['total_active_windows'] = $r ? intval($r->fetch_assoc()['c']) : 0;

    $r = $conn->query("SELECT COUNT(*) AS c FROM offices");
    $totals['total_active_offices'] = $r ? intval($r->fetch_assoc()['c']) : 0;

    $r = $conn->query("SELECT COUNT(*) AS c FROM queues WHERE status = 'pending'");
    $totals['total_pending_queues'] = $r ? intval($r->fetch_assoc()['c']) : 0;

    $r = $conn->query("SELECT COUNT(*) AS c FROM queues WHERE status = 'ongoing'");
    $totals['total_ongoing_queues'] = $r ? intval($r->fetch_assoc()['c']) : 0;

    $r = $conn->query("SELECT COUNT(*) AS c FROM finished_queues");
    $totals['total_finished_queues'] = $r ? intval($r->fetch_assoc()['c']) : 0;

    // top 5 services by finished_queues via original_queue_id -> queue_services
    $sql = "SELECT s.service_description, COUNT(*) AS cnt
            FROM finished_queues fq
            JOIN queue_services qs ON qs.queue_id = fq.original_queue_id
            JOIN services s ON qs.service_id = s.service_id
            GROUP BY s.service_id
            ORDER BY cnt DESC
            LIMIT 5";
    $top = [];
    if ($res = $conn->query($sql)) {
        while ($row = $res->fetch_assoc()) $top[] = $row;
    }

    // queues per day (last 14 days) from queues.created_at
    $days = [];
    $sql = "SELECT DATE(created_at) AS d, COUNT(*) AS cnt FROM queues GROUP BY DATE(created_at) ORDER BY d DESC LIMIT 14";
    if ($res = $conn->query($sql)) {
        while ($row = $res->fetch_assoc()) $days[] = $row;
    }
    // ensure order ascending for chart
    $days = array_reverse($days);

    echo json_encode(['success'=>true,'totals'=>$totals,'top_services'=>$top,'queues_per_day'=>$days], JSON_UNESCAPED_UNICODE);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
    exit;
}