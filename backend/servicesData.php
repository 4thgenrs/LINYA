<?php
include('../linya_db/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['service_description'], $_POST['office_id'])) {
        // Add service
        $desc = trim($_POST['service_description']);
        $officeId = intval($_POST['office_id']);
        if ($desc !== '' && $officeId > 0) {
            addService($conn, $desc, $officeId);
            header("Location: services.php");
            exit;
        }
    }

    if (isset($_POST['delete_id'])) {
        // Delete service
        $id = intval($_POST['delete_id']);
        deleteService($conn, $id);
        header("Location: services.php");
        exit;
    }

}


function getServiceData($conn) {
    $data = [];
    $sql = "SELECT S.service_id, S.service_description, O.office_description FROM services S INNER JOIN offices O ON S.office = O.office_id";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    return $data;
}

function getOfficeData($conn) {
    $sql = "SELECT office_id, office_description FROM offices";
    $result = $conn->query($sql);

    $data = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    return $data;
}

function addService($conn, $serviceDesc, $officeId) {
    $stmt = $conn->prepare("INSERT INTO services (service_description, office) VALUES (?, ?)");
    $stmt->bind_param("si", $serviceDesc, $officeId);
    return $stmt->execute();
}

function updateService($conn, $id, $desc, $officeId) {
    $id = intval($id);
    $desc = $conn->real_escape_string($desc);
    $officeId = intval($officeId);

    $sql = "UPDATE services SET service_description = '$desc', office = $officeId WHERE service_id = $id";
    return $conn->query($sql);
}

function deleteService($conn, $id) {
    $id = intval($id);
    $sql = "DELETE FROM services WHERE service_id = $id";
    return $conn->query($sql);
}