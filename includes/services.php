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
    <title>Service List</title>

    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <!-- DATATABLES CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="../css/services.css">
</head>

<body class="p-4">

<div class="container-fluid">

    <h2 class="mb-4 fw-bold">
        <i class="fa-solid fa-list me-2"></i>Service List
    </h2>

    <!-- ---------- SERVICE FORM ---------- -->
    <div class="mb-4">
        <form id="serviceForm" method="POST" action="../backend/servicesData.php">
            <input type="hidden" name="update_id" id="update_id">

            <div class="row g-3">

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Service Description</label>
                    <input type="text" class="form-control" name="service_description" id="service_description"
                           placeholder="Enter service description" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Office</label>
                    <select class="form-select" name="office_id" id="office_id" required>
                        <option value="">Select Office</option>
                        <?php foreach ($offices as $o) { ?>
                            <option value="<?php echo $o['office_id']; ?>">
                                <?php echo $o['office_description']; ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" id="submitBtn"
                           class="btn btn-success fw-semibold flex-grow-1">
                        Add Service
                    </button>
                    <button type="button" id="cancelBtn" class="btn btn-secondary fw-semibold" style="display:none;">
                        Cancel
                    </button>
                </div>

            </div>
        </form>
    </div>

    <!-- ---------- SERVICES TABLE ---------- -->
    <div class="card shadow-sm border-0">

        <div class="card-header text-white" style="background-color: #27693f;">
            <h5 class="mb-0">
                <i class="fa-solid fa-list me-2"></i>Service List
            </h5>
        </div>

        <div class="card-body">
            <div class="table-responsive">
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
        </div>

    </div>

</div>

<!-- JS Libraries -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../js/services.js"></script>

</body>
</html>
