<?php include '../linya_db/config.php'; session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kiosk Menu</title>
    <link rel="stylesheet" href="../css/kioskMenu.css">
</head>
<body>
    <div class="kiosk-container">

        <!-- VISITOR FORM (shown first) -->
        <div id="visitorForm" class="visitor-screen">
            <h1 class="title">Visitor Details</h1>
            <p class="muted small">Please enter your details before proceeding.</p>

            <form id="visitorDetails" onsubmit="return false;">
                <label class="vf-field">
                    <div class="vf-label">Name</div>
                    <input type="text" id="visitorName" placeholder="Full name" autocomplete="name" required />
                </label>

                <label class="vf-field">
                    <div class="vf-label">Phone</div>
                    <input type="tel" id="visitorPhone" placeholder="09XXXXXXXXX" inputmode="numeric" required />
                    <div id="visitorPhoneHint" class="vf-hint" style="display:none;color:#da1e28">Phone must be 11 digits and start with 09</div>
                </label>

                <label class="vf-field">
                    <div class="vf-label">Email (optional)</div>
                    <input type="email" id="visitorEmail" placeholder="you@example.com" autocomplete="email" />
                    <div id="visitorEmailHint" class="vf-hint" style="display:none;color:#da1e28">Invalid email address</div>
                </label>

                <div class="vf-actions">
                    <button id="visitorProceed" class="primary-btn" type="button" disabled>Proceed</button>
                </div>
            </form>
        </div>

        <!-- CONFIRMATION MODAL -->
        <div id="visitorConfirmModal" class="vf-modal" style="display:none">
            <div class="vf-modal-card">
                <h2>Confirm details</h2>
                <div class="vf-confirm-row"><strong>Name:</strong> <span id="confirmName"></span></div>
                <div class="vf-confirm-row"><strong>Phone:</strong> <span id="confirmPhone"></span></div>
                <div class="vf-confirm-row"><strong>Email:</strong> <span id="confirmEmail"></span></div>

                <div class="vf-actions" style="margin-top:12px">
                    <button id="visitorCancel" class="ghost-btn" type="button">Go back</button>
                    <button id="visitorConfirm" class="primary-btn" type="button">Confirm & Enter Kiosk</button>
                </div>
            </div>
        </div>

        <!-- EXISTING OFFICE / SERVICE SECTIONS -->
        <div id="officeSection" class="screen active">
            <h1 class="title">Select an Office</h1>
            <div class="grid" id="officeGrid">
                <?php
                // changed SQL: only show offices with enabled = 1
                $sql = "SELECT * FROM offices WHERE enabled = 1 ORDER BY office_description ASC";
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
    <?php
    // If a client session exists (face-login), inject JS to prefill and auto-enter
    if (isset($_SESSION['client']) && is_array($_SESSION['client'])) {
        $client_js = json_encode($_SESSION['client']);
        echo "<script>window.__CLIENT_SESSION = $client_js;</script>";
        echo "<script>
            document.addEventListener('DOMContentLoaded', function(){
                try {
                    const c = window.__CLIENT_SESSION;
                    if (c) {
                        const nameEl = document.getElementById('visitorName');
                        const phoneEl = document.getElementById('visitorPhone');
                        const emailEl = document.getElementById('visitorEmail');
                        if (nameEl) nameEl.value = c.name || '';
                        if (phoneEl) phoneEl.value = c.phone || '';
                        if (emailEl) emailEl.value = c.email || '';
                        // sync state and auto-proceed to kiosk
                        if (typeof visitorSyncState === 'function') visitorSyncState();
                        // simulate confirm after short delay
                        setTimeout(function(){
                            // hide visitor form and enter
                            const visitor = document.getElementById('visitorForm');
                            const officeSection = document.getElementById('officeSection');
                            if (visitor) visitor.style.display = 'none';
                            if (officeSection) { officeSection.style.display = 'block'; setTimeout(()=>officeSection.classList.add('active'),80); }
                            // populate global visitorInfo
                            window.visitorInfo = { name: c.name || '', phone: c.phone || '', email: c.email || '' };
                        }, 250);
                    }
                } catch (e) { console.error(e); }
            });
        </script>";
    }
    ?>
</body>
</html>
