<?php
include('../linya_db/config.php');

function getOfficeData($conn) {
    $data = [];
    $sql = "SELECT * FROM offices "; // change table name if different
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    return $data;
}