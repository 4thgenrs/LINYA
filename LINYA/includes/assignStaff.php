<?php include '../linya_db/config.php'; ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Assign Staff to Window</title>

  <!-- BOOTSTRAP & FONTAWESOME -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

  <!-- DATATABLES CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="../css/assignStaff.css">
</head>
<body class="p-4">

<div class="container-fluid">

  <h2 class="mb-4 fw-bold"><i class="fa-solid fa-person-chalkboard me-2"></i>Create Window & Assign Staff</h2>

  <!-- ---------------- FORM CARD ---------------- -->
  <div class="mb-4">
    <form id="createForm" class="row g-2 align-items-end">
      
      <div class="col-md-3">
        <label for="officeSelect" class="form-label">Office</label>
        <select id="officeSelect" name="office_id" class="form-select" required>
          <option value="">Loading offices...</option>
        </select>
      </div>

      <div class="col-md-3">
        <label for="windowNo" class="form-label">Window No. 
          <!-- <small class="text-muted d-block">Leave empty to auto-assign</small> -->
        </label>
        <input id="windowNo" name="window_no" type="number" min="1" class="form-control" placeholder="Leave empty to auto-assign" />
      </div>

      <div class="col-md-3">
        <label for="staffSelect" class="form-label">Assign Staff</label>
        <select id="staffSelect" name="staff_id" class="form-select" required disabled>
          <option value="">Select office first</option>
        </select>
      </div>

      <div class="col-md-3 d-flex gap-2">
        <button id="createBtn" type="submit" class="btn btn-success fw-semibold">Create Window</button>
        <button id="refreshBtn" type="button" class="btn btn-secondary fw-semibold">Refresh List</button>
      </div>

    </form>
  </div>

  <!-- ---------------- TABLE CARD ---------------- -->
  <div class="card shadow-sm">
    <div class="card-header bg-success text-white fw-semibold">
      Existing Windows
    </div>
    <div class="card-body p-3">
      <div class="table-responsive">
        <table id="windowsTable" class="table table-bordered table-hover align-middle">
          <thead class="table-success">
            <tr>
              <th>Office</th>
              <th>Window No.</th>
              <th>Assigned Staff</th>
              <th>Enabled</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr><td colspan="5" class="text-center py-3">Loading...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>

<!-- JS LIBRARIES -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="../js/assignStaff.js"></script>
</body>
</html>
