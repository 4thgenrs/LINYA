
<?php
include '../linya_db/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Method not allowed";
    exit;
}

$conn->begin_transaction();
try {
    $conn->query("TRUNCATE TABLE queue_services");
    $conn->query("TRUNCATE TABLE queues");
    $conn->query("TRUNCATE TABLE queue_counters");
    $conn->commit();
    echo "OK";
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo $e->getMessage();
}
?>