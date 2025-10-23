<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('../linya_db/config.php');

function getRegistrarQueue($conn) {
    $queue_data = [];
    $sql = "SELECT number FROM queue WHERE status='waiting' ORDER BY number ASC";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $queue_data[] = $row;
        }
    }
    return $queue_data;

}

function getNextLine($conn, $window) {
    // Select the next waiting row by smallest number
    $sql_select = "SELECT number FROM queue WHERE status='waiting' ORDER BY number ASC LIMIT 1";
    $res = $conn->query($sql_select);

    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $number = $row['number'];

        // Update that specific number to ongoing status
        $sql_update = "UPDATE queue SET status='ongoing', window = '" . $conn->real_escape_string($window) . "' WHERE number = '" . $conn->real_escape_string($number) . "' AND status='waiting'";
        if ($conn->query($sql_update)) {
            // Return just the number since we don't have id
            return ['number' => $number];
        }
    }
    return null;
}

function displayOnAir($conn,$window) {
    $sql = "SELECT number FROM queue WHERE status='ongoing' AND window = $window ORDER BY number DESC LIMIT 1";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $onAir = $result->fetch_assoc();
        return $onAir;
    }
    return null;
}

function finishCurrentQueue($conn, $window) {
    // Update the current ongoing queue for this window to finished
    $sql_update = "UPDATE queue SET status='finished' WHERE status='ongoing' AND window = '" . $conn->real_escape_string($window) . "'";
    if ($conn->query($sql_update)) {
        return true;
    }
    return false;
}

function setWindowBreak($conn, $window, $isBreak) {
    if ($isBreak) {
        // If going on break, update any ongoing queue to waiting
        $sql = "UPDATE queue SET status='waiting', window=NULL WHERE status='ongoing' AND window = '" . $conn->real_escape_string($window) . "'";
        if (!$conn->query($sql)) {
            return false;
        }
        // Store break status in a session variable or database table if needed
        $_SESSION['window_' . $window . '_break'] = true;
        return true;
    } else {
        // Returning from break
        $_SESSION['window_' . $window . '_break'] = false;
        return true;
    }
}