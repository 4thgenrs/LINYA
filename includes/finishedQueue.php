<?php include '../linya_db/config.php'; ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Finished Queues — LINYA</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">

  <!-- BOOTSTRAP -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- FONT AWESOME -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- DATATABLES CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<!-- 
  <link rel="stylesheet" href="../css/finishedQueue.css"> -->
</head>

<body class="py-4">

  <main class="container-fluid">

    <div class="mb-2">
        <h1 class="mb-0"><i class="fa-solid fa-check-double me-2"></i>Finished Queues</h1>
      </div>

    <div class="d-flex align-items-center justify-content-end mb-2 mt-2">
      <label for="officeFilter" class="me-2 fw-semibold">Office:</label>
      <select id="officeFilter" class="form-select form-select-sm me-3" style="width:160px;">
        <option value="0">All</option>
      </select>

      <button id="refreshBtn" type="button" class="btn btn-sm btn-success">
        <i class="fa-solid fa-rotate"></i> Refresh
      </button>
    </div>

    <!-- TABLE CARD -->
    <div class="card shadow-sm border-0">
      <div class="card-header text-white" style="background-color:#1b5e20;">
        <h5 class="mb-0"><i class="fa-solid fa-list me-2"></i>Finished Queue List</h5>
      </div>

      <div class="card-body">
        <div class="table-responsive">
          <table id="finishedTable" class="table table-hover table-bordered align-middle nowrap w-100">
            <thead class="table-success">
              <tr>
                <th>Office</th>
                <th>Window</th>
                <th>Processed By</th>
                <th>Created At</th>
                <th>Finished At</th>
                <th>Services</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Email</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>

        <div class="text-end mt-3">
          <button id="exportCSVBtn" type="button" class="btn btn-success me-2">
            <i class="fa-solid fa-file-csv me-1"></i>Export to CSV
          </button>
          <button id="openReportBtn" type="button" class="btn btn-success">
            <i class="fa-solid fa-file-export me-1"></i>Generate Report
          </button>
        </div>
      </div>
    </div>

    <!-- REPORT MODAL -->
    <div id="reportModal" class="modal fade" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="reportModalLabel">Generate Report</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form id="reportForm" method="POST" target="_blank" action="../backend/generateFinishedReport.php">

              <div class="row g-2 mb-3">
                <div class="col-md-6">
                  <label for="r_office" class="form-label">Office:</label>
                  <select id="r_office" name="office_id" class="form-select form-select-sm">
                    <option value="0">All</option>
                  </select>
                </div>

                <div class="col-md-6">
                  <label for="r_staff" class="form-label">Staff:</label>
                  <select id="r_staff" name="processed_by" class="form-select form-select-sm">
                    <option value="">Any</option>
                  </select>
                </div>
              </div>

              <div class="row g-2 mb-3">
                <div class="col-md-6">
                  <label for="r_start" class="form-label">Start Date:</label>
                  <input id="r_start" name="start_date" type="date" class="form-control form-control-sm">
                </div>

                <div class="col-md-6">
                  <label for="r_end" class="form-label">End Date:</label>
                  <input id="r_end" name="end_date" type="date" class="form-control form-control-sm">
                </div>
              </div>

            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" form="reportForm" class="btn btn-success">
              <i class="fa-solid fa-file-export me-1"></i>Generate
            </button>
          </div>
        </div>
      </div>
    </div>

  </main>

  <!-- JS LIBRARIES -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <!-- YOUR EXISTING JS -->
  <script src="../js/finishedQueue.js"></script>
</body>
</html>
