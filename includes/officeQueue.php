<?php
$office = isset($_GET['office']) ? intval($_GET['office']) : 1;
$currentDate = date('l, F j, Y');
$currentTime = date('h:i:s A');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Queue</title>
  <link rel="stylesheet" href="/123/css/officeQueue.css?v=11" />
</head>
<body>
  <!-- University Header -->
  <nav class="navbar-header">
    <img src="/123/resources/ucc.png" class="ucc-logo" alt="UCC Logo">
    <div class="university-info">
      <div class="university-name">UNIVERSITY OF CALOOCAN CITY</div>
      <div id="office-name" class="office-name">
        <?php 
          // Default office names based on office ID
          $officeNames = [
            1 => 'OFFICE OF THE UNIVERSITY REGISTRAR',
            2 => 'OFFICE OF THE UNIVERSITY ACCOUNTING',
            8 => 'MANAGEMENT INFORMATION SYSTEMS',
            4 => 'OFFICE 4'
          ];
          echo isset($officeNames[$office]) ? $officeNames[$office] : 'OFFICE OF THE UNIVERSITY REGISTRAR';
        ?>
      </div>
    </div>
    <div class="datetime-info">
      <div id="current-date" class="current-date"><?php echo $currentDate; ?></div>
      <div id="current-time" class="current-time"><?php echo $currentTime; ?></div>
    </div>
  </nav>
  
  <div id="root" data-office="<?php echo $office; ?>">
    <header class="oq-header">
      <div class="office-selector-container">
        <label for="officeSelect" class="oq-select-label">Select Office:</label>
        <select id="officeSelect" class="oq-select">
          <option value="1" <?php echo $office == 1 ? 'selected' : ''; ?>>OFFICE OF THE UNIVERSITY REGISTRAR</option>
          <option value="2" <?php echo $office == 2 ? 'selected' : ''; ?>>OFFICE OF THE UNIVERSITY ACCOUNTING</option>
          <option value="3" <?php echo $office == 3 ? 'selected' : ''; ?>>MANAGEMENT INFORMATION SYSTEMS</option>
          <option value="4" <?php echo $office == 4 ? 'selected' : ''; ?>>OFFICE 4</option>
        </select>
      </div>
    </header>
    
    <main class="oq-main">
      <section class="oq-windows" id="windowsContainer" aria-label="Windows"></section>
      <aside class="oq-pending">
        <h3>Pending Queues</h3>
        <div id="pendingList" class="pending-list">
          <div class="pending-empty">No pending queues</div>
        </div>
      </aside>
    </main>
  </div>
  
  <!-- Footer -->
  <footer class="oq-footer">
    <div class="footer-left">
      <div>LINYA - Queue Management System</div>
      <div class="footer-sub">Auto-updates every 3s</div>
    </div>
    <div class="footer-right">© 2025 All Rights Reserved</div>
  </footer>
  
  <script src="../js/officeQueue.js?v=3"></script>
</body>
</html>