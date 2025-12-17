<?php
header('Content-Type: application/json; charset=utf-8');
include __DIR__ . '/../linya_db/config.php';

$office = isset($_GET['office_id']) ? intval($_GET['office_id']) : 0;

$out = [];
try {
    if ($office > 0) {
        $stmt = $conn->prepare("SELECT user_id, name, user_name FROM accounts WHERE office = ? ORDER BY name ASC");
        if ($stmt) {
            $stmt->bind_param('i', $office);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($r = $res->fetch_assoc()) $out[] = $r;
            $stmt->close();
        }
    } else {
        $res = $conn->query("SELECT user_id, name, user_name FROM accounts ORDER BY name ASC");
        while ($r = $res->fetch_assoc()) $out[] = $r;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([]);
    exit;
}

echo json_encode($out, JSON_UNESCAPED_UNICODE);
exit;

?>
