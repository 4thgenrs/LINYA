<?php
include('../backend/officeData.php');
include('../linya_db/config.php');

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_office'])) {
        $code = trim($_POST['office_code']);
        $desc = trim($_POST['office_description']);
        if ($code !== '' && $desc !== '') {
            addOffice($conn, $code, $desc);
        }
    }

    if (isset($_POST['update_office'])) {
        $id = intval($_POST['office_id']);
        $code = trim($_POST['office_code']);
        $desc = trim($_POST['office_description']);
        if ($id > 0 && $code !== '' && $desc !== '') {
            updateOffice($conn, $id, $code, $desc);
        }
    }

    if (isset($_POST['delete_office'])) {
        $id = intval($_POST['office_id']);
        if ($id > 0) {
            deleteOffice($conn, $id);
        }
    }
}

// Fetch data
$offices = getOfficeData($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Office Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        /* .table thead {
            background-color: #1b5e20;
            color: #fff;
        } */
        /* .table tbody tr:hover {
            background-color: #f2f2f2;
        } */
        .form-control {
            border-radius: 8px;
        }
        .page-title {
            color: #1b5e20;
            font-weight: 700;
            margin-bottom: 25px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">

        <h2 class="page-title"><i class="fa-solid fa-building me-2"></i>Office Management</h2>

        <!-- Add / Update Form -->
        <div class="card mb-4">
            <div class="card-body">
                <form id="officeForm" method="POST" class="row g-3 align-items-center">
                    <input type="hidden" name="office_id" id="office_id">
                    <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="office_code" class="form-label fw-semibold">Office Code</label>
                        <input type="text" name="office_code" id="office_code" class="form-control" placeholder="Enter office code" required>
                    </div>

                    <div class="col-md-6">
                        <label for="office_description" class="form-label fw-semibold">Office Description</label>
                        <input type="text" name="office_description" id="office_description" class="form-control" placeholder="Enter office description" required>
                    </div>

                    <div class="col-md-2 d-flex">
                        <input type="submit" name="add_office" id="submitBtn" value="Add Office" class="btn btn-success w-100 align-self-end">
                    </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Offices Table -->
<div class="card shadow-sm border-0">
  <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
    <h5 class="mb-0"><i class="fa-solid fa-list me-2"></i>Office List</h5>
  </div>

  <div class="card-body">
    <div class="table-responsive">
      <table id="officesTable" class="table table-hover table-bordered w-100">
        <thead>
          <tr class="text-center align-middle" style="background-color: #d8efe1; color: #0b3b21; font-weight:700;">
            <th>#</th>
            <th>Office Code</th>
            <th>Office Description</th>
            <th>Action</th>
          </tr>
        </thead>

        <tbody class="text-center align-middle">
          <?php if (!empty($offices)) : ?>
            <?php $counter = 1; ?>
            <?php foreach ($offices as $office) : ?>
              <tr 
                data-id="<?php echo $office['office_id']; ?>" 
                data-code="<?php echo htmlspecialchars($office['office_code']); ?>"
                data-description="<?php echo htmlspecialchars($office['office_description']); ?>"
              >
                <td><?php echo $counter++; ?></td>
                <td><?php echo htmlspecialchars($office['office_code']); ?></td>
                <td><?php echo htmlspecialchars($office['office_description']); ?></td>
                <td>
                  <button type="button" class="btn btn-sm btn-warning editBtn me-2">
                    <i class="fa-solid fa-pen-to-square"></i>
                  </button>
                  <form method="POST" class="d-inline">
                    <input type="hidden" name="office_id" value="<?php echo $office['office_id']; ?>">
                    <button type="submit" name="delete_office" class="btn btn-sm btn-danger">
                      <i class="fa-solid fa-trash"></i>
                    </button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else : ?>
            <tr>
              <td colspan="4" class="text-center text-muted">No data found</td>
            </tr>
          <?php endif; ?>
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

<script>
    // Edit button functionality
    document.querySelectorAll('.editBtn').forEach(button => {
        button.addEventListener('click', function() {
            const row = this.closest('tr');
            document.getElementById('office_id').value = row.dataset.id;
            document.getElementById('office_code').value = row.dataset.code;
            document.getElementById('office_description').value = row.dataset.description;

            const submitBtn = document.getElementById('submitBtn');
            submitBtn.value = 'Update Office';
            submitBtn.name = 'update_office';
            submitBtn.classList.remove('btn-success');
            submitBtn.classList.add('btn-warning');
        });
    });
    </script>
</body>
</html>
