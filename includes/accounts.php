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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accounts Table</title>

    <!-- Bootstrap + Font Awesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

    <!-- DataTables Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">

    <link rel="stylesheet" href="../css/accounts.css">
    <link rel="stylesheet" href="../css/table-unified.css">
</head>
<body class="py-4">

<div class="container-fluid">

    <div class="accounts-topbar">
        <div>
            <h2 class="accounts-page-title mb-0"><i class="fas fa-users me-2"></i>Manage All Users</h2>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-success accounts-add-btn" id="openAddAccountBtn" data-bs-toggle="modal" data-bs-target="#accountsModal">
                <i class="fas fa-user-plus me-2"></i>Add User
            </button>
        </div>
    </div>

    <div class="card shadow-sm border-0 accounts-table-card">
        <div class="card-header text-white accounts-table-header">
            <div>
                <h5 class="mb-0"><i class="fa-solid fa-users me-2"></i>All Users</h5>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive d-none d-md-block">
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

            <div class="d-block d-md-none mt-2">
                <div class="row g-2 mb-3 accounts-mobile-filters">
                        <div class="col-12">
                            <input type="text" class="form-control" id="accountsMobileSearch" placeholder="Search accounts...">
                        </div>
                        <div class="col-6">
                            <select class="form-select" id="accountsMobileRoleFilter" aria-label="Filter by role">
                                <option value="">All roles</option>
                                <?php foreach ($roles as $r) { ?>
                                    <option value="<?php echo htmlspecialchars($r['role_id']); ?>"><?php echo htmlspecialchars($r['role_name']); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <select class="form-select" id="accountsMobileOfficeFilter" aria-label="Filter by office">
                                <option value="">All offices</option>
                                <?php foreach ($offices as $o) { ?>
                                    <option value="<?php echo htmlspecialchars($o['office_id']); ?>"><?php echo htmlspecialchars($o['office_description']); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                <div id="accountsMobileEmpty" class="mobile-table-empty" style="display:none;">No accounts found.</div>
                <div id="accountsMobileList" class="mobile-table-list">
                    <?php if (!empty($accounts)) : $mobileCounter = 1; ?>
                        <?php foreach ($accounts as $acc) : ?>
                            <?php $collapseId = 'accountMobile' . (int)$acc['user_id']; ?>
                            <div class="mobile-table-card mobile-account-card"
                                 data-id="<?php echo htmlspecialchars($acc['user_id']); ?>"
                                 data-username="<?php echo htmlspecialchars($acc['user_name'], ENT_QUOTES); ?>"
                                 data-password="<?php echo htmlspecialchars($acc['password'], ENT_QUOTES); ?>"
                                 data-name="<?php echo htmlspecialchars($acc['name'], ENT_QUOTES); ?>"
                                 data-role="<?php echo htmlspecialchars($acc['user_type'], ENT_QUOTES); ?>"
                                 data-role-name="<?php echo htmlspecialchars($acc['role_name'], ENT_QUOTES); ?>"
                                 data-office="<?php echo htmlspecialchars($acc['office'], ENT_QUOTES); ?>"
                                 data-office-name="<?php echo htmlspecialchars($acc['office_description'], ENT_QUOTES); ?>"
                                 data-search-text="<?php echo htmlspecialchars(($acc['user_name'] ?? '') . ' ' . ($acc['name'] ?? '') . ' ' . ($acc['role_name'] ?? '') . ' ' . ($acc['office_description'] ?? ''), ENT_QUOTES); ?>">
                                <button class="mobile-table-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo $collapseId; ?>" aria-expanded="false" aria-controls="<?php echo $collapseId; ?>">
                                    <span class="mobile-account-initial"><?php echo strtoupper(substr($acc['user_name'], 0, 1)); ?></span>
                                    <span class="mobile-table-summary">
                                        <span class="mobile-table-title"><?php echo htmlspecialchars($acc['user_name']); ?></span>
                                        <span class="mobile-table-meta"><?php echo htmlspecialchars($acc['name']); ?></span>
                                    </span>
                                    <span class="mobile-table-chevron"><i class="fa-solid fa-chevron-down"></i></span>
                                </button>
                                <div id="<?php echo $collapseId; ?>" class="collapse mobile-table-body">
                                    <div class="mobile-table-grid">
                                        <div class="mobile-table-field"><span class="mobile-table-label">Username</span><span class="mobile-table-value"><strong><?php echo htmlspecialchars($acc['user_name']); ?></strong></span></div>
                                        <div class="mobile-table-field"><span class="mobile-table-label">Password</span><span class="mobile-table-value"><?php echo htmlspecialchars($acc['password']); ?></span></div>
                                        <div class="mobile-table-field"><span class="mobile-table-label">Full Name</span><span class="mobile-table-value"><?php echo htmlspecialchars($acc['name']); ?></span></div>
                                        <div class="mobile-table-field"><span class="mobile-table-label">User Type</span><span class="mobile-table-value"><?php echo htmlspecialchars($acc['role_name']); ?></span></div>
                                        <div class="mobile-table-field"><span class="mobile-table-label">Office</span><span class="mobile-table-value"><?php echo htmlspecialchars($acc['office_description']); ?></span></div>
                                    </div>
                                    <div class="mobile-table-actions">
                                        <button type="button" class="btn btn-sm btn-warning editBtn">
                                            <i class="fa-solid fa-pen-to-square me-1"></i>Edit
                                        </button>
                                        <form method="POST">
                                            <input type="hidden" name="delete_id" value="<?php echo $acc['user_id']; ?>">
                                            <button type="submit" name="delete_account" class="btn btn-sm btn-danger delete-btn">
                                                <i class="fa-solid fa-trash me-1"></i>Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="accountsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered accounts-modal-dialog">
            <div class="modal-content accounts-modal-content">
                <form id="accountsForm" method="POST">
                    <div class="modal-header accounts-modal-header text-white">
                        <div>
                            <h5 class="modal-title mb-0" id="accountsModalTitle">Add User</h5>
                            <small class="accounts-modal-subtitle">Fill in the account details and save the record.</small>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="user_id" id="user_id">

                        <div class="row g-3 accounts-form-grid">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Username</label>
                                <input type="text" class="form-control" name="account_username" id="account_username" placeholder="Enter username" required>
                                <div id="usernameFeedback" class="text-danger small mt-1" style="display:none"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Password</label>
                                <input type="text" class="form-control" name="account_password" id="account_password" placeholder="Enter password" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Name</label>
                                <input type="text" class="form-control" name="account_name" id="account_name" placeholder="Enter name" required>
                                <div id="nameFeedback" class="text-danger small mt-1" style="display:none"></div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">User Type</label>
                                <select class="form-select" name="role_id" id="role_id" required>
                                  <option value="">Select User Type</option>
                                  <?php foreach ($roles as $r) { ?>
                                    <option value="<?php echo $r['role_id']; ?>"><?php echo $r['role_name']; ?></option>
                                  <?php } ?>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Office</label>
                                <select class="form-select" name="office_id" id="office_id" required>
                                  <option value="">Select Office</option>
                                  <?php foreach ($offices as $o) { ?>
                                    <option value="<?php echo $o['office_id']; ?>"><?php echo $o['office_description']; ?></option>
                                  <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i>Cancel</button>
                        <input type="submit" name="add_office" id="submitBtn" value="Add User" class="btn btn-success fw-semibold">
                    </div>
                </form>
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
