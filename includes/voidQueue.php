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
</head>

<body class="py-4">

  <main class="container-fluid">

    <div class="mb-2">
        <h1 class="mb-0"><i class="fa-solid fa-ban me-2"></i>Voided Queues</h1>
      </div>

    <div class="d-flex align-items-center justify-content-end mb-2 mt-2">
      <label for="officeFilter" class="me-2 fw-semibold">Office:</label>
      <select id="officeFilter" class="form-select form-select-sm me-3" style="width:160px;">
        <option value="0">All</option>
      </select>

      <button id="refreshBtn" type="button" class="btn btn-sm btn-danger">
        <i class="fa-solid fa-rotate"></i> Refresh
      </button>
    </div>

    <!-- TABLE CARD -->
    <div class="card shadow-sm border-0">
      <div class="card-header text-white" style="background-color:#b71c1c;">
        <h5 class="mb-0"><i class="fa-solid fa-list me-2"></i>Voided Queue List</h5>
      </div>

      <div class="card-body">
        <div class="table-responsive">
          <table id="voidTable" class="table table-hover table-bordered align-middle nowrap w-100">
            <thead class="table-danger">
              <tr>
                <th>Office</th>
                <th>Window</th>
                <th>Processed By</th>
                <th>Created At</th>
                <th>Voided At</th>
                <th>Process Start</th>
                <th>Process Finished</th>
                <th>Processing Duration</th>
                <th>Remarks</th>
                <th>Services</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Student #</th>
                <th>Course</th>
                <th>Original Queue ID</th>
                <th>Record ID</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>

      </div>
    </div>

  </main>

  <!-- JS LIBRARIES -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
  
  <script src="../js/voidQueue.js"></script>
</body>
</html>
