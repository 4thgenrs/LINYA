<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || intval($_SESSION['user_type']) !== 1) {
    header('Location: login.php');
    exit;
}

include_once('../linya_db/config.php');

$userName = htmlspecialchars($_SESSION['user_name']);
$office_description = '';
$office_code = '';

// Fetch office details if office_id exists in session
if (isset($_SESSION['office_id']) && $_SESSION['office_id'] > 0) {
    $office_id = intval($_SESSION['office_id']);
    $stmt = $conn->prepare("SELECT office_code, office_description FROM offices WHERE office_id = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('i', $office_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $office_description = $row['office_description'];
            $office_code = $row['office_code'];
        }
        $stmt->close();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Admin Dashboard — LINYA</title>

  <!-- BOOTSTRAP + FONTAWESOME -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../css/adminDashboard.css">
</head>
<body>

  <!-- MOBILE TOGGLE BUTTON -->
  <button id="mobileToggle" aria-label="Toggle Menu">
    <i class="fa-solid fa-bars"></i>
  </button>

  <!-- SIDEBAR -->
  <aside id="sidebar">
    <div class="brand-box">
      <img src="../logo.png" alt="Logo">
      <div class="brand-text fw-bold">Live Interactive<br>Navigation of Your Appointment</div>
    </div>

    <nav class="adm-nav">

      <button data-module="landing" class="adm-btn active">
        <i class="fa-solid fa-house"></i> Dashboard
      </button>

      <button data-module="adminClients" class="adm-btn">
        <i class="fa-solid fa-users"></i> Face Clients
      </button>

      <button data-module="finishedQueue" class="adm-btn">
        <i class="fa-solid fa-clipboard-check"></i> Finished Queues
      </button>

      <div class="adm-dropdown">
      <button class="dropdown-toggle" type="button" aria-expanded="false">
        <i class="fa-solid fa-gear"></i> Management
        <!-- <i class="fa-solid fa-chevron-down"></i> -->
      </button>

  <div class="dropdown-menu">
    <button data-module="accounts" class="dropdown-item adm-btn">
      <i class="fa-solid fa-user-gear"></i> Accounts
    </button>

    <button data-module="assignStaff" class="dropdown-item adm-btn">
      <i class="fa-solid fa-user-tag"></i> Assign Staff
    </button>

    <button data-module="office" class="dropdown-item adm-btn">
      <i class="fa-solid fa-building"></i> Offices
    </button>

    <button data-module="services" class="dropdown-item adm-btn">
      <i class="fa-solid fa-briefcase"></i> Services
    </button>
  </div>
</div>

      <button data-module="queueDisplay" class="adm-btn">
        <i class="fa-solid fa-tv"></i> Queue Display
      </button>

      <button data-module="officeQueue" class="adm-btn">
        <i class="fa-solid fa-list-check"></i> Live Office Queue
      </button>

    </nav>


    <div class="user-box">
      <div class="user-card">
        <div class="user-avatar">
          <?php echo strtoupper(substr($userName, 0, 3)); ?>
        </div>
        <div class="user-info">
          <div><strong><?php echo $userName; ?></strong></div>
          <small>
              <?php echo $office_description ? htmlspecialchars($office_description . ' (' . $office_code . ')') : '—'; ?>
          </small>
        </div>
      </div>
      <button id="adminLogout" class="btn btn-sm mt-2 w-100">Logout</button>
    </div>
  </aside>

  <!-- MAIN -->
  <main class="adm-main">
    <iframe id="moduleFrame" src="../includes/landing.php"></iframe>
  </main>

  <script>
    window.__ADMIN_SESSION = { user_name: <?php echo json_encode($userName); ?> };
  </script>
  <script src="../js/adminDashboard.js" defer></script>


</body>
</html>
