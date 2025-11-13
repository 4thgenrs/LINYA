<?php
include('../backend/accountsData.php');
include('../backend/servicesData.php');
include('../linya_db/config.php');

$accounts = getAccountsData($conn);
$offices = getOfficeData($conn);
$roles = getRolesData($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Accounts Management</title>

    <!-- Bootstrap, FontAwesome & DataTables -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <style>
        /* Match example UI theme */
        body {
            background-color: #f8f9fa;
        }
        #accountsTable thead th {
            background-color: #d8efe1 !important;
            color: #0b3b21;
            font-weight: 700;
            text-align: center;
        }
        #accountsTable tbody td {
            text-align: center;
            vertical-align: middle;
        }
        .dataTables_wrapper .dataTables_filter input {
            width: 250px;
        }
    </style>
</head>

<body>
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <h3 class="fw-bold mb-0"><i class="fa-solid fa-users me-2"></i>User Management</h3>
        </div>
    </div>

    <!-- Form Section -->
    <form id="accountsForm" action="../backend/accountsData.php" method="POST" class="row g-2 align-items-end mb-4">
        <input type="hidden" name="user_id" id="user_id" value="">

        <div class="col-md-2">
            <label for="account_username" class="form-label mb-0 fw-semibold">Username</label>
            <input type="text" name="account_username" id="account_username" class="form-control" placeholder="Enter username" required>
        </div>

        <div class="col-md-2">
            <label for="account_password" class="form-label mb-0 fw-semibold">Password</label>
            <input type="text" name="account_password" id="account_password" class="form-control" placeholder="Enter password" required>
        </div>

        <div class="col-md-2">
            <label for="account_name" class="form-label mb-0 fw-semibold">Name</label>
            <input type="text" name="account_name" id="account_name" class="form-control" placeholder="Enter name" required>
        </div>

        <div class="col-md-2">
            <label for="role_id" class="form-label mb-0 fw-semibold">User Type</label>
            <select name="role_id" id="role_id" class="form-select" required>
                <option value="">Select User Type</option>
                <?php foreach ($roles as $r) { ?>
                    <option value="<?php echo $r['role_id']; ?>"><?php echo $r['role_name']; ?></option>
                <?php } ?>
            </select>
        </div>

        <div class="col-md-2">
            <label for="office_id" class="form-label mb-0 fw-semibold">Office</label>
            <select name="office_id" id="office_id" class="form-select" required>
                <option value="">Select Office</option>
                <?php foreach ($offices as $o) { ?>
                    <option value="<?php echo $o['office_id']; ?>"><?php echo $o['office_description']; ?></option>
                <?php } ?>
            </select>
        </div>

        <div class="col-md-1 text-end">
            <button type="submit" name="add_office" id="submitBtn" class="btn btn-success w-100" style="background-color: #1b5e20;">
                <i class="fas fa-plus me-1"></i> Add
            </button>
        </div>
    </form>

    <!-- Table Section -->
    <div class="card shadow-sm border-0">
        <div class="card-header text-white" style="background-color: #1b5e20;">
            <h5 class="mb-0"><i class="fa-solid fa-list me-2"></i>Accounts List</h5>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table id="accountsTable" class="table table-hover table-bordered align-middle nowrap w-100">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Username</th>
                            <th>Password</th>
                            <th>Name</th>
                            <th>User Type</th>
                            <th>Office</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($accounts)) : $counter = 1; ?>
                            <?php foreach ($accounts as $acc) : ?>
                                <tr
                                    data-id="<?php echo htmlspecialchars($acc['user_id']); ?>"
                                    data-username="<?php echo htmlspecialchars($acc['user_name']); ?>"
                                    data-password="<?php echo htmlspecialchars($acc['password']); ?>"
                                    data-name="<?php echo htmlspecialchars($acc['name']); ?>"
                                    data-role="<?php echo htmlspecialchars($acc['user_type']); ?>"
                                    data-office="<?php echo htmlspecialchars($acc['office']); ?>"
                                >
                                    <td><?php echo $counter++; ?></td>
                                    <td><?php echo htmlspecialchars($acc['user_name']); ?></td>
                                    <td><?php echo htmlspecialchars($acc['password']); ?></td>
                                    <td><?php echo htmlspecialchars($acc['name']); ?></td>
                                    <td><?php echo htmlspecialchars($acc['role_name']); ?></td>
                                    <td><?php echo htmlspecialchars($acc['office_description']); ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-warning editBtn me-1">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <form method="POST" action="../backend/accountsData.php" style="display:inline;">
                                            <input type="hidden" name="delete_id" value="<?php echo $acc['user_id']; ?>">
                                            <button type="submit" name="delete_account" class="btn btn-sm btn-danger delete-btn">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">No data found</td>
                            </tr>
                        <?php endif; ?>
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
<script src="../js/accounts.js"></script>

<script>
$(document).ready(function() {
    $('#accountsTable').DataTable({
        responsive: true,
        ordering: false,
        pageLength: 10,
        columnDefs: [{ orderable: false, targets: -1 }],
        language: {
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_ to _END_ of _TOTAL_ entries'
        }
    });

    $('.dataTables_filter input').attr('placeholder', 'Search accounts...');

    // SweetAlert confirm for delete
    $(document).on('click', '.delete-btn', function(e) {
        e.preventDefault();
        var form = $(this).closest('form');
        Swal.fire({
            title: 'Are you sure?',
            text: "This account will be deleted.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
</script>
</body>
</html>
