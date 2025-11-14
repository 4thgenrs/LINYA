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

    // TOP 5 SERVICES based on finished_queues.services (services stored as comma-separated descriptions)
    $serviceCounts = [];
    $sql = "SELECT services FROM finished_queues WHERE services IS NOT NULL AND TRIM(services) != ''";
    if ($res = $conn->query($sql)) {
        while ($row = $res->fetch_assoc()) {
            $svcText = $row['services'];
            // explode by comma
            $parts = preg_split('/,/', $svcText);
            foreach ($parts as $p) {
                $s = trim($p);
                if ($s === '') continue;
                // remove parenthetical notes like "(PDF)" or other parentheses
                $s = preg_replace('/\s*\([^)]*\)/', '', $s);
                $s = trim($s);
                if ($s === '') continue;
                // normalize spacing and case (keep original case for display, but count case-insensitive)
                $key = mb_strtolower($s, 'UTF-8');
                if (!isset($serviceCounts[$key])) $serviceCounts[$key] = ['label' => $s, 'count' => 0];
                $serviceCounts[$key]['count'] += 1;
            }
        }
        $res->close();
    }
    // sort and take top 5
    usort($serviceCounts, function($a,$b){ return $b['count'] <=> $a['count']; });
    $top = array_slice($serviceCounts, 0, 5);
    // normalize output
    $top_services = [];
    foreach ($top as $t) {
        $top_services[] = ['service_description' => $t['label'], 'cnt' => $t['count']];
    }

    // QUEUES PER DAY using finished_queues.finished_at (last 14 days)
    $days = [];
    $daysMap = [];
    // initialize last 14 days (including today)
    $period = 14;
    for ($i = $period - 1; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-{$i} days"));
        $daysMap[$d] = 0;
    }
    // fetch counts grouped by date
    $stmt = $conn->prepare("SELECT DATE(finished_at) AS d, COUNT(*) AS cnt FROM finished_queues WHERE finished_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY) GROUP BY DATE(finished_at)");
    if ($stmt) {
        $stmt->bind_param('i', $period);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $d = $row['d'];
            $cnt = intval($row['cnt']);
            if (isset($daysMap[$d])) $daysMap[$d] = $cnt;
        }
        $stmt->close();
    }
    // convert to array of objects in ascending date order
    foreach ($daysMap as $d => $cnt) {
        $days[] = ['d' => $d, 'cnt' => $cnt];
    }

    echo json_encode(['success'=>true,'totals'=>$totals,'top_services'=>$top_services,'queues_per_day'=>$days], JSON_UNESCAPED_UNICODE);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
    exit;
}
?>