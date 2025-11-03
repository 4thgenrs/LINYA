<?php
session_start();
// require login
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || intval($_SESSION['user_type']) !== 2) {
    header('Location: login.php');
    exit;
}

// session-provided office/window (fallback to query params)
$office = isset($_SESSION['office_id']) ? intval($_SESSION['office_id']) : (isset($_GET['office']) ? intval($_GET['office']) : 1);
$window_no = isset($_SESSION['window_no']) ? intval($_SESSION['window_no']) : (isset($_GET['window']) ? intval($_GET['window']) : 1);
$user_name = htmlspecialchars($_SESSION['user_name']);
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Queue Window</title>
  <link rel="stylesheet" href="../css/queueSelect.css" />
</head>
<body>
  <div class="wrap">
    <div class="box" role="group" aria-label="Window Control" id="boxRoot" data-office="<?php echo $office; ?>" data-window="<?php echo $window_no; ?>">
      <div style="width:100%;display:flex;justify-content:space-between;align-items:center">
        <div id="window" class="window">Window <?php echo $window_no; ?></div>
        <div style="text-align:right">
          <div style="font-size:13px;color:#6b7280">Signed in as</div>
          <div style="font-weight:700"><?php echo $user_name; ?></div>
        </div>
      </div>

      <div id="number" class="number">Loading…</div>
      <div><i id="ongoingText"></i></div>

      <div class="controls">
        <button id="finished" type="button" disabled>Finished</button>
        <button id="onBreak" type="button" data-break="false">Break</button>
        <button id="nextQueue" type="button">Call Next</button>
        <button id="logoutBtn" type="button" style="background:#f3f4f6;color:#111;border-radius:8px;padding:8px 10px;border:1px solid rgba(11,18,48,0.04)">Logout</button>
      </div>
    </div>

    <div class="queue-box" aria-label="Queue List">
      <div class="queue-title">Queue List</div>
      <div id="queueListContainer"></div>
    </div>
  </div>

  <script src="../js/queueSelect.js"></script>
</body>
</html>