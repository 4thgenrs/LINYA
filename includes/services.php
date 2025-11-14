<?php
include('../backend/servicesData.php');
include('../linya_db/config.php');




$services = getServiceData($conn);
$offices = getOfficeData($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Service Management</title>

  <!-- Bootstrap + Font Awesome + DataTables -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">

  <style>
    body { background-color: #f8f9fa; }
    .services-header { background-color: #1b5e20; color: #fff; }
    #servicesTable thead th { background-color: #d8efe1 !important; color: #0b3b21; font-weight:700; text-align:center; }
    #servicesTable tbody td { text-align:center; vertical-align: middle; }
    .dataTables_wrapper .dataTables_filter input { width: 220px; }
  </style>
</head>

<body>
<div class="container-fluid">
  <div class="row mb-3">
    <div class="col">
      <h3 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-concierge-bell me-2"></i>Service Management</h3>
    </div>
  </div>

  <!-- Add Service Form -->
  <form id="serviceForm" action="../backend/servicesData.php" method="POST" class="row g-3 align-items-end mb-4">
    <input type="hidden" name="update_id" id="update_id" value="">
    <div class="col-md-6">
      <label for="service_description" class="form-label fw-semibold">Service Description</label>
      <input type="text" name="service_description" id="service_description" class="form-control" placeholder="Enter service description" required>
    </div>

    <div class="col-md-4">
      <label for="office_id" class="form-label fw-semibold">Office</label>
      <select name="office_id" id="office_id" class="form-select" required>
        <option value="">Select Office</option>
        <?php foreach ($offices as $o) { ?>
          <option value="<?php echo $o['office_id']; ?>"><?php echo $o['office_description']; ?></option>
        <?php } ?>
      </select>
    </div>

    <div class="col-md-2 text-end">
      <button type="submit" id="submitBtn" class="btn btn-success w-100" style="background-color:#1b5e20;">
        <i class="fas fa-plus me-1"></i> Add Service
      </button>
    </div>
  </form>

  <!-- Services Table -->
  <div class="card shadow-sm border-0">
    <div class="card-header services-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0"><i class="fa-solid fa-list me-2"></i>Services</h5>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table id="servicesTable" class="table table-hover table-bordered w-100">
          <thead>
            <tr>
              <th>#</th>
              <th>Service Description</th>
              <th>Office</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
          <?php if (!empty($services)) {
              $counter = 1;
              foreach ($services as $row) { ?>
                <tr 
                  data-id="<?php echo $row['service_id']; ?>"
                  data-description="<?php echo htmlspecialchars($row['service_description']); ?>"
                  data-office="<?php echo $row['office_description']; ?>"
                >
                  <td><?php echo $counter++; ?></td>
                  <td><?php echo htmlspecialchars($row['service_description']); ?></td>
                  <td><?php echo htmlspecialchars($row['office_description']); ?></td>
                  <td>
                    <button type="button" class="btn btn-sm btn-warning editBtn me-2"><i class="fa-solid fa-pen-to-square"></i></button>
                    <form method="POST" action="../backend/servicesData.php" style="display:inline;">
                      <input type="hidden" name="delete_id" value="<?php echo $row['service_id']; ?>">
                     <button type="submit" class="btn btn-sm btn-danger delete-btn">
                        <i class="fa-solid fa-trash"></i>
                      </button>
                    </form>
                  </td>
                </tr>
          <?php } } else { ?>
              <tr><td colspan="4" class="text-center text-muted">No data found</td></tr>
          <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- External JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="../js/services.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function(){
  // Initialize DataTable
  $('#servicesTable').DataTable({
    responsive: true,
    ordering: false,
    pageLength: 10,
    lengthMenu: [[10,25,50,-1],[10,25,50,'All']],
    language: { lengthMenu: 'Show _MENU_ entries', info: 'Showing _START_ to _END_ of _TOTAL_ entries' }
  });
  $('.dataTables_filter input').attr('placeholder', 'Search services...');

// SweetAlert2 Delete Confirmation for delete buttons only
$(document).on('click', '.delete-btn', function(e) {
  e.preventDefault(); // stop the default form submission

  const form = $(this).closest('form'); // get the parent form

  Swal.fire({
    title: 'Are you sure?',
    text: "This service will be permanently deleted.",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Yes, delete it!',
    cancelButtonText: 'Cancel'
  }).then((result) => {
    if (result.isConfirmed) {
      form.submit(); // submit the form after confirmation
    }
  });
});
});

</script>


</body>
</html>
