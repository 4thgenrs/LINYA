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
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Dashboard — LINYA<?php echo $office_description ? ' — '.htmlspecialchars($office_description) : ''; ?></title>
  <link rel="stylesheet" href="../css/dashboard.css" />
</head>
<body>
  <header class="dash-header">
    <div class="brand">LINYA — Staff Dashboard</div>
    <nav class="dash-nav" role="navigation">
      <button data-view="activity" class="nav-btn active">My Activity</button>
      <button data-view="window" class="nav-btn">Window Control</button>
      <button data-view="office" class="nav-btn">Live Office Queue</button>
    </nav>
    <div class="dash-right">
      <div class="small muted">Signed in as <strong><?php echo $userName; ?></strong></div>
      <div class="small muted">Office: <strong id="assignedOffice"><?php echo $office_description ? htmlspecialchars($office_description.' ('.$office_code.')') : '—'; ?></strong></div>
      <button id="logoutBtn" class="btn-ghost">Logout</button>
    </div>
  </header>

  <main class="dash-main">
    <section id="view-activity" class="dash-view">
      <h2>My Activity</h2>
      <div id="activityContainer" class="activity-wrap">Loading…</div>
    </section>

    <section id="view-window" class="dash-view" style="display:none">
      <h2>Window Control</h2>
      <!-- open the full window control page in an iframe so staff tools remain available -->
      <iframe id="windowFrame" src="../includes/queueSelect.php" title="Window Control" frameborder="0"></iframe>
    </section>

    <section id="view-office" class="dash-view" style="display:none">
      <h2>Live Office Queue</h2>
      <div id="officeQueueContainer">Loading…</div>
      <h3>Pending</h3>
      <div id="officePending">Loading…</div>
    </section>
  </main>

  <script>
    // expose initial session values to the dashboard script (safe small read-only)
    window.__LINYA_SESSION = {
      office: <?php echo json_encode($office); ?>,
      window_no: <?php echo json_encode($window_no); ?>,
      office_description: <?php echo json_encode($office_description); ?>,
      office_code: <?php echo json_encode($office_code); ?>
    };
  </script>
  <script src="../js/dashboard.js" defer></script>
</body>
</html>
