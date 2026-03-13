<?php include '../linya_db/config.php'; ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Voided Queues — LINYA</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">

  <!-- BOOTSTRAP -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- FONT AWESOME -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- DATATABLES CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="../css/voidQueue.css">
  <link rel="stylesheet" href="../css/table-unified.css">
</head>

<body class="py-4">

  <main class="container-fluid">

    <div class="card shadow-sm border-0 void-table-card">
      <section class="void-hero void-topbar">
        <div class="void-hero-copy void-topbar-copy">
          <div class="void-hero-badge">
            <i class="fa-solid fa-ban"></i>
            Exception Archive
          </div>
          <h1 class="mb-0"><i class="fa-solid fa-ban me-2"></i>Voided Queues</h1>
          <p class="void-hero-subtitle mb-0">Monitor cancelled transactions, review the affected customer record, and inspect processing history from a cleaner archive view.</p>
          <div class="void-topbar-tablecopy">
            <span class="void-panel-eyebrow">Archive Table</span>
            <h5 class="mb-0"><i class="fa-solid fa-list me-2"></i>Voided Queue List</h5>
          </div>
        </div>

        <div class="void-hero-side void-topbar-side">
          <div>
            <div class="void-status-pill">
              <span class="void-status-dot"></span>
              Voided records view
            </div>
            <span class="void-status-label">Use the office filter and DataTable search to narrow down archived queue records.</span>
          </div>

          <div class="void-toolbar-controls">
            <div class="void-filter-group">
              <label for="officeFilter" class="fw-semibold mb-0">Office</label>
              <select id="officeFilter" class="form-select form-select-sm">
                <option value="0">All</option>
              </select>
            </div>

            <div class="void-action-group">
              <button id="refreshBtn" type="button" class="btn btn-sm btn-success void-refresh-btn">
                <i class="fa-solid fa-rotate"></i>
                <span>Refresh</span>
              </button>
            </div>
          </div>
        </div>
      </section>

      <div class="card-body void-table-body">
        <div class="table-responsive">
          <table id="voidTable" class="table table-hover table-bordered align-middle nowrap w-100">
            <thead class="table-success">
              <tr>
                <th>Original Queue ID</th>
                <th>Office</th>
                <th>Name</th>
                <th>Student #</th>
                <th>Course</th>
                <th>Processed By</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>

      </div>
    </div>

  </main>

  <div class="modal fade" id="voidDetailsModal" tabindex="-1" aria-labelledby="voidDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header void-modal-header text-white">
          <h5 class="modal-title" id="voidDetailsModalLabel">
            <i class="fa-solid fa-circle-info me-2"></i>Voided Queue Details
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="voidDetailsContent"></div>
      </div>
    </div>
  </div>

  <!-- JS LIBRARIES -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
  
  <script src="../js/voidQueue.js"></script>
</body>
</html>