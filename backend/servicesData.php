<?php
include('../linya_db/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $desc = trim($_POST['service_description'] ?? '');
    $officeId = intval($_POST['office_id'] ?? 0);

    // UPDATE service
    if (!empty($_POST['update_id'])) {
        $id = intval($_POST['update_id']);
        if ($id > 0 && $desc !== '' && $officeId > 0) {
            updateService($conn, $id, $desc, $officeId);
            header("Location: ../includes/services.php");
            exit;
        }
    }

    // ADD service
    if ($desc !== '' && $officeId > 0 && empty($_POST['update_id'])) {
        addService($conn, $desc, $officeId);
        header("Location: ../includes/services.php");
        exit;
    }

    // TOGGLE enable/disable (replaces delete)
    if (isset($_POST['toggle_service'])) {
        $id = intval($_POST['service_id'] ?? 0);
        if ($id > 0) {
            // get current enabled
            $stmt = $conn->prepare("SELECT enabled FROM services WHERE service_id = ?");
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
                $u = $conn->prepare("UPDATE services SET enabled = ? WHERE service_id = ?");
                if ($u) {
                    $u->bind_param("ii", $new, $id);
                    $u->execute();
                    $u->close();
                }
            }
        }
        header("Location: ../includes/services.php");
        exit;
    }
}



function getServiceData($conn) {
    $data = [];
    // Note: ensure services table has 'enabled' column (see provided ALTER TABLE)
    $sql = "SELECT S.service_id, S.service_description, S.office, S.enabled, O.office_description FROM services S INNER JOIN offices O ON S.office = O.office_id";
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

function updateService($conn, $serviceId, $serviceDesc, $officeId) {
    $stmt = $conn->prepare("UPDATE services SET service_description = ?, office = ? WHERE service_id = ?");
    $stmt->bind_param("sii", $serviceDesc, $officeId, $serviceId);
    return $stmt->execute();
}


function deleteService($conn, $id) {
    $id = intval($id);
    $sql = "DELETE FROM services WHERE service_id = $id";
    return $conn->query($sql);
}