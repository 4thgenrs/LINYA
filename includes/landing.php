<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || intval($_SESSION['user_type']) !== 1) {
    echo "Access denied";
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Admin — Overview</title>
  <link rel="stylesheet" href="../css/landing.css" />
</head>
<body>
  <div class="land-wrap">
    <header class="land-header">
      <h1>Admin Overview</h1>
      <div class="muted">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
    </header>

    <section class="land-cards" id="statsCards">
      <!-- cards populated by JS -->
    </section>

    <section class="land-main">
      <div class="chart-panel">
        <h3>Finished Queues per Day (last 14 days)</h3>
        <div id="chartBars" class="chart-bars"></div>
      </div>

      <div class="top-services">
        <h3>Top 5 Services (from finished queues)</h3>
        <ul id="topServices"></ul>
      </div>
    </section>
  </div>

  <script src="../js/landing.js" defer></script>
</body>
</html>