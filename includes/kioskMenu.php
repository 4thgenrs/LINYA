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
            <h1 class="title">Select an Office</h1>
            <div class="grid" id="officeGrid">
                <?php
                $sql = "SELECT * FROM offices";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $id = $row['office_id'];
                        $desc = htmlspecialchars($row['office_description']);
                        echo "<button class='office-btn' onclick='loadServices($id, " . json_encode($row['office_description']) . ")'>$desc</button>";
                    }
                } else {
                    echo "<p class='muted'>No offices found.</p>";
                }
                ?>
            </div>
        </div>

        <div id="serviceSection" class="screen">
            <div class="header-row">
                <h1 class="title" id="serviceTitle">Services</h1>
                <button id="backBtn" class="ghost-btn" type="button">Back</button>
            </div>

            <div class="controls-row">
                <button id="multiSelectToggle" class="secondary-btn" type="button">Select multiple</button>
                <div class="muted small" id="hint">
                    Tap a service to select — press Confirm to continue.
                    Toggle the PDF switch after selecting a service to request a soft copy.
                </div>
            </div>

            <div class="grid cards" id="serviceGrid"></div>

            <div id="serviceActions" class="service-actions">
                <div id="selectedCount" class="muted">0 selected</div>
                <button id="confirmBtn" class="primary-btn" type="button" disabled>Confirm</button>
            </div>
        </div>
    </div>

    <script src="../js/kioskMenu.js"></script>
</body>
</html>
