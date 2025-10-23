<?php
header('Content-Type: text/html; charset=utf-8');
include '../linya_db/config.php';

if (!isset($conn)) {
    echo "<p>DB connection missing.</p>";
    exit;
}

if (isset($_GET['office_id'])) {
    $officeId = intval($_GET['office_id']);

    $stmt = $conn->prepare("SELECT * FROM services WHERE office = ?");
    if (!$stmt) {
        echo "<p>DB prepare error: " . htmlspecialchars($conn->error) . "</p>";
        exit;
    }

    $stmt->bind_param("i", $officeId);
    if (!$stmt->execute()) {
        echo "<p>DB execute error: " . htmlspecialchars($stmt->error) . "</p>";
        $stmt->close();
        exit;
    }

    $result = $stmt->get_result();
    if ($result === false) {
        echo "<p>DB get_result error.</p>";
        $stmt->close();
        exit;
    }

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $serviceId = isset($row['service_id']) ? intval($row['service_id']) : 0;
            $desc = isset($row['service_description']) ? $row['service_description'] : 'Service';
            // button contains a checkbox and label; id used for label association
            $cbId = "svc-" . $serviceId;
            echo "<button type='button' class='service-btn' data-service-id='" . $serviceId . "'>";
            // add a value so checkbox.value is available if JS scans checkboxes
            echo "<input type='checkbox' class='service-checkbox' id='" . htmlspecialchars($cbId) . "' value='" . intval($serviceId) . "' />";
            echo "<span class='service-label'>" . htmlspecialchars($desc) . "</span>";
            echo "</button>";
        }
    } else {
        echo "<p>No services found for this office.</p>";
    }

    $stmt->close();
} else {
    echo "<p>Missing office_id</p>";
}