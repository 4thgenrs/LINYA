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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <link rel="stylesheet" href="../css/landing.css" />
</head>
<body>  
  <div class="container-fluid py-3 landing-shell"> 
    <header class="land-header">
      <div class="land-header-copy">
        <div class="land-header-badge">
          <i class="fa-solid fa-chart-pie"></i>
          Admin Overview
        </div>
        <h1>Dashboard Insights</h1>
        <p class="land-header-subtitle">Monitor accounts, offices, queue activity, and service demand from one live overview panel.</p>
      </div>
      <div class="land-header-user">
        <div class="land-user-status">
          <span class="land-live-dot"></span>
          Auto refresh every 5 seconds
        </div>
        <span class="land-user-label">Signed in as</span>
        <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong>
        <span id="landingRefreshStamp" class="land-refresh-stamp">Waiting for first update</span>
      </div>
    </header>

    <section class="land-cards" id="statsCards">
      <!-- cards populated by JS -->
    </section>

    <section class="land-main">
      <div class="chart-panel">
        <div class="panel-heading">
          <div>
            <span class="panel-eyebrow">Analytics</span>
            <h3>Finished Queues per Day</h3>
            <p>Queue completion trend across the last 14 days.</p>
          </div>
          <div class="panel-icon is-chart"><i class="fa-solid fa-chart-column"></i></div>
        </div>
        <div id="chartBars" class="chart-bars"></div>
      </div>

      <div class="top-services">
        <div class="panel-heading">
          <div>
            <span class="panel-eyebrow">Services</span>
            <h3>Top 5 Services</h3>
            <p>Most requested services based on completed queues.</p>
          </div>
          <div class="panel-icon is-services"><i class="fa-solid fa-layer-group"></i></div>
        </div>
        <ul id="topServices"></ul>
      </div>
    </section>
  </div>

  <script src="../js/landing.js" defer></script>
</body>
</html>