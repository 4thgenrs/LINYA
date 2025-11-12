<?php
header('Content-Type: text/html; charset=utf-8');
include '../linya_db/config.php';

if (!isset($conn)) {
    echo "<p>DB connection missing.</p>";
    exit;
}

if (isset($_GET['office_id'])) {
    $officeId = intval($_GET['office_id']);

    $stmt = $conn->prepare("SELECT * FROM services WHERE office = ? ORDER BY service_description ASC");
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
            $cbId = "svc-" . $serviceId;

            // card-style service item with per-service soft toggle (PDF)
            echo "<div class='service-card' data-service-id='" . $serviceId . "'>";
            echo "  <div class='service-main'>";
            echo "    <div class='service-info'>";
            echo "      <div class='service-title'>" . htmlspecialchars($desc) . "</div>";
            echo "    </div>";
            // hidden selection checkbox (for multi-select) kept for keyboard/accessibility
            echo "    <input type='checkbox' class='service-checkbox' id='" . htmlspecialchars($cbId) . "' value='" . intval($serviceId) . "' aria-hidden='true' />";
            echo "  </div>";

            // per-service soft toggle (modern switch) - DISABLED by default until service selected
            echo "  <div class='service-controls'>";
            echo "    <label class='switch' title='Request soft copy (PDF)'>";
            echo "      <input type='checkbox' class='service-soft-checkbox' data-service-id='" . intval($serviceId) . "' disabled />";
            echo "      <span class='slider'></span>";
            echo "    </label>";
            echo "    <div class='soft-label'>PDF</div>";
            echo "  </div>";

            echo "</div>";
        }
    } else {
        echo "<p class='muted'>No services found for this office.</p>";
    }

    $stmt->close();
} else {
    echo "<p class='muted'>Missing office_id</p>";
}