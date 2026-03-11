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

$moduleMap = [
  'landing' => '../includes/landing.php',
  'accounts' => '../includes/accounts.php',
  'adminClients' => '../includes/adminClients.php',
  'assignStaff' => '../includes/assignStaff.php',
  'finishedQueue' => '../includes/finishedQueue.php',
  'office' => '../includes/office.php',
  'services' => '../includes/services.php',
  'queueDisplay' => '../includes/queueDisplay.php',
  'officeQueue' => '../includes/officeQueue.php'
];

$currentModule = $_GET['module'] ?? 'landing';
if (!array_key_exists($currentModule, $moduleMap)) {
  $currentModule = 'landing';
}
$moduleSrc = $moduleMap[$currentModule];

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
  <aside id="sidebar" class="sidebar">
    <div class="sidebar-header brand-box">
      <img src="../resources/ucc.png" alt="Logo">
      <h4 class="brand-text fw-bold">Live Interactive<br>Navigation of Your Appointment</h4>
    </div>

    <div class="nav-section-title">
      <?php echo $office_description ? htmlspecialchars($office_description . ' (' . $office_code . ')') : 'Administrator'; ?>
    </div>

    <nav class="adm-nav">
      <ul class="sidebar-menu">
        <li>
          <button data-module="landing" class="adm-btn <?php echo $currentModule === 'landing' ? 'active' : ''; ?>">
            <i class="fa-solid fa-house"></i> Dashboard
          </button>
        </li>

        <li>
          <button data-module="adminClients" class="adm-btn <?php echo $currentModule === 'adminClients' ? 'active' : ''; ?>">
            <i class="fa-solid fa-users"></i> Face Clients
          </button>
        </li>

        <li>
          <button data-module="finishedQueue" class="adm-btn <?php echo $currentModule === 'finishedQueue' ? 'active' : ''; ?>">
            <i class="fa-solid fa-clipboard-check"></i> Finished Queues
          </button>
        </li>

        <li class="adm-dropdown">
          <button class="dropdown-toggle" type="button" aria-expanded="false">
            <i class="fa-solid fa-gear"></i> Management
            <!-- <i class="fa-solid fa-chevron-down ms-auto"></i> -->
          </button>

          <div class="dropdown-menu">
            <button data-module="accounts" class="dropdown-item adm-btn <?php echo $currentModule === 'accounts' ? 'active' : ''; ?>">
              <i class="fa-solid fa-user-gear"></i> Accounts
            </button>

            <button data-module="assignStaff" class="dropdown-item adm-btn <?php echo $currentModule === 'assignStaff' ? 'active' : ''; ?>">
              <i class="fa-solid fa-user-tag"></i> Assign Staff
            </button>

            <button data-module="office" class="dropdown-item adm-btn <?php echo $currentModule === 'office' ? 'active' : ''; ?>">
              <i class="fa-solid fa-building"></i> Offices
            </button>

            <button data-module="services" class="dropdown-item adm-btn <?php echo $currentModule === 'services' ? 'active' : ''; ?>">
              <i class="fa-solid fa-briefcase"></i> Services
            </button>
          </div>
        </li>

        <li>
          <button data-module="queueDisplay" class="adm-btn <?php echo $currentModule === 'queueDisplay' ? 'active' : ''; ?>">
            <i class="fa-solid fa-tv"></i> Queue Display
          </button>
        </li>

        <li>
          <button data-module="officeQueue" class="adm-btn <?php echo $currentModule === 'officeQueue' ? 'active' : ''; ?>">
            <i class="fa-solid fa-list-check"></i> Live Office Queue
          </button>
        </li>
      </ul>
    </nav>


    <div class="user-box sidebar-footer">
      <div class="user-card">
        <div class="user-avatar">
          <?php echo strtoupper(substr($userName, 0, 3)); ?>
        </div>
        <div class="user-info">
          <div class="fw-semibold"><?php echo $userName; ?></div>
          <small>
              <?php echo $office_description ? htmlspecialchars($office_description . ' (' . $office_code . ')') : '—'; ?>
          </small>
        </div>
      </div>
      <button id="adminLogout" class="btn btn-light w-100 mt-2">Logout</button>
    </div>
  </aside>

  <!-- MAIN -->
  <main class="adm-main">
    <iframe id="moduleFrame" src="<?php echo htmlspecialchars($moduleSrc); ?>"></iframe>
  </main>

  <script>
    window.__ADMIN_SESSION = { user_name: <?php echo json_encode($userName); ?> };
  </script>
  <script src="../js/adminDashboard.js" defer></script>


</body>
</html>
