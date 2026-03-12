<?php
include_once('../backend/officeData.php'); 
$offices = getOfficeData($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Office Management</title>

    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.css">

    <!-- DATATABLES CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="../css/office.css">
    <link rel="stylesheet" href="../css/table-unified.css">
</head>
<body class="p-4">

<div class="container-fluid">

    <div class="office-topbar">
        <div>
            <h2 class="office-page-title mb-0"><i class="fas fa-building me-2"></i>Manage Offices</h2>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-success office-add-btn" id="openAddOfficeBtn" data-bs-toggle="modal" data-bs-target="#officeModal">
                <i class="fas fa-plus me-2"></i>Add Office
            </button>
        </div>
    </div>

    <!-- ---------------- TABLE CARD ---------------- -->
    <div class="card shadow-sm border-0 office-table-card">

        <div class="card-header text-white office-table-header">
            <div>
                <h5 class="mb-0"><i class="fa-solid fa-building me-2"></i>All Offices</h5>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive d-none d-md-block">

                <table id="officeTable" class="table table-bordered table-hover align-middle nowrap w-100">
                    <thead class="table-success">
                        <tr>
                            <th>#</th>
                            <th>Office Code</th>
                            <th>Office Description</th>
                            <th>Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (!empty($offices)) : $counter = 1; ?>
                            <?php foreach ($offices as $office) : ?>
                                <tr 
                                    data-id="<?php echo $office['office_id']; ?>" 
                                    data-code="<?php echo htmlspecialchars($office['office_code']); ?>"
                                    data-description="<?php echo htmlspecialchars($office['office_description']); ?>"
                                    data-enabled="<?php echo intval($office['enabled']); ?>"
                                >
                                    <td><?php echo $counter++; ?></td>
                                    <td><?php echo htmlspecialchars($office['office_code']); ?></td>
                                    <td><?php echo htmlspecialchars($office['office_description']); ?></td>
                                    <td><?php echo intval($office['enabled']) ? "Active" : "Inactive"; ?></td>

                                    <td class="text-center">

                                        <!-- EDIT -->
                                        <button type="button" class="editBtn btn btn-sm btn-warning me-1">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <!-- ENABLE/DISABLE -->
                                        <button type="button"
                                                class="btn btn-sm <?php echo intval($office['enabled']) ? 'btn-secondary' : 'btn-success'; ?> toggleBtn"
                                                data-office-id="<?php echo $office['office_id']; ?>"
                                                data-office-name="<?php echo htmlspecialchars($office['office_description']); ?>"
                                                data-current-status="<?php echo intval($office['enabled']); ?>">
                                            <i class="fa-solid fa-<?php echo intval($office['enabled']) ? 'toggle-on' : 'toggle-off'; ?>"></i>
                                        </button>

                                        <!-- DELETE -->
                                        <button type="button"
                                                class="btn btn-sm btn-danger deleteBtn"
                                                data-office-id="<?php echo $office['office_id']; ?>"
                                                data-office-name="<?php echo htmlspecialchars($office['office_description']); ?>">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>

                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr><td colspan="5" class="text-center py-3">No data found</td></tr>
                        <?php endif; ?>
                    </tbody>

                </table>
            </div>

            <div class="d-block d-md-none mt-2">
                <div class="mobile-table-search">
                    <input type="text" class="form-control" id="officeMobileSearch" placeholder="Search offices...">
                </div>
                <div id="officeMobileEmpty" class="mobile-table-empty" style="display:none;">No offices found.</div>
                <div id="officeMobileList" class="mobile-table-list">
                    <?php if (!empty($offices)) : $mobileCounter = 1; ?>
                        <?php foreach ($offices as $office) : ?>
                            <?php $collapseId = 'officeMobile' . (int)$office['office_id']; ?>
                            <div class="mobile-table-card"
                                 data-id="<?php echo $office['office_id']; ?>"
                                 data-code="<?php echo htmlspecialchars($office['office_code'], ENT_QUOTES); ?>"
                                 data-description="<?php echo htmlspecialchars($office['office_description'], ENT_QUOTES); ?>"
                                 data-enabled="<?php echo intval($office['enabled']); ?>"
                                 data-search-text="<?php echo htmlspecialchars(($office['office_code'] ?? '') . ' ' . ($office['office_description'] ?? '') . ' ' . (intval($office['enabled']) ? 'Active' : 'Inactive'), ENT_QUOTES); ?>">
                                <button class="mobile-table-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo $collapseId; ?>" aria-expanded="false" aria-controls="<?php echo $collapseId; ?>">
                                    <span class="mobile-table-icon"><i class="fa-solid fa-building"></i></span>
                                    <span class="mobile-table-summary">
                                        <span class="mobile-table-title"><?php echo $mobileCounter++; ?>. <?php echo htmlspecialchars($office['office_description']); ?></span>
                                        <span class="mobile-table-meta"><?php echo htmlspecialchars($office['office_code']); ?> · <?php echo intval($office['enabled']) ? 'Active' : 'Inactive'; ?></span>
                                    </span>
                                    <span class="mobile-table-chevron"><i class="fa-solid fa-chevron-down"></i></span>
                                </button>
                                <div id="<?php echo $collapseId; ?>" class="collapse mobile-table-body">
                                    <div class="mobile-table-grid">
                                        <div class="mobile-table-field"><span class="mobile-table-label">Office Code</span><span class="mobile-table-value"><?php echo htmlspecialchars($office['office_code']); ?></span></div>
                                        <div class="mobile-table-field"><span class="mobile-table-label">Office Description</span><span class="mobile-table-value"><?php echo htmlspecialchars($office['office_description']); ?></span></div>
                                        <div class="mobile-table-field"><span class="mobile-table-label">Status</span><span class="mobile-table-value"><?php echo intval($office['enabled']) ? 'Active' : 'Inactive'; ?></span></div>
                                    </div>
                                    <div class="mobile-table-actions">
                                        <button type="button" class="editBtn btn btn-sm btn-warning">
                                            <i class="fas fa-edit me-1"></i>Edit
                                        </button>
                                        <button type="button"
                                                class="btn btn-sm <?php echo intval($office['enabled']) ? 'btn-secondary' : 'btn-success'; ?> toggleBtn"
                                                data-office-id="<?php echo $office['office_id']; ?>"
                                                data-office-name="<?php echo htmlspecialchars($office['office_description']); ?>"
                                                data-current-status="<?php echo intval($office['enabled']); ?>">
                                            <i class="fa-solid fa-<?php echo intval($office['enabled']) ? 'toggle-on' : 'toggle-off'; ?> me-1"></i><?php echo intval($office['enabled']) ? 'Disable' : 'Enable'; ?>
                                        </button>
                                        <button type="button"
                                                class="btn btn-sm btn-danger deleteBtn"
                                                data-office-id="<?php echo $office['office_id']; ?>"
                                                data-office-name="<?php echo htmlspecialchars($office['office_description']); ?>">
                                            <i class="fa-solid fa-trash me-1"></i>Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="officeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content office-modal-content">
                <form id="officeForm" method="POST" action="../backend/officeData.php">
                    <div class="modal-header office-modal-header text-white">
                        <div>
                            <h5 class="modal-title mb-0" id="officeModalTitle">Add Office</h5>
                            <small class="office-modal-subtitle">Set the office code and description before saving.</small>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="office_id" id="office_id">
                        <input type="hidden" name="form_action" id="form_action" value="">

                        <div class="row g-3 office-form-grid">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Office Code</label>
                                <input type="text" class="form-control" name="office_code" id="office_code" placeholder="Enter office code" required>
                            </div>

                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Office Description</label>
                                <input type="text" class="form-control" name="office_description" id="office_description" placeholder="Enter office description" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="cancelBtn" class="btn btn-secondary fw-semibold" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="submitBtn" class="btn btn-success fw-semibold">Add Office</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- JS LIBRARIES -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../js/tableMobileCards.js"></script>
<script src="../js/office.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.mobileTableCards) {
        window.mobileTableCards.register('officeMobileSearch', 'officeMobileList', 'officeMobileEmpty', {
            emptyText: 'No offices found.',
            emptySearchText: 'No offices match your search.'
        });
    }
});
</script>
</body>
</html>
