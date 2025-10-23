    
<?php include '../linya_db/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kiosk Menu</title>
    <link rel="stylesheet" href="../css/kioskMenu.css">
</head>
<body>
    <div class="kiosk-container">
        <div id="officeSection" class="screen active">
            <h2>Select an Office</h2>
            <div class="grid" id="officeGrid">
                <?php
                $sql = "SELECT * FROM offices";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $id = $row['office_id'];
                        $desc = htmlspecialchars($row['office_description']);
                        // keep using json_encode for safe JS string
                        echo "<button class='office-btn' onclick='loadServices($id, " . json_encode($row['office_description']) . ")'>$desc</button>";
                    }
                } else {
                    echo "<p>No offices found.</p>";
                }
                ?>
            </div>
        </div>

        <div id="serviceSection" class="screen">
            <h2 id="serviceTitle">Services</h2>

            <!-- external toggle for multi-select: no inline onclick -->
            <div style="margin-bottom:12px;">
                <button id="multiSelectToggle" class="back-btn" type="button">Select multiple</button>
            </div>

            <div class="grid" id="serviceGrid"></div>

            <div id="serviceActions" class="service-actions">
                <span id="selectedCount">0 selected</span>
                <button id="confirmBtn" class="back-btn" type="button">Confirm</button>
            </div>

            <!-- back button without inline onclick -->
            <button id="backBtn" class="back-btn" type="button">⬅ Back</button>
        </div>
    </div>

    <script src="../js/kioskMenu.js"></script>
</body>
</html>
