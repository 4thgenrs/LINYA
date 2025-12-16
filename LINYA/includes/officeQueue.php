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
  <link rel="stylesheet" href="../css/officeQueue.css" />
</head>
<body>
  <div id="root" data-office="<?php echo $office; ?>">
    <header class="oq-header">
      <div>
        <div class="oq-title">Live Queue</div>
        <div class="oq-sub">Auto-updates every 3s</div>
      </div>

      <!-- office selector -->
      <div>
        <label for="officeSelect" class="oq-select-label">Office:</label>
        <select id="officeSelect" class="oq-select"></select>
      </div>
    </header>

    <main class="oq-main">
      <section class="oq-windows" id="windowsContainer" aria-label="Windows"></section>

      <aside class="oq-pending">
        <h3>Pending Queues</h3>
        <div id="pendingList" class="pending-list"></div>
      </aside>
    </main>
  </div>

  <script src="../js/officeQueue.js" defer></script>
</body>
</html>