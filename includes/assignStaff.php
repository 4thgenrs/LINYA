<?php include '../linya_db/config.php';



?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Assign Staff to Window</title>

  <!-- Bootstrap, FontAwesome & DataTables -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">

  <style>
    body {
      background-color: #f8f9fa;
    }
    #windowsTable thead th {
      background-color: #d8efe1 !important;
      color: #0b3b21;
      font-weight: 700;
      text-align: center;
    }
    #windowsTable tbody td {
      text-align: center;
      vertical-align: middle;
    }
    .dataTables_wrapper .dataTables_filter input {
      width: 250px;
    }
    .card-header {
      background-color: #1b5e20;
      color: #fff;
    }
    label small {
      font-weight: 400;
      display: block;
      color: #6c757d;
    }
  </style>
</head>

<body>
<div class="container-fluid py-4">

  <div class="row mb-3">
    <div class="col">
      <h3 class="fw-bold mb-0">
        <i class="fa-solid fa-people-arrows me-2"></i>Create Window & Assign Staff
      </h3>
    </div>
  </div>

  <!-- Form Section -->
  <form id="createForm" class="row g-3 align-items-end mb-4">
    <div class="col-md-4">
      <label for="officeSelect" class="form-label fw-semibold mb-0">Office</label>
      <select id="officeSelect" name="office_id" class="form-select" required>
        <option value="">Loading offices...</option>
      </select>
    </div>

    <div class="col-md-3">
      <label for="windowNo" class="form-label fw-semibold mb-0">
        Window No.
        <small>Leave empty to auto-assign</small>
      </label>
      <input id="windowNo" name="window_no" type="number" min="1" class="form-control">
    </div>

    <div class="col-md-4">
      <label for="staffSelect" class="form-label fw-semibold mb-0">Assign Staff</label>
      <select id="staffSelect" name="staff_id" class="form-select" required disabled>
        <option value="">Select office first</option>
      </select>
    </div>

    <div class="col-md-1 d-flex gap-1">
      <button id="createBtn" type="submit" class="btn btn-success w-100" style="background-color:#1b5e20;">
        <i class="fa-solid fa-plus me-1"></i> Create
      </button>
    </div>
    <div class="col-md-1 d-flex gap-1">
      <button id="refreshBtn" type="button" class="btn btn-secondary w-100">
        <i class="fa-solid fa-rotate me-1"></i> Refresh
      </button>
    </div>
  </form>

  <!-- Table Section -->
  <!-- Existing Windows Table -->
<div class="card shadow-sm border-0">
  <div class="card-header bg-success text-white d-flex align-items-center">
    <h5 class="mb-0"><i class="fa-solid fa-list me-2"></i>Existing Windows</h5>
  </div>

  <div class="card-body">
    <div class="table-responsive">
      <table id="windowsTable" class="table table-hover table-bordered align-middle nowrap w-100">
        <thead class="table-success text-center">
          <tr>
            <th>Office</th>
            <th>Window No.</th>
            <th>Assigned Staff</th>
            <th>Enabled</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody class="text-center">
          <tr><td colspan="5" class="text-center text-muted">Loading...</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
</div>

<!-- JS Libraries -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../js/assignStaff.js"></script>

<script>
$(document).ready(function() {
  $('#windowsTable').DataTable({
    responsive: true,
    ordering: false,
    pageLength: 10,
    language: {
      lengthMenu: 'Show _MENU_ entries',
      info: 'Showing _START_ to _END_ of _TOTAL_ entries'
    }
  });

  $('.dataTables_filter input').attr('placeholder', 'Search windows...');
});
</script>
</body>
</html>
