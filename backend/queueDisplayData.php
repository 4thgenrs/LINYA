<?php
include '../linya_db/config.php';

$officeFilter = isset($_GET['office_id']) ? intval($_GET['office_id']) : 0;

// Load offices for dropdown
$offices = [];
$r = $conn->query("SELECT office_id, office_description, office_code FROM offices");
while ($o = $r->fetch_assoc()) $offices[] = $o;

// Build main queue query
$sql = "SELECT q.queue_id, q.queue_code, q.office_id, q.created_at, q.status, o.office_description
        FROM queues q
        JOIN offices o ON q.office_id = o.office_id";
if ($officeFilter) $sql .= " WHERE q.office_id = " . intval($officeFilter);
$sql .= " ORDER BY q.created_at ASC";

$res = $conn->query($sql);
?>
