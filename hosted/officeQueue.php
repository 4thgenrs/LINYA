<?php
session_start();
// optionally require login or leave public. Accept office via ?office=#
$office = isset($_GET['office']) ? intval($_GET['office']) : 1;

// load DB config for optional permission check and to lookup office description
require_once __DIR__ . '/../linya_db/config.php';

// If a staff user is signed in, ensure they may view this office's live queue
if (isset($_SESSION['user_id']) && isset($_SESSION['user_type']) && intval($_SESSION['user_type']) === 2) {
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

// Fetch office description so we can render the selector as a single, fixed option
$office_description = null;
$of = $conn->prepare("SELECT office_description FROM offices WHERE office_id = ? LIMIT 1");
if ($of) {
  $of->bind_param('i', $office);
  $of->execute();
  $or = $of->get_result();
  $row = $or ? $or->fetch_assoc() : null;
  if ($row && isset($row['office_description'])) $office_description = $row['office_description'];
  $of->close();
}
if ($office_description === null) $office_description = 'Office ' . $office;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Live Queue - <?php echo htmlspecialchars($office_description, ENT_QUOTES); ?></title>
  <link rel="stylesheet" href="../css/officeQueue.css" />
</head>
<body>
  <div id="root" data-office="<?php echo $office; ?>" data-fixed="1">
    <header class="oq-header">
      <div>
        <div class="oq-title">Live Queue</div>
        <div class="oq-sub">Auto-updates every 3s</div>
      </div>

      <!-- office selector: when accessed via QR we hide the dropdown and show a static office name -->
      <div>
        <div class="oq-office-fixed" aria-hidden="true" style="display:flex;align-items:center;gap:8px;">
          <span class="oq-select-label">Office:</span>
          <strong><?php echo htmlspecialchars($office_description, ENT_QUOTES); ?></strong>
        </div>
        <!-- keep a hidden select with the same id/value so existing JS that queries #officeSelect still works -->
        <select id="officeSelect" class="oq-select" style="display:none;">
          <option value="<?php echo htmlspecialchars($office, ENT_QUOTES); ?>" selected><?php echo htmlspecialchars($office_description, ENT_QUOTES); ?></option>
        </select>
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