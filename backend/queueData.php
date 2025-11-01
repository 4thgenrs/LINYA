<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('../linya_db/config.php');

function getOfficeQueue($conn, $office) {
    $queue_data = [];
    $sql = "SELECT queue_id, queue_code FROM queues WHERE status='pending' AND office_id = '$office' ORDER BY queue_id ASC";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $queue_data[] = $row;
        }
    }
    return $queue_data;

}
//NOTE: $window is For specific window in session
function getNextLine($conn, $office, $window) { 
    // Select the next pending queue (by smallest queue_id)
    $sql_select = "SELECT queue_id, queue_code FROM queues WHERE status='pending' AND office_id = '$office' ORDER BY queue_id ASC LIMIT 1";
    $res = $conn->query($sql_select);

    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $queue_id = (int)$row['queue_id'];
        $queue_code = $row['queue_code'];

        $sql_update = "UPDATE queues SET status='ongoing', window_id='$window' WHERE queue_id = $queue_id AND status='pending'";
        if ($conn->query($sql_update)) {
            return ['queue_id' => $queue_id, 'queue_code' => $queue_code];
        }
    }
    return null;
}

function displayOnAir($conn,$window) {
    $sql = "SELECT queue_id, queue_code FROM queues WHERE status='ongoing' AND window_id='$window' ORDER BY queue_id DESC LIMIT 1";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $onAir = $result->fetch_assoc();
        return $onAir;
    }
    return null;
}

function finishCurrentQueue($conn, $window) {
    // Find the most recent ongoing queue for this window
    $sql_select = "SELECT queue_id FROM queues WHERE status='ongoing' AND window_id='$window' ORDER BY queue_id DESC LIMIT 1";
    $res = $conn->query($sql_select);
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $qid = (int)$row['queue_id'];
        $sql_update = "UPDATE queues SET status='finished' WHERE queue_id = $qid";
        return (bool)$conn->query($sql_update);
    }
    return false;
}

function setWindowBreak($conn, $isBreak) {
    if ($isBreak) {
        // If going on break, move any ongoing queue back to pending (global)
        $sql = "UPDATE queues SET status='pending' , window_id ='0' WHERE status='ongoing'";
        if ($conn->query($sql) === false) {
            return false;
        }
        return true;
    } else {
        return true;
    }
}