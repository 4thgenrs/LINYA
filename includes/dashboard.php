  <?php
  include('../linya_db/config.php');
  
  // Count active queues (status = 'pending' or 'ongoing')
  $activeQueueCount = 0;
  $result = $conn->query("SELECT COUNT(*) as count FROM queues WHERE status IN ('pending', 'ongoing')");
  if ($result) {
    $row = $result->fetch_assoc();
    $activeQueueCount = intval($row['count'] ?? 0);
  }
  ?>
  <title>Dashboard</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    .card-stat {
      border: 1px solid #e5e5e5;
      border-radius: 10px;
      text-align: center;
      background: #fff;
      box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .card-stat i {
      font-size: 1.8rem;
      color: #198754;
      margin-bottom: 8px;
    }

    .stat-number {
      font-size: 1.4rem;
      font-weight: 600;
    }

    .quick-card {
      border-radius: 10px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }

    .btn-success {
      border-radius: 8px;
    }

    h4, h5 {
      font-weight: 600;
    }
  </style>
</head>

<body>

  <div class="container-fluid">
    <h4 class="fw-bold mb-3">Dashboard</h4>

    <!-- Stats Row -->
    <div class="row g-3 mb-4">
      <div class="col-md-3 col-sm-6">
        <div class="card-stat p-3">
          <i class="fa-solid fa-user-clock"></i>
          <div class="stat-number"><?php echo $activeQueueCount; ?></div>
          <div>Active in Queue</div>
        </div>
      </div>
      <div class="col-md-3 col-sm-6">
        <div class="card-stat p-3">
          <i class="fa-solid fa-check-circle"></i>
          <div class="stat-number">43</div>
          <div>Completed Today</div>
        </div>
      </div>
      <div class="col-md-3 col-sm-6">
        <div class="card-stat p-3">
          <i class="fa-solid fa-clock"></i>
          <div class="stat-number">12 min</div>
          <div>Avg Wait Time</div>
        </div>
      </div>
      <div class="col-md-3 col-sm-6">
        <div class="card-stat p-3">
          <i class="fa-solid fa-briefcase"></i>
          <div class="stat-number">47</div>
          <div>Total Services</div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <h5 class="fw-bold mb-3">Quick Actions</h5>
    <div class="row g-3">
      <div class="col-md-4">
        <div class="card quick-card p-3">
          <h6 class="fw-semibold">View Live Queue</h6>
          <p class="text-muted mb-3">Manage current queue for Registrar services.</p>
          <button class="btn btn-success btn-sm">View Queue</button>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card quick-card p-3">
          <h6 class="fw-semibold">Reports & Analytics</h6>
          <p class="text-muted mb-3">View detailed reports and analytics for Registrar services.</p>
          <button class="btn btn-success btn-sm">View Reports</button>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card quick-card p-3">
          <h6 class="fw-semibold">Settings</h6>
          <p class="text-muted mb-3">Configure Registrar services and courses.</p>
          <button class="btn btn-success btn-sm">View Settings</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
