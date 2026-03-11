<?php include '../linya_db/config.php'; ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Assign Staff to Window</title>

  <!-- BOOTSTRAP & FONTAWESOME -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

  <!-- DATATABLES CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="../css/assignStaff.css">
  <link rel="stylesheet" href="../css/table-unified.css">
</head>
<body class="p-4">

<div class="container-fluid">

  <section class="assign-hero">
    <div class="assign-hero-copy">
      <span class="assign-hero-badge">
        <i class="fa-solid fa-person-chalkboard"></i>
        Window Assignment
      </span>
      <h1><i class="fa-solid fa-person-chalkboard me-2"></i>Create Window & Assign Staff</h1>
      <p class="assign-hero-subtitle">Create service windows, assign staff to an office, and manage active window availability from one streamlined workspace.</p>
    </div>
    <div class="assign-hero-side">
      <div class="assign-status-pill">
        <span class="assign-status-dot"></span>
        Live window registry
      </div>
      <span class="assign-status-copy">Assign staff by office, update window numbers, and manage enable or delete actions directly from the table below.</span>
    </div>
  </section>

  <!-- ---------------- FORM CARD ---------------- -->
  <section class="assign-form-card card shadow-sm border-0 mb-4">
    <div class="card-body">
      <div class="assign-section-heading">
        <div>
          <span class="assign-section-eyebrow">Window Form</span>
          <h2>Create or Assign Window</h2>
          <p>Select an office, set or auto-fill the window number, and assign an available staff member to the new service window.</p>
        </div>
        <div class="assign-section-icon">
          <i class="fa-solid fa-window-maximize"></i>
        </div>
      </div>
    <form id="createForm" class="row g-3 align-items-end assign-form-grid">
      
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
  </section>

  <!-- ---------------- TABLE CARD ---------------- -->
  <div class="card shadow-sm border-0 assign-table-card">
    <div class="card-header text-white assign-table-header">
      <div>
        <span class="assign-section-eyebrow is-light">Window Directory</span>
        <h5 class="mb-0">Existing Windows</h5>
      </div>
      <div class="assign-table-icon">
        <i class="fa-solid fa-table-list"></i>
      </div>
    </div>
    <div class="card-body">
      <div class="table-responsive d-none d-md-block">
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
      <div class="d-block d-md-none mt-2">
        <div class="mobile-table-search">
          <input type="text" class="form-control" id="windowsMobileSearch" placeholder="Search windows...">
        </div>
        <div id="windowsMobileEmpty" class="mobile-table-empty" style="display:none;">No windows found.</div>
        <div id="windowsMobileList" class="mobile-table-list"></div>
      </div>
    </div>
  </div>

</div>

<!-- JS LIBRARIES -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="../js/tableMobileCards.js"></script>
<script src="../js/assignStaff.js"></script>
</body>
</html>
