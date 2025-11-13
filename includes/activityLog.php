<?php
// session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>My Activity Log — LINYA</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Bootstrap + Font Awesome + DataTables -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">

  <style>
    body { background-color: #f8f9fa; }
    .fq-header { background-color: #1b5e20; color: #fff; padding: 1rem 1.5rem; border-radius: .5rem .5rem 0 0; }
    .fq-header h1 { margin: 0; font-weight: 700; font-size: 1.5rem; display: flex; align-items: center; gap: .5rem; }
    .controls label { font-weight: 600; margin-right: .5rem; }
    .controls select, .controls input { margin-right: .75rem; }
    .controls { display: flex; align-items: center; gap: .5rem; flex-wrap: wrap; margin-top: .75rem; }

    #activityTable thead th { background-color: #d8efe1 !important; color: #0b3b21; text-align: center; font-weight: 700; }
    #activityTable tbody td { text-align: center; vertical-align: middle; }
    .dataTables_wrapper .dataTables_filter input { width: 220px; }

    .card { border: none; border-radius: .5rem; overflow: hidden; }
    .card-header { background-color: #2e7d32; border-bottom: none; }
  </style>
</head>

<body>
<div class="container-fluid">
            <div class="activity-header mb-4 d-flex justify-content-between align-items-center flex-wrap">
            <h3 class=" fw-bold mb-0">
                <i class="fa-solid fa-clock-rotate-left me-2"></i> My Activity Log
            </h3>

            <div class="controls d-flex align-items-center gap-2 flex-wrap">
                <label for="officeFilter" class="mb-0">Office:</label>
                <select id="officeFilter" class="form-select form-select-sm w-auto">
                <option value="0">All</option>
                </select>

                <label for="filterType" class="mb-0">Date:</label>
                <select id="filterType" class="form-select form-select-sm w-auto">
                <option value="all">All</option>
                <option value="day">Day</option>
                <option value="month">Month</option>
                <option value="range">Range</option>
                </select>

                <input type="date" id="filterDate" class="form-control form-control-sm w-auto" style="display:none;">
                <input type="month" id="filterMonth" class="form-control form-control-sm w-auto" style="display:none;">
                <input type="date" id="startDate" class="form-control form-control-sm w-auto" style="display:none;">
                <input type="date" id="endDate" class="form-control form-control-sm w-auto" style="display:none;">
            </div>
            </div>
            <!-- Table Section -->
            <div class="card shadow-sm border-0">
                <div class="card-header text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fa-solid fa-list me-2"></i>Processed Queues</h5>
                </div>
                <div class="card-body">
                <div class="table-responsive">
                    <table id="activityTable" class="table table-hover table-bordered align-middle w-100">
                    <thead>
                        <tr>
                        <th>Office</th>
                        <th>Window</th>
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

<!-- JS Libraries -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="../js/activityLog.js"></script>

<script>
$(document).ready(function() {
  $('#activityTable').DataTable({
    responsive: true,
    ordering: false,
    pageLength: 10,
    lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
    language: { 
      lengthMenu: 'Show _MENU_ entries', 
      info: 'Showing _START_ to _END_ of _TOTAL_ logs' 
    }
  });
  $('.dataTables_filter input').attr('placeholder', 'Search logs...');
});
</script>
</body>
</html>
