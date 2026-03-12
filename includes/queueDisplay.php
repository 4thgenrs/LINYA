<?php
session_start();
include '../backend/queueDisplayData.php';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Queue Display</title>

    <!-- Bootstrap + FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    
    <!-- DataTables Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="../css/queueDisplay.css">
</head>

<body>

<div class="container-fluid">

    <!-- PAGE TITLE -->
    <h2 class="mb-4 fw-bold">
        <i class="fa-solid fa-list-ol me-2"></i>Queue Display
    </h2>

    <!-- FILTER + RESET CONTROLS -->
    <div class="d-flex align-items-center justify-content-end mb-2">
            <form id="filterForm" method="get" class="d-flex align-items-end">

                <!-- Office Filter -->
                <div class="d-flex align-items-center me-2">
                    <label class="form-label fw-semibold mb-0 me-2" for="office_id">Filter by Office</label>
                    <select name="office_id" id="office_id" class="form-select"
                            onchange="document.getElementById('filterForm').submit()">
                        <option value="0">All</option>
                        <?php foreach($offices as $o): ?>
                            <option value="<?= $o['office_id'] ?>" <?= ($officeFilter == $o['office_id'] ? 'selected' : '') ?>>
                                <?= htmlspecialchars($o['office_description']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Reset Button -->
                <div>
                    <button id="resetBtn" type="button" onclick="resetQueues()" 
                            class="btn btn-danger fw-semibold">
                        <i class="fa-solid fa-rotate-left me-1"></i> Reset All
                    </button>
                </div>

            </form>
        </div>


    <!-- QUEUE TABLE CARD -->
    <div class="card shadow-sm border-0">

        <!-- Card Header -->
        <div class="card-header text-white" style="background-color: #1b5e20;">
            <h5 class="mb-0"><i class="fa-solid fa-list me-2"></i>Queue List</h5>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table id="queueTable" class="table table-hover table-bordered align-middle nowrap w-100">
                    <thead class="table-success">
                        <tr>
                            <th>Queue Code</th>
                            <th>Office</th>
                            <th>Services</th>
                            <th>Student No.</th>
                            <th>Course</th>
                            <th>Created</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $res->fetch_assoc()): ?>
                            <?php
                                $qst = $conn->prepare("SELECT s.service_description 
                                                       FROM queue_services qs 
                                                       JOIN services s ON qs.service_id = s.service_id 
                                                       WHERE qs.queue_id=?");
                                $qst->bind_param("i",$row['queue_id']);
                                $qst->execute();
                                $gr = $qst->get_result();
                                $svc = [];
                                while($r2 = $gr->fetch_assoc()) $svc[] = $r2['service_description'];
                                $qst->close();
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($row['queue_code']) ?></td>
                                <td><?= htmlspecialchars($row['office_description']) ?></td>
                                <td><?= htmlspecialchars(implode(', ', $svc)) ?></td>
                                <td><?= htmlspecialchars($row['student_number'] ?? '') ?></td>
                                <td><?= htmlspecialchars($row['course'] ?? '') ?></td>
                                <td><?= htmlspecialchars($row['created_at']) ?></td>
                                <td class="status <?= strtolower($row['status']) ?>">
                                    <?= htmlspecialchars($row['status']) ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Initialize DataTable
$(document).ready(function() {
    $('#queueTable').DataTable({
        responsive: true,
        ordering: false,
        pageLength: 10,
        columnDefs: [{ orderable: false, targets: -1 }],
        language: {
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_ to _END_ of _TOTAL_ entries'
        }
    });

    $('.dataTables_filter input').attr('placeholder', 'Search queues...');
});

function resetQueues() {
    if (!confirm('Reset all queues?')) return;

    fetch('../backend/queueReset.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({ confirm: '1' }).toString()
    })
    .then(async response => {
        let json = {};
        try { json = await response.json(); } catch (e) { }

        if (!response.ok) {
            const serverMsg = json && (json.message || json.error) ? (json.message || json.error) : ('Server returned ' + response.status);
            alert('Reset failed: ' + serverMsg);
            throw new Error(serverMsg);
        }

        if (json && json.success === false) {
            const msg = json.message || 'Reset failed';
            alert('Reset failed: ' + msg);
            throw new Error(msg);
        }

        location.reload();
    })
    .catch(err => {
        console.error('Reset failed', err);
        alert('Reset failed');
    });
}
</script>

</body>
</html>
