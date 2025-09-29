<?php
include('../linya_db/config.php');

function getServiceData($conn) {
    $data = [];
    $sql = "SELECT service_id, service_description, office FROM services"; // change table name if different
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    return $data;
}