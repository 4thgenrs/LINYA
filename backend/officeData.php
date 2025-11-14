<?php
include('../linya_db/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_office'])) {
        $code = trim($_POST['office_code']);
        $desc = trim($_POST['office_description']);

        if ($code !== '' && $desc !== '') {
            addOffice($conn, $code, $desc);
        }
    }

    if (isset($_POST['update_office'])) {
        $id = intval($_POST['office_id']);
        $code = trim($_POST['office_code']);
        $desc = trim($_POST['office_description']);
        updateOffice($conn, $id, $code, $desc);
    }

    // TOGGLE enable/disable (replaces delete)
    if (isset($_POST['toggle_office'])) {
        $id = intval($_POST['office_id'] ?? 0);
        if ($id > 0) {
            // get current value
            $stmt = $conn->prepare("SELECT enabled FROM offices WHERE office_id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $res = $stmt->get_result();
                $current = 1;
                if ($res && $row = $res->fetch_assoc()) {
                    $current = intval($row['enabled']);
                }
                $stmt->close();

                $new = $current ? 0 : 1;
                $u = $conn->prepare("UPDATE offices SET enabled = ? WHERE office_id = ?");
                if ($u) {
                    $u->bind_param("ii", $new, $id);
                    $u->execute();
                    $u->close();
                }
            }
        }
    }

    // 🔁 Redirect to prevent form resubmission
    header("Location: office.php");
    exit;
}

// Fetch all offices after processing
$offices = getOfficeData($conn);


// ======================
// 🧩 FUNCTIONS
// ======================

function getOfficeData($conn) {
    $data = [];
    // Note: ensure offices table has 'enabled' column (see provided ALTER TABLE)
    $sql = "SELECT office_id, office_code, office_description, enabled FROM offices ORDER BY office_id ASC";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    return $data;
}

function addOffice($conn, $code, $description) {
    $stmt = $conn->prepare("INSERT INTO offices (office_code, office_description) VALUES (?, ?)");
    $stmt->bind_param("ss", $code, $description);
    return $stmt->execute();
}

function updateOffice($conn, $id, $code, $description) {
    $stmt = $conn->prepare("UPDATE offices SET office_code = ?, office_description = ? WHERE office_id = ?");
    $stmt->bind_param("ssi", $code, $description, $id);
    return $stmt->execute();
}

function deleteOffice($conn, $id) {
    $stmt = $conn->prepare("DELETE FROM offices WHERE office_id = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}
?>
