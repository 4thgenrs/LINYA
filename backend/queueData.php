<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('../linya_db/config.php');

function getOfficeQueue($conn, $office) {
    $queue_data = [];
    $office = intval($office);
    $sql = "SELECT queue_id, queue_code, created_at FROM queues WHERE status='pending' AND office_id = ? ORDER BY queue_id ASC";
    $stmt = $conn->prepare($sql);
    if (!$stmt) return $queue_data;
    $stmt->bind_param('i', $office);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // fetch services for this queue
            $services = [];
            $sstmt = $conn->prepare("SELECT s.service_id, s.service_description 
                                     FROM queue_services qs 
                                     JOIN services s ON qs.service_id = s.service_id 
                                     WHERE qs.queue_id = ? ORDER BY qs.id ASC");
            if ($sstmt) {
                $sstmt->bind_param('i', $row['queue_id']);
                $sstmt->execute();
                $rs = $sstmt->get_result();
                while ($sr = $rs->fetch_assoc()) {
                    $services[] = $sr;
                }
                $sstmt->close();
            }

            $queue_data[] = [
                'queue_id' => (int)$row['queue_id'],
                'queue_code' => $row['queue_code'],
                'created_at' => $row['created_at'],
                'services' => $services
            ];
        }
    }
    $stmt->close();
    return $queue_data;
}

//NOTE: $window is For specific window in session
function getNextLine($conn, $office, $window) { 
    $office = intval($office);
    $window = intval($window);

    // Do not assign next if this window already has an ongoing queue
    $chkStmt = $conn->prepare("SELECT queue_id FROM queues WHERE status='ongoing' AND window_id = ? LIMIT 1");
    if ($chkStmt) {
        $chkStmt->bind_param('i', $window);
        $chkStmt->execute();
        $chkRes = $chkStmt->get_result();
        if ($chkRes && $chkRes->num_rows > 0) {
            // there is already an active queue for this window — do not assign next
            $chkStmt->close();
            return null;
        }
        $chkStmt->close();
    }

    // Select the next pending queue (by smallest queue_id)
    $sql_select = "SELECT queue_id, queue_code FROM queues WHERE status='pending' AND office_id = ? ORDER BY queue_id ASC LIMIT 1";
    $stmt = $conn->prepare($sql_select);
    if (!$stmt) return null;
    $stmt->bind_param('i', $office);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $queue_id = (int)$row['queue_id'];
        $queue_code = $row['queue_code'];

        $sql_update = "UPDATE queues SET status='ongoing', window_id=? WHERE queue_id = ? AND status='pending'";
        $u = $conn->prepare($sql_update);
        if ($u) {
            $u->bind_param('ii', $window, $queue_id);
            if ($u->execute()) {
                $u->close();
                $stmt->close();
                return ['queue_id' => $queue_id, 'queue_code' => $queue_code];
            }
            $u->close();
        }
    }
    $stmt->close();
    return null;
}

function displayOnAir($conn,$window) {
    $window = intval($window);
    $sql = "SELECT queue_id, queue_code, created_at FROM queues WHERE status='ongoing' AND window_id=? ORDER BY queue_id DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    if (!$stmt) return null;
    $stmt->bind_param('i', $window);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $onAir = $result->fetch_assoc();
        // fetch services for this on-air queue
        $services = [];
        $sstmt = $conn->prepare("SELECT s.service_id, s.service_description
                                 FROM queue_services qs
                                 JOIN services s ON qs.service_id = s.service_id
                                 WHERE qs.queue_id = ? ORDER BY qs.id ASC");
        if ($sstmt) {
            $sstmt->bind_param('i', $onAir['queue_id']);
            $sstmt->execute();
            $rs = $sstmt->get_result();
            while ($sr = $rs->fetch_assoc()) {
                $services[] = $sr;
            }
            $sstmt->close();
        }
        $stmt->close();
        return [
            'queue_id' => (int)$onAir['queue_id'],
            'queue_code' => $onAir['queue_code'],
            'created_at' => $onAir['created_at'],
            'services' => $services
        ];
    }
    $stmt->close();
    return null;
}

function finishCurrentQueue($conn, $window) {
    $window = intval($window);
    // Find the most recent ongoing queue for this window
    $sql_select = "SELECT queue_id FROM queues WHERE status='ongoing' AND window_id=? ORDER BY queue_id DESC LIMIT 1";
    $stmt = $conn->prepare($sql_select);
    if (!$stmt) return false;
    $stmt->bind_param('i', $window);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $qid = (int)$row['queue_id'];
        $sql_update = "UPDATE queues SET status='finished' WHERE queue_id = ?";
        $u = $conn->prepare($sql_update);
        if ($u) {
            $u->bind_param('i', $qid);
            $ok = $u->execute();
            $u->close();
            $stmt->close();
            return (bool)$ok;
        }
    }
    $stmt->close();
    return false;
}

/**
 * Set or clear break for a specific window.
 * Called as: setWindowBreak($conn, $window, $isBreak)
 * - When $isBreak === true: move the ongoing queue for that window back to pending and clear window_id.
 * - When $isBreak === false: resume (no DB change required here).
 */
function setWindowBreak($conn, $window, $isBreak = true) {
    $window = intval($window);
    if ($isBreak) {
        // Move the ongoing queue for this window (if any) back to pending and clear window_id
        $sql = "UPDATE queues 
                SET status = 'pending', window_id = 0 
                WHERE status = 'ongoing' AND window_id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('i', $window);
        $ok = $stmt->execute();
        $stmt->close();
        return (bool)$ok;
    } else {
        // Resuming from break -- nothing to do to queues in DB.
        return true;
    }
}