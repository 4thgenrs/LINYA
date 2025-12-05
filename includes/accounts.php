<?php
include('../backend/accountsData.php');
include('../backend/servicesData.php');
include('../linya_db/config.php');

$accounts = getAccountsData($conn);
$offices = getOfficeData($conn);
$roles = getRolesData($conn);

// server-side messages
$message = '';
if (isset($_GET['error'])) {
    $err = $_GET['error'];
    if ($err === 'duplicate_username') $message = 'Username already exists. Please choose another.';
    if ($err === 'duplicate_name') $message = 'Name already exists. Please choose another.';
    if ($err === 'missing_fields') $message = 'Please fill all required fields.';
}
if (isset($_GET['success'])) {
    $s = $_GET['success'];
    if ($s === 'added') $message = 'Account added successfully.';
    if ($s === 'updated') $message = 'Account updated successfully.';
    if ($s === 'deleted') $message = 'Account deleted.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Accounts Table</title>

    <!-- Bootstrap + Font Awesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

    <!-- DataTables Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">

    <link rel="stylesheet" href="../css/accounts.css">
</head>
<body class="py-4">

<div class="container-fluid">

    <h2 class="mb-4 fw-bold"><i class="fa-solid fa-users-gear me-2"></i>Accounts List</h2>

    <!-- ==================== ACCOUNT FORM ===================== -->
    <div class="mb-4">
        <form id="accountsForm" method="POST">
            <input type="hidden" name="user_id" id="user_id">

            <div class="row g-3">

                <div class="col-md-2">
                    <label class="form-label fw-semibold">Username</label>
                    <input type="text" class="form-control" name="account_username" id="account_username"
                           placeholder="Enter username" required>
                    <div id="usernameFeedback" class="text-danger small mt-1" style="display:none"></div>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold">Password</label>
                    <input type="text" class="form-control" name="account_password" id="account_password"
                           placeholder="Enter password" required>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold">Name</label>
                    <input type="text" class="form-control" name="account_name" id="account_name"
                           placeholder="Enter name" required>
                    <div id="nameFeedback" class="text-danger small mt-1" style="display:none"></div>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold">User Type</label>
                    <select class="form-select" name="role_id" id="role_id" required>
                      <option value="">Select User Type</option>
                      <?php foreach ($roles as $r) { ?>
                        <option value="<?php echo $r['role_id']; ?>"><?php echo $r['role_name']; ?></option>
                      <?php } ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold">Office</label>
                    <select class="form-select" name="office_id" id="office_id" required>
                      <option value="">Select Office</option>
                      <?php foreach ($offices as $o) { ?>
                        <option value="<?php echo $o['office_id']; ?>"><?php echo $o['office_description']; ?></option>
                      <?php } ?>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <input type="submit" name="add_office" id="submitBtn" 
                           value="Add Account"
                           class="btn btn-success w-100 fw-semibold">
                </div>

            </div>
        </form>
    </div>



    <!-- ==================== TABLE SECTION (NEW UI STYLE) ===================== -->
    <div class="card shadow-sm border-0">
        <div class="card-header text-white" style="background-color: #27693f;">
            <h5 class="mb-0"><i class="fa-solid fa-list me-2"></i>Accounts List</h5>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table id="accountsTable" class="table table-hover table-bordered align-middle nowrap w-100">
                    <thead class="table-success">
                        <tr>
                            <th>#</th>
                            <th>Username</th>
                            <th>Password</th>
                            <th>Name</th>
                            <th>User Type</th>
                            <th>Office</th>
                            <th class="text-center">Actions</th>
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

                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-warning editBtn me-1">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>

                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="delete_id" value="<?php echo $acc['user_id']; ?>">
                                            <button type="submit" name="delete_account"
                                                    class="btn btn-sm btn-danger delete-btn">
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



<!-- jQuery + DataTables + Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="../js/accounts.js"></script>

<!-- DataTable Initialization -->
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

    // Show SweetAlert for server messages
    <?php if ($message): ?>
        <?php
        $icon = 'info';
        $title = 'Notification';
        if (isset($_GET['error'])) {
            $icon = 'error';
            $title = 'Error';
        } elseif (isset($_GET['success'])) {
            $icon = 'success';
            $title = 'Success';
        }
        ?>
        Swal.fire({
            title: '<?php echo $title; ?>',
            text: '<?php echo addslashes($message); ?>',
            icon: '<?php echo $icon; ?>',
            confirmButtonColor: '#1b5e20'
        });
    <?php endif; ?>
});
</script>

</body>
</html>
