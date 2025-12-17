<?php
session_start();
// require staff login
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || intval($_SESSION['user_type']) !== 2) {
    header('Location: login.php');
    exit;
}
$userName = htmlspecialchars($_SESSION['user_name']);
$office = isset($_SESSION['office_id']) ? intval($_SESSION['office_id']) : 0;
$window_no = isset($_SESSION['window_no']) ? intval($_SESSION['window_no']) : 0;

// load office description/code for display in header
$office_description = null;
$office_code = null;
require_once __DIR__ . '/../linya_db/config.php';
if ($office > 0 && isset($conn)) {
    $ostmt = $conn->prepare("SELECT office_description, office_code FROM offices WHERE office_id = ? LIMIT 1");
    if ($ostmt) {
        $ostmt->bind_param('i', $office);
        $ostmt->execute();
        $ores = $ostmt->get_result();
        if ($row = $ores->fetch_assoc()) {
            $office_description = $row['office_description'];
            $office_code = $row['office_code'];
        }
        $ostmt->close();
    }
}

// Determine if staff is assigned to a window (server-side)
$canControl = false;
$canViewOffice = ($office > 0);

if (isset($conn) && isset($_SESSION['user_id']) && intval($_SESSION['user_type']) === 2) {
    $staffId = intval($_SESSION['user_id']);

    // read the account's office field; if account.office is null/0, don't show window control
    $accountOffice = 0;
    $acctStmt = $conn->prepare("SELECT office FROM accounts WHERE user_id = ? LIMIT 1");
    if ($acctStmt) {
        $acctStmt->bind_param('i', $staffId);
        $acctStmt->execute();
        $ares = $acctStmt->get_result();
        if ($ares && ($arow = $ares->fetch_assoc())) {
            $accountOffice = isset($arow['office']) ? intval($arow['office']) : 0;
        }
        $acctStmt->close();
    }

    $aw = $conn->prepare("SELECT window_id, office_id FROM office_windows WHERE assigned_staff_id = ? LIMIT 1");
    if ($aw) {
        $aw->bind_param('i', $staffId);
        $aw->execute();
        $awr = $aw->get_result();
        if ($awr && ($ar = $awr->fetch_assoc())) {
            if (!empty($ar['window_id']) && $accountOffice > 0) {
                $canControl = true;
            }
            if (!empty($ar['office_id'])) $canViewOffice = true;
        }
        $aw->close();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Dashboard — LINYA<?php echo $office_description ? ' — '.htmlspecialchars($office_description) : ''; ?></title>

<!-- BOOTSTRAP + FONTAWESOME -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
<link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>

<!-- SIDEBAR LIKE ADMIN -->
<aside id="sidebar">

    <div class="brand-box">
        <img src="../logo.png" alt="Logo">
        <div class="brand-text fw-bold">
            Live Interactive<br>Navigation of Your Appointment
        </div>
    </div>

    <nav>
        <button data-view="activity" class="nav-btn active">
            <i class="fa-solid fa-house"></i> My Activity
        </button>

        <?php if ($canControl): ?>
        <button data-view="window" class="nav-btn">
            <i class="fa-solid fa-sliders"></i> Window Control
        </button>
        <?php endif; ?>

        <?php if ($canViewOffice): ?>
        <button data-view="office" class="nav-btn">
            <i class="fa-solid fa-list-check"></i> Live Office Queue
        </button>
        <?php endif; ?>
    </nav>

    <div class="user-box">
        <div><strong><?php echo $userName; ?></strong></div>
        <small>
            <?php echo $office_description ? htmlspecialchars($office_description . ' (' . $office_code . ')') : '—'; ?>
        </small>

        <button id="logoutBtn" class="btn btn-sm mt-2 w-100">Logout</button>
    </div>
</aside>

<!-- MAIN CONTENT -->
<main class="dash-main">

    <section id="view-activity" class="dash-view">
        <h2>My Activity</h2>
        <div id="activityContainer">
            <table id="activityTable" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Queue Code</th>
                        <th>Service</th>
                        <th>Customer</th>
                        <th>Contact</th>
                        <th>Window</th>
                        <th>Finished At</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Populated by JS -->
                </tbody>
            </table>
        </div>
    </section>

    <section id="view-window" class="dash-view">
        <h2>Window Control</h2>
        <iframe id="windowFrame" src="../includes/queueSelect.php"></iframe>
    </section>

    <section id="view-office" class="dash-view" style="display:none;">
        <h2>Live Office Queue</h2>
        <div class="row">
            <div id="officeQueueContainer" class="col-12 col-lg-8 mb-3">Loading…</div>
            <div id="officePending" class="col-12 col-lg-4">Loading…</div>
        </div>
    </section>


</main>

<!-- JS SESSION PASSTHROUGH -->
<script>
window.__LINYA_SESSION = {
    office: <?php echo json_encode($office); ?>,
    window_no: <?php echo json_encode($window_no); ?>,
    office_description: <?php echo json_encode($office_description); ?>,
    office_code: <?php echo json_encode($office_code); ?>
};
</script>

<script src="../js/dashboard.js" defer></script>

<!-- DataTables JS -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

</body>
</html>
