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
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Dashboard — LINYA</title>
  <link rel="stylesheet" href="../css/dashboard.css" />
</head>
<body>
  <header class="dash-header">
    <div class="brand-container">
      <img src="/LATESTLINYA/resources/ucc.png" alt="UCC Logo" class="brand-logo">
      <div class="brand">LINYA — Staff Dashboard</div>
    </div>
    <nav class="dash-nav" role="navigation">
      <button data-view="activity" class="nav-btn active">My Activity</button>
      <button data-view="window" class="nav-btn">Window Control</button>
      <button data-view="office" class="nav-btn">Live Office Queue</button>
    </nav>
    <div class="dash-right">
      <div class="small muted">Signed in as <strong><?php echo $userName; ?></strong></div>
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
      window_no: <?php echo json_encode($window_no); ?>
    };
  </script>
  <script src="../js/dashboard.js" defer></script>
</body>
</html>