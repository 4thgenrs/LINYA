<?php include '../linya_db/config.php';
session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiosk Menu</title>
    <link rel="stylesheet" href="../css/kioskMenu.css">
</head>

<body>
    <!-- Visitor Form Section -->
    <div class="kiosk-container">
        <div id="visitorForm" class="visitor-screen">
            <div class="visitor-Title">
                <h1 class="title">Visitor Details</h1>
                <p class="muted small">Please enter your details before proceeding</p>
            </div>

            <form class="visitorDetails" id="visitorDetails" onsubmit="return false;">
                <label class="vf-field">
                    <div class="vf-label">Name:</div>
                    <input type="text" id="visitorName" placeholder="e.g., Juan T. Dela Cruz" autocomplete="name" required />
                </label>

                <label class="vf-field">
                    <div class="vf-label">Phone:</div>
                    <input type="tel" id="visitorPhone" placeholder="09XXXXXXXXX" inputmode="numeric" required />
                    <div id="visitorPhoneHint" class="vf-hint" style="display:none;color:#ff0000">Phone must be 11 digits and start with 09</div>
                </label>

                <label class="vf-field">
                    <div class="vf-label">Email (optional):</div>
                    <input type="email" id="visitorEmail" placeholder="you@example.com" autocomplete="email" />
                    <div id="visitorEmailHint" class="vf-hint" style="display:none;color:#ff0000">Invalid email address</div>
                </label>

                <div class="vf-actions">
                    <button id="visitorProceed" class="primary-btn" type="button" disabled>Proceed</button>
                    <a href="kioskSelect.php" class="back-btn" title="Go back to selection">
                        Go Back
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Visitor Confirmation Modal - MOVED OUTSIDE KIOSK CONTAINER -->
    <div id="visitorConfirmModal" class="vf-modal" style="display:none">
        <div class="vf-modal-card">
            <h2>CONFIRM DETAILS</h2>

            <div class="confirm-instruction">
                Please review your information carefully before proceeding
            </div>

            <div class="vf-confirm-row"><strong>Name:</strong> <span id="confirmName"></span></div>
            <div class="vf-confirm-row"><strong>Phone:</strong> <span id="confirmPhone"></span></div>
            <div class="vf-confirm-row"><strong>Email:</strong> <span id="confirmEmail"></span></div>

            <div class="vf-actions" style="margin-top:12px">
                <button id="visitorConfirm" class="vf-modal-confirm" type="button">Confirm & Enter Kiosk</button>
                <button id="visitorCancel" class="vf-modal-ghost" type="button">Go back</button>
            </div>
        </div>
    </div>

    <!-- Office Section -->
    <div id="officeSection" class="screen" style="display: none;">
        <div class="office-section-container">
            <div class="officeTitle">
                <h1 class="title">Select an Office</h1>
                <div class="office-instruction">
                    Please choose the office you wish to visit from the options below
                </div>
            </div>

            <div class="grid" id="officeGrid">
                <?php
                $sql = "SELECT * FROM offices WHERE enabled = 1 ORDER BY office_description ASC";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $id = $row['office_id'];
                        $code = htmlspecialchars($row['office_code']);
                        $desc = htmlspecialchars($row['office_description']);
                        echo "
                            <button class='office-btn' onclick='loadServices($id, " . json_encode($row['office_description']) . ")'>
                                <span class='office-code'>$desc</span>
                                <span class='office-desc'>( $code )</span>
                            </button>";
                    }
                } else {
                    echo "<p class='muted'>No offices found.</p>";
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Service Selection Section -->
    <div class="service-section-container" style="display: none;">
        <div id="serviceSection" class="screen">
            <div class="header-row">
                <h6 class="title" id="serviceTitle">Services</h6>
            </div>
            <div class="mute small" id="hint">
                Tap a service to select and press Confirm to continue.
                Toggle the PDF switch after selecting a service to request a soft copy.
            </div>
            <div class="controls-row">
                <div class="controls-btn">
                    <button id="multiSelectToggle" class="secondary-btn" type="button">Select multiple</button>
                    <button id="backBtn" class="ghost-btn" type="button">Back</button>
                </div>
            </div>

            <div class="grid cards" id="serviceGrid"></div>

            <div id="serviceActions" class="service-actions">
                <div id="selectedCount" class="muted">0 selected</div>
                <button id="confirmBtn" class="primary-btn" type="button" disabled>Confirm</button>
            </div>
        </div>
    </div>

    <?php
    if (isset($_SESSION['client']) && is_array($_SESSION['client'])) {
        $client_js = json_encode($_SESSION['client']);
        echo "<script>window.SKIP_VISITOR_FORM = true; window.__CLIENT_SESSION = $client_js;</script>";
    }
    ?>
    <script src="../js/kioskMenu.js"></script>
</body>

</html>