<?php 
session_start();
include('../linya_db/config.php'); // make sure this path is correct

// SECURITY CHECK: user must be logged in
if (!isset($_SESSION['user_id'])) {
  header('Location: ../index.php');
  exit;
}

// Fetch user info from DB to ensure data is current
$user_id = $_SESSION['user_id'];
$user_query = $conn->prepare("SELECT name, user_type FROM accounts WHERE user_id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_result = $user_query->get_result();
$user = $user_result->fetch_assoc();

if (!$user) {
  // Invalid user session
  session_destroy();
  header('Location: ../index.php');
  exit;
}

$name = $user['name'];
$user_type = $user['user_type']; // 1 = Admin, 2 = Staff

$allowed_pages = [
  'assignStaff', 'accounts', 'activitylog', 'dashboard', 
  'finishedQueue', 'office', 'officeQueue', 'queueDisplay', 
  'queueSelect', 'services'
];

$page = 'dashboard'; // default
if (isset($_GET['page']) && in_array($_GET['page'], $allowed_pages, true)) {
  $page = $_GET['page'];
}

$includes_dir = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes');
$expected = $includes_dir ? $includes_dir . DIRECTORY_SEPARATOR . $page . '.php' : '';
$content_file = $expected && file_exists($expected) ? realpath($expected) : false;

function _norm_path($p) {
  return $p === false ? '' : strtolower(str_replace('\\', '/', $p));
}

$norm_includes = _norm_path($includes_dir);
$norm_content = _norm_path($content_file);

if (empty($norm_includes) || empty($norm_content) || strpos($norm_content, $norm_includes) !== 0) {
  $content_file = '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>LINYA - Admin</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    body { background-color: #f8f9fa; margin: 0; }
    html, body { overflow-x: hidden; }

    #sidebar {
      position: fixed;
      top: 0;
      left: 0;
      height: 100vh;
      width: 280px;
      background-color: #1b5e20;
      color: #fff;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      z-index: 1040;
      overflow-y: auto;
      overflow-x: hidden;
      transition: transform 0.3s ease;
      box-shadow: 4px 0 12px rgba(0, 0, 0, 0.15);
    }
    #sidebar.mobile-hidden { transform: translateX(-100%); }

    .navbar-brand {
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
      color: #fff;
      text-decoration: none;
      padding: 20px 0;
    }
    .navbar-brand .brand-logo { width: 90px; height: 90px; object-fit: contain; margin-bottom: 8px; }
    .navbar-brand .brand-title { font-size: 14px; line-height: 1.3; }

    #sidebar .nav-link {
      display: flex; align-items: center; gap: 10px;
      color: #fff; padding: 10px 20px; text-decoration: none;
      border-radius: 6px; transition: background-color 0.2s;
      font-size: 0.95rem; margin-bottom: 4px;
    }
    #sidebar .nav-link:hover, #sidebar .nav-link.active {
      background-color: #2e7d32; font-weight: 600;
    }

    .sidebar-bottom {
      background-color: #164d19; padding: 15px; margin-top: auto;
      color: #fff; border-top: 1px solid rgba(255,255,255,0.1);
    }
    .user-card { display: flex; align-items: center; gap: 10px; }
    .user-avatar {
      width: 45px; height: 45px; background-color: #2e7d32;
      border-radius: 50%; display: flex; align-items: center;
      justify-content: center; font-weight: bold; font-size: 14px;
      color: #fff;
    }

    .main-content { margin-left: 280px; flex-grow: 1; padding: 20px; overflow-y: auto; height: 100vh; }

    #sidebarToggle { display: none; }

    @media (max-width: 991.98px) {
      #sidebarToggle {
        display: block; position: fixed; top: 1rem; left: 1rem;
        z-index: 2100; background: #fff; border: none; border-radius: 50%;
        width: 44px; height: 44px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        color: #2C3E50; font-size: 1.3rem;
      }
      #sidebar { transform: translateX(-100%); }
      #sidebar.show { transform: translateX(0); }
      .main-content { margin-left: 0; padding: 1rem; }
    }
  </style>
