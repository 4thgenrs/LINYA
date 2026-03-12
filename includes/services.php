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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service List</title>

    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <!-- DATATABLES CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="../css/services.css">
    <link rel="stylesheet" href="../css/table-unified.css">
</head>

<body class="p-4">

<div class="container-fluid">

    <div class="services-topbar">
        <div>
            <h2 class="services-page-title mb-0"><i class="fas fa-layer-group me-2"></i>Manage Services</h2>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-success services-add-btn" id="openAddServiceBtn" data-bs-toggle="modal" data-bs-target="#servicesModal">
                <i class="fas fa-plus me-2"></i>Add Service
            </button>
        </div>
    </div>

    <!-- ---------- SERVICES TABLE ---------- -->
    <div class="card shadow-sm border-0 services-table-card">

        <div class="card-header text-white services-table-header">
            <div>
                <h5 class="mb-0">
                    <i class="fa-solid fa-layer-group me-2"></i>All Services
                </h5>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive d-none d-md-block">
                <table id="servicesTable" class="table table-hover table-bordered align-middle nowrap w-100">
                    <thead class="table-success">
                        <tr>
                            <th>#</th>
                            <th>Service Description</th>
                            <th>Office</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (!empty($services)) : $counter = 1; ?>
                            <?php foreach ($services as $row) : ?>
                                <tr
                                    data-id="<?php echo $row['service_id']; ?>"
                                    data-description="<?php echo htmlspecialchars($row['service_description']); ?>"
                                    data-office="<?php echo intval($row['office']); ?>"
                                    data-enabled="<?php echo intval($row['enabled']); ?>"
                                >
                                    <td><?php echo $counter++; ?></td>
                                    <td><?php echo htmlspecialchars($row['service_description']); ?></td>
                                    <td><?php echo htmlspecialchars($row['office_description']); ?></td>
                                    <td><?php echo intval($row['enabled']) ? "Active" : "Inactive"; ?></td>

                                    <td class="text-center">

                                        <!-- EDIT BUTTON -->
                                        <button type="button" class="btn btn-sm btn-warning editBtn me-1">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>

                                        <!-- ENABLE/DISABLE BUTTON -->
                                        <form method="POST" action="../backend/servicesData.php" style="display:inline;">
                                            <input type="hidden" name="service_id" value="<?php echo $row['service_id']; ?>">
                                            <button type="submit" name="toggle_service"
                                                class="btn btn-sm <?php echo intval($row['enabled']) ? 'btn-secondary' : 'btn-success'; ?>">
                                                <i class="fa-solid fa-<?php echo intval($row['enabled']) ? 'toggle-on' : 'toggle-off'; ?>"></i>
                                            </button>
                                        </form>

                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">No data found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>

                </table>
            </div>

            <div class="d-block d-md-none mt-2">
                <div class="mobile-table-search">
                    <input type="text" class="form-control" id="servicesMobileSearch" placeholder="Search services...">
                </div>
                <div id="servicesMobileEmpty" class="mobile-table-empty" style="display:none;">No services found.</div>
                <div id="servicesMobileList" class="mobile-table-list">
                    <?php if (!empty($services)) : $mobileCounter = 1; ?>
                        <?php foreach ($services as $row) : ?>
                            <?php $collapseId = 'serviceMobile' . (int)$row['service_id']; ?>
                            <div class="mobile-table-card"
                                 data-id="<?php echo $row['service_id']; ?>"
                                 data-description="<?php echo htmlspecialchars($row['service_description'], ENT_QUOTES); ?>"
                                 data-office="<?php echo intval($row['office']); ?>"
                                 data-enabled="<?php echo intval($row['enabled']); ?>"
                                 data-search-text="<?php echo htmlspecialchars(($row['service_description'] ?? '') . ' ' . ($row['office_description'] ?? '') . ' ' . (intval($row['enabled']) ? 'Active' : 'Inactive'), ENT_QUOTES); ?>">
                                <button class="mobile-table-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo $collapseId; ?>" aria-expanded="false" aria-controls="<?php echo $collapseId; ?>">
                                    <span class="mobile-table-icon"><i class="fa-solid fa-layer-group"></i></span>
                                    <span class="mobile-table-summary">
                                        <span class="mobile-table-title"><?php echo $mobileCounter++; ?>. <?php echo htmlspecialchars($row['service_description']); ?></span>
                                        <span class="mobile-table-meta"><?php echo htmlspecialchars($row['office_description']); ?> · <?php echo intval($row['enabled']) ? 'Active' : 'Inactive'; ?></span>
                                    </span>
                                    <span class="mobile-table-chevron"><i class="fa-solid fa-chevron-down"></i></span>
                                </button>
                                <div id="<?php echo $collapseId; ?>" class="collapse mobile-table-body">
                                    <div class="mobile-table-grid">
                                        <div class="mobile-table-field"><span class="mobile-table-label">Service</span><span class="mobile-table-value"><?php echo htmlspecialchars($row['service_description']); ?></span></div>
                                        <div class="mobile-table-field"><span class="mobile-table-label">Office</span><span class="mobile-table-value"><?php echo htmlspecialchars($row['office_description']); ?></span></div>
                                        <div class="mobile-table-field"><span class="mobile-table-label">Status</span><span class="mobile-table-value"><?php echo intval($row['enabled']) ? 'Active' : 'Inactive'; ?></span></div>
                                    </div>
                                    <div class="mobile-table-actions">
                                        <button type="button" class="btn btn-sm btn-warning editBtn">
                                            <i class="fa-solid fa-pen-to-square me-1"></i>Edit
                                        </button>
                                        <form method="POST" action="../backend/servicesData.php">
                                            <input type="hidden" name="service_id" value="<?php echo $row['service_id']; ?>">
                                            <button type="submit" name="toggle_service" class="btn btn-sm <?php echo intval($row['enabled']) ? 'btn-secondary' : 'btn-success'; ?>">
                                                <i class="fa-solid fa-<?php echo intval($row['enabled']) ? 'toggle-on' : 'toggle-off'; ?> me-1"></i><?php echo intval($row['enabled']) ? 'Disable' : 'Enable'; ?>
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

    <div class="modal fade" id="servicesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content services-modal-content">
                <form id="serviceForm" method="POST" action="../backend/servicesData.php">
                    <div class="modal-header services-modal-header text-white">
                        <div>
                            <h5 class="modal-title mb-0" id="servicesModalTitle">Add Service</h5>
                            <small class="services-modal-subtitle">Create a service and assign it to an office.</small>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="update_id" id="update_id">
                        <div class="row g-3 services-form-grid">
                            <div class="col-md-7">
                                <label class="form-label fw-semibold">Service Description</label>
                                <input type="text" class="form-control" name="service_description" id="service_description" placeholder="Enter service description" required>
                            </div>

                            <div class="col-md-5">
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
                        <button type="button" id="cancelBtn" class="btn btn-secondary fw-semibold" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="submitBtn" class="btn btn-success fw-semibold">Add Service</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<!-- JS Libraries -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../js/tableMobileCards.js"></script>
<script src="../js/services.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.mobileTableCards) {
        window.mobileTableCards.register('servicesMobileSearch', 'servicesMobileList', 'servicesMobileEmpty', {
            emptyText: 'No services found.',
            emptySearchText: 'No services match your search.'
        });
    }
});
</script>

</body>
</html>
