<?php
session_start();
// optionally require login or leave public. Accept office via ?office=#
$office = isset($_GET['office']) ? intval($_GET['office']) : 1;

// If a staff user is signed in, ensure they may view this office's live queue
if (isset($_SESSION['user_id']) && isset($_SESSION['user_type']) && intval($_SESSION['user_type']) === 2) {
  require_once __DIR__ . '/../linya_db/config.php';
  $staffId = intval($_SESSION['user_id']);
  $chk = $conn->prepare("SELECT office_id FROM office_windows WHERE assigned_staff_id = ? LIMIT 1");
  if ($chk) {
    $chk->bind_param('i', $staffId);
    $chk->execute();
    $cres = $chk->get_result();
    $assigned = $cres ? $cres->fetch_assoc() : null;
    $chk->close();

    if (!$assigned || intval($assigned['office_id']) !== $office) {
      header('Location: ../includes/dashboard.php');
      exit;
    }
  }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Live Queue - Office <?php echo $office; ?></title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="../css/officeQueue.css">
</head>
<body class="py-4">
  <div class="container-fluid" id="root" data-office="<?php echo $office; ?>">

    <!-- Header -->
    <header class="oq-header">
      <div class="oq-header-copy">
        <div class="oq-badge">
          <i class="fa-solid fa-wave-square"></i>
          Live Monitoring
        </div>
        <div class="oq-title">Live Queue</div>
        <div class="oq-sub">Track active windows, monitor pending queues, and stay synced with updates every 3 seconds.</div>
      </div>

      <div class="oq-header-side">
        <div class="oq-status-pill">
          <span class="oq-status-dot"></span>
          Realtime feed
        </div>

        <div class="oq-select-wrap">
          <label for="officeSelect" class="oq-select-label">Office</label>
          <select id="officeSelect" class="oq-select"></select>
        </div>
      </div>
    </header>

    <!-- Main Layout -->
    <main class="oq-main">
      <section id="windowsContainer">
        <!-- JS inserts .window-card here -->
      </section>

      <aside class="oq-pending">
        <div class="oq-pending-head">
          <div>
            <span class="oq-panel-eyebrow">Waiting List</span>
            <h3>Pending Queues</h3>
          </div>
          <div class="oq-panel-icon">
            <i class="fa-solid fa-hourglass-half"></i>
          </div>
        </div>
        <div id="pendingList" class="pending-list">
          <!-- JS inserts .pending-item here -->
        </div>
      </aside>
    </main>
    
  </div>

  <script src="../js/officeQueue.js" defer></script>
</body>
</html>
