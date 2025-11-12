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

    if (isset($_POST['delete_office'])) {
        $id = intval($_POST['office_id']);
        deleteOffice($conn, $id);
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
    $sql = "SELECT office_id, office_code, office_description FROM offices ORDER BY office_id ASC";
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
