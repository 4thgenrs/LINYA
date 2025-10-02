<?php
include('../linya_db/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_office'])) {
        $desc = trim($_POST['office_description']);
        if ($desc !== '') {
            addOffice($conn, $desc);
        }
    }

    if (isset($_POST['update_office'])) {
        $id = intval($_POST['office_id']);
        $desc = trim($_POST['office_description']);
        updateOffice($conn, $id, $desc);
    }

    if (isset($_POST['delete_office'])) {
        $id = intval($_POST['office_id']);
        deleteOffice($conn, $id);
    }

    // 🔑 Redirect to the same page (avoids resubmission)
    header("Location: office.php");
    exit;
}

// Fetch data AFTER handling post
$offices = getOfficeData($conn);


function getOfficeData($conn) {
    $data = [];
    $sql = "SELECT office_id, office_description FROM offices "; // change table name if different
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    return $data;
}

function addOffice($conn, $description) {
    $stmt = $conn->prepare("INSERT INTO offices (office_description) VALUES (?)");
    $stmt->bind_param("s", $description);
    return $stmt->execute();
}

function updateOffice($conn, $id, $description) {
    $stmt = $conn->prepare("UPDATE offices SET office_description = ? WHERE office_id = ?");
    $stmt->bind_param("si", $description, $id);
    return $stmt->execute();
}

function deleteOffice($conn, $id) {
    $stmt = $conn->prepare("DELETE FROM offices WHERE office_id = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