</head>
<body>

  <button id="sidebarToggle" class="btn">
    <i class="bi bi-list"></i>
  </button>

  <div class="layout">
    <aside id="sidebar">
      <div>
        <div class="text-center">
          <img src="../UCC.png" alt="logo" class="img-fluid mb-2" style="max-width: 80px;">
          <div class="brand-title fw-bold text-white">
            Live Interactive <br> Navigation of Your Appointment
          </div>
        </div>

        <nav class="sidebar-nav mt-4">
          <ul class="list-unstyled">
            <li>
              <a href="?page=dashboard" class="nav-link <?php echo $page === 'dashboard' ? 'active' : ''; ?>">
                <i class="fa-solid fa-chart-simple"></i> Dashboard
              </a>
            </li>

            <?php if ($user_type == 1): // ADMIN ?>
              <li><a href="?page=accounts" class="nav-link <?php echo $page === 'accounts' ? 'active' : ''; ?>"><i class="fa-solid fa-user-gear me-2"></i> Accounts</a></li>
              <li><a href="?page=assignStaff" class="nav-link <?php echo $page === 'assignStaff' ? 'active' : ''; ?>"><i class="fa-solid fa-user-tag me-2"></i> Assign Staff</a></li>
              <li><a href="?page=finishedQueue" class="nav-link <?php echo $page === 'finishedQueue' ? 'active' : ''; ?>"><i class="fa-solid fa-clipboard-check me-2"></i> Finished Queue</a></li>
              <li><a href="?page=office" class="nav-link <?php echo $page === 'office' ? 'active' : ''; ?>"><i class="fa-solid fa-building me-2"></i> Offices</a></li>
              <li><a href="?page=services" class="nav-link <?php echo $page === 'services' ? 'active' : ''; ?>"><i class="fa-solid fa-briefcase me-2"></i> Services</a></li>
              <li><a href="?page=queueDisplay" class="nav-link <?php echo $page === 'queueDisplay' ? 'active' : ''; ?>"><i class="fa-solid fa-tv me-2"></i> Queue Display</a></li>

            <?php elseif ($user_type == 2): // STAFF ?>
              <li><a href="?page=queueSelect" class="nav-link <?php echo $page === 'queueSelect' ? 'active' : ''; ?>"><i class="fa-solid fa-list-check me-2"></i> Queue Select</a></li>
              <li><a href="?page=activitylog" class="nav-link <?php echo $page === 'activitylog' ? 'active' : ''; ?>"><i class="fa-solid fa-list"></i> Activity Log</a></li>
              <li><a href="?page=finishedQueue" class="nav-link <?php echo $page === 'finishedQueue' ? 'active' : ''; ?>"><i class="fa-solid fa-clipboard-check me-2"></i> Finished Queue</a></li>
            <?php endif; ?>

            <li>
              <a class="nav-link text-secondary d-flex align-items-center" id="logoutBtn">
                <i class="fa-solid fa-right-from-bracket me-2"></i> Logout
              </a>
            </li>
          </ul>
        </nav>
      </div>

      <div class="sidebar-bottom">
        <div class="user-card">
          <div class="user-avatar">
            <?php echo strtoupper(substr($name, 0, 3)); ?>
          </div>
          <div class="user-info">
            <div class="fw-semibold"><?php echo htmlspecialchars($name); ?></div>
            <small class="text-light text-capitalize">
              <?php echo ($user_type == 1) ? 'Admin' : 'Staff'; ?>
            </small>
          </div>
        </div>
      </div>
    </aside>

    <main class="main-content">
      <?php
      if (file_exists($content_file)) {
        include $content_file;
      } else {
        echo '<h2>Page not found</h2>';
      }
      ?>
    </main>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.js"></script>
  <script src="../js/logout.js"></script>
  <script>
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('sidebarToggle');
    toggleBtn.addEventListener('click', () => sidebar.classList.toggle('show'));
  </script>
</body>
</html>
