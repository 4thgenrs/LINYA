<?php include '../linya_db/config.php'; ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Finished Queues — LINYA</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">

  <!-- Bootstrap, FontAwesome, DataTables -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">

  <style>
    body {
      background-color: #f8f9fa;
    }

    .card-header {
      background-color: #1b5e20;
      color: #fff;
      border-bottom: none;
    }

    .dataTables_wrapper .dataTables_filter input {
      border-radius: 20px;
      padding: 4px 10px;
    }

    .dataTables_length select {
      border-radius: 10px;
    }

    .controls label {
      white-space: nowrap;
    }
  </style>
</head>

<body>
<div class="container-fluid py-3">
  <!-- Header and Controls -->
  <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
    <h1 class="fw-bold mb-0">Finished Queues</h1>
    <div class="controls d-flex align-items-center gap-2">
      <label for="officeFilter" class="fw-semibold mb-0">Office:</label>
      <select id="officeFilter" class="form-select form-select-sm" style="width: 180px;">
        <option value="0">All</option>
      </select>
      <button id="refreshBtn" type="button" class="btn btn-success btn-sm">
        <i class="fa-solid fa-rotate-right me-1"></i> Refresh
      </button>
    </div>
  </div>

  <!-- Finished Queues Table (Styled like Services Table) -->
  <div class="card shadow-sm border-0">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0"><i class="fa-solid fa-list me-2"></i>Finished Queues</h5>
    </div>

    <div class="card-body">
      <div class="table-responsive">
        <table id="finishedTable" class="table table-hover table-bordered align-middle nowrap w-100">
          <thead class="table-light">
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
    </div>
  </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="../js/finishedQueue.js"></script>

<script>
$(document).ready(function() {
  $('#finishedTable').DataTable({
    responsive: true,
    ordering: false,
    pageLength: 10,
    lengthMenu: [[10,25,50,-1],[10,25,50,'All']],
    columnDefs: [
      { orderable: false, targets: '_all' }
    ],
    language: {
      lengthMenu: 'Show _MENU_ entries',
      info: 'Showing _START_ to _END_ of _TOTAL_ entries'
    }
  });
  $('.dataTables_filter input').attr('placeholder', 'Search finished queues...');
});
</script>

</body>
</html>
