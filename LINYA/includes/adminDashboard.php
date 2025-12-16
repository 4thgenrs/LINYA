
<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || intval($_SESSION['user_type']) !== 1) {
    header('Location: login.php');
    exit;
}
$userName = htmlspecialchars($_SESSION['user_name']);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Admin Dashboard — LINYA</title>
  <link rel="stylesheet" href="../css/adminDashboard.css" />
</head>
<body>
  <header class="adm-header">
    <div class="brand">LINYA — Admin</div>
    <nav class="adm-nav">
      <button data-module="landing" class="adm-btn active">Home</button>
      <button data-module="accounts" class="adm-btn">Accounts</button>
      <button data-module="adminClients" class="adm-btn">Face Clients</button>
      <button data-module="assignStaff" class="adm-btn">Assign Staff</button>
      <button data-module="finishedQueue" class="adm-btn">Finished Queues</button>
      <button data-module="office" class="adm-btn">Offices</button>
      <button data-module="services" class="adm-btn">Services</button>
      <button data-module="queueDisplay" class="adm-btn">Queue Display</button>
      <button data-module="officeQueue" class="adm-btn">Live Office Queue</button>
    </nav>
    <div class="adm-right">
      <div class="muted">Signed in as <strong><?php echo $userName; ?></strong></div>
      <button id="adminLogout" class="btn-ghost">Logout</button>
    </div>
  </header>

  <main class="adm-main">
    <iframe id="moduleFrame" src="../includes/landing.php" frameborder="0"></iframe>
  </main>

  <script>
    window.__ADMIN_SESSION = { user_name: <?php echo json_encode($userName); ?> };
  </script>
  <script src="../js/adminDashboard.js" defer></script>
</body>
</html>