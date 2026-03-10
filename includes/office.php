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

    <section class="office-hero">
        <div class="office-hero-copy">
            <span class="office-hero-badge">
                <i class="fa-solid fa-building"></i>
                Office Management
            </span>
            <h1><i class="fa-solid fa-building me-2"></i>Office Management</h1>
            <p class="office-hero-subtitle">Manage office records, update codes and descriptions, and control active office availability from one workspace.</p>
        </div>
        <div class="office-hero-side">
            <div class="office-status-pill">
                <span class="office-status-dot"></span>
                Live office registry
            </div>
            <span class="office-status-copy">Create new offices, revise details, and enable or disable office availability without changing your existing workflow.</span>
        </div>
    </section>

    <!-- ---------------- FORM CARD ---------------- -->
    <section class="office-form-card card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="office-section-heading">
                <div>
                    <span class="office-section-eyebrow">Office Form</span>
                    <h2>Add or Update Office</h2>
                    <p>Set the office code and description, then save changes or switch to update mode from the table below.</p>
                </div>
                <div class="office-section-icon">
                    <i class="fa-solid fa-building-circle-check"></i>
                </div>
            </div>
        <form id="officeForm" method="POST" action="../backend/officeData.php">
            <input type="hidden" name="office_id" id="office_id">
            <input type="hidden" name="form_action" id="form_action" value="">

            <div class="row g-3 office-form-grid">

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Office Code</label>
                    <input type="text" class="form-control" name="office_code" id="office_code"
                           placeholder="Enter office code" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Office Description</label>
                    <input type="text" class="form-control" name="office_description" id="office_description"
                           placeholder="Enter office description" required>
                </div>

                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" id="submitBtn" 
                           class="btn btn-success fw-semibold flex-grow-1">
                        Add Office
                    </button>
                    <button type="button" id="cancelBtn" class="btn btn-secondary fw-semibold" style="display:none;">
                        Cancel
                    </button>
                </div>

            </div>
        </form>
        </div>
    </section>

    <!-- ---------------- TABLE CARD ---------------- -->
    <div class="card shadow-sm border-0 office-table-card">

        <div class="card-header text-white office-table-header">
            <div>
                <span class="office-section-eyebrow is-light">Office Directory</span>
                <h5 class="mb-0">Office List</h5>
            </div>
            <div class="office-table-icon">
                <i class="fa-solid fa-table-list"></i>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">

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
        </div>
    </div>
</div>

<!-- JS LIBRARIES -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../js/office.js"></script>
</body>
</html>
