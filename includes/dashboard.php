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

$allowedViews = ['activity', 'window', 'office'];
$currentView = $_GET['view'] ?? 'activity';
if (!in_array($currentView, $allowedViews, true)) {
    $currentView = 'activity';
}

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

if ($currentView === 'window' && !$canControl) {
    $currentView = 'activity';
}

if ($currentView === 'office' && !$canViewOffice) {
    $currentView = 'activity';
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
<link rel="stylesheet" href="../css/table-unified.css">
</head>
<body>

<button id="mobileToggle" aria-label="Toggle Menu">
    <i class="fa-solid fa-bars"></i>
</button>

<!-- SIDEBAR LIKE ADMIN -->
<aside id="sidebar" class="sidebar">

    <div class="sidebar-header brand-box">
        <img src="../resources/ucc.png" alt="Logo">
        <div class="brand-text fw-bold">
            Live Interactive<br>Navigation of Your Appointment
        </div>
    </div>

    <div class="nav-section-title">
        <?php echo $office_description ? htmlspecialchars($office_description . ' (' . $office_code . ')') : 'Staff Dashboard'; ?>
    </div>

    <nav class="adm-nav">
        <ul class="sidebar-menu">
            <li>
                <button data-view="activity" class="nav-btn <?php echo $currentView === 'activity' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-house"></i> My Activity
                </button>
            </li>

            <?php if ($canControl): ?>
            <li>
                <button data-view="window" class="nav-btn <?php echo $currentView === 'window' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-sliders"></i> Window Control
                </button>
            </li>
            <?php endif; ?>

            <?php if ($canViewOffice): ?>
            <li>
                <button data-view="office" class="nav-btn <?php echo $currentView === 'office' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-list-check"></i> Live Office Queue
                </button>
            </li>
            <?php endif; ?>
        </ul>
    </nav>

    <div class="user-box sidebar-footer">
        <div class="user-card">
            <div class="user-avatar"><?php echo strtoupper(substr($userName, 0, 3)); ?></div>
            <div class="user-info">
                <div class="fw-semibold"><?php echo $userName; ?></div>
                <small id="assignedOffice">
                    <?php echo $office_description ? htmlspecialchars($office_description . ' (' . $office_code . ')') : '—'; ?>
                </small>
            </div>
        </div>

        <button id="logoutBtn" class="btn btn-light mt-2 w-100">Logout</button>
    </div>
</aside>

<!-- MAIN CONTENT -->
<main class="dash-main">

    <section id="view-activity" class="dash-view activity-view"<?php echo $currentView !== 'activity' ? ' style="display:none;"' : ''; ?>>
        <div class="activity-view-header">
            <div>
                <h2>My Activity</h2>
                <p class="activity-view-subtitle">Track the queues you have recently served, including service details, customer information, and completion time.</p>
            </div>
            <div class="activity-view-badge">
                <i class="fa-solid fa-chart-line"></i>
                Activity Log
            </div>
        </div>
        <div class="activity-shell">
            <div class="activity-shell-header">
                <div>
                    <h3><i class="fa-solid fa-clock-rotate-left me-2"></i>Recent Transactions</h3>
                    <p>Completed queue history for your assigned office and window.</p>
                </div>
            </div>
            <div class="activity-summary-grid">
                <div class="activity-summary-card">
                    <div class="activity-summary-icon is-served">
                        <i class="fa-solid fa-check-double"></i>
                    </div>
                    <div class="activity-summary-content">
                        <span class="activity-summary-label">Queues Served</span>
                        <strong id="activityTotalServed" class="activity-summary-value">0</strong>
                        <span class="activity-summary-meta">Completed transactions in this session view</span>
                    </div>
                </div>
                <div class="activity-summary-card">
                    <div class="activity-summary-icon is-services">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                    <div class="activity-summary-content">
                        <span class="activity-summary-label">Services Handled</span>
                        <strong id="activityServicesHandled" class="activity-summary-value">0</strong>
                        <span class="activity-summary-meta">Total service items across completed queues</span>
                    </div>
                </div>
                <div class="activity-summary-card">
                    <div class="activity-summary-icon is-latest">
                        <i class="fa-regular fa-clock"></i>
                    </div>
                    <div class="activity-summary-content">
                        <span class="activity-summary-label">Latest Completion</span>
                        <strong id="activityLatestCompletion" class="activity-summary-value activity-summary-value-sm">—</strong>
                        <span id="activityLatestCustomer" class="activity-summary-meta">Waiting for activity data</span>
                    </div>
                </div>
            </div>
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
        </div>
    </section>

    <section id="view-window" class="dash-view window-view"<?php echo $currentView !== 'window' ? ' style="display:none;"' : ''; ?>>
        <div class="window-frame-shell">
            <div class="window-frame-body">
                <div class="window-frame-intro">
                    <h3><i class="fa-solid fa-sliders me-2"></i>Window Control</h3>
                    <p>Manage your assigned service window, call the next queue, and handle active customer transactions.</p>
                </div>
                <iframe id="windowFrame" src="../includes/queueSelect.php"></iframe>
            </div>
        </div>
    </section>

    <section id="view-office" class="dash-view office-view"<?php echo $currentView !== 'office' ? ' style="display:none;"' : ''; ?>>
        <div class="office-view-header">
            <div>
                <h2>Live Office Queue</h2>
                <p class="office-view-subtitle">Monitor active window queues and pending customers for your assigned office in real time.</p>
                <div id="officeLastUpdated" class="office-last-updated">
                    <i class="fa-regular fa-clock me-2"></i>Last updated: —
                </div>
            </div>
            <div class="office-view-badge">
                <i class="fa-solid fa-wave-square"></i>
                Live Monitor
            </div>
        </div>
        <div class="row g-4 office-view-grid">
            <div class="col-12 col-lg-8">
                <div class="office-panel office-panel-main">
                    <div class="office-panel-header">
                        <div>
                            <h3><i class="fa-solid fa-desktop me-2"></i>Window Status</h3>
                            <p>Current queues being served across active windows.</p>
                        </div>
                    </div>
                    <div id="officeQueueContainer" class="office-panel-body">Loading…</div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="office-panel office-panel-side">
                    <div class="office-panel-header">
                        <div>
                            <h3><i class="fa-solid fa-hourglass-half me-2"></i>Pending Queue</h3>
                            <p>Customers waiting to be called next.</p>
                        </div>
                    </div>
                    <div id="officePending" class="office-panel-body">Loading…</div>
                </div>
            </div>
        </div>
    </section>


</main>

<!-- JS SESSION PASSTHROUGH -->
<script>
window.__LINYA_SESSION = {
    office: <?php echo json_encode($office); ?>,
    window_no: <?php echo json_encode($window_no); ?>,
    office_description: <?php echo json_encode($office_description); ?>,
    office_code: <?php echo json_encode($office_code); ?>,
    current_view: <?php echo json_encode($currentView); ?>,
    can_control: <?php echo json_encode($canControl); ?>,
    can_view_office: <?php echo json_encode($canViewOffice); ?>
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
