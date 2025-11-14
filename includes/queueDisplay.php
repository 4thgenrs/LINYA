<?php
include '../backend/queueDisplayData.php';
// require login

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Queue Display</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Bootstrap + Font Awesome + DataTables -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">

  <style>
    body { background-color: #f8f9fa; }
    .queue-header { background-color: #1b5e20; color: #fff; }
    #queueTable thead th { background-color: #d8efe1 !important; color: #0b3b21; font-weight:700; text-align:center; }
    #queueTable tbody td { text-align:center; vertical-align: middle; }
    .dataTables_wrapper .dataTables_filter input { width: 220px; }
    .status.pending { color: #735702; font-weight: 600; }
    .status.completed { color: #155724; font-weight: 600; }
    .status.cancelled { color: #721c24; font-weight: 600; }
  </style>
</head>

<body>
<div class="container-fluid py-4">

    <!-- Header + Filter + Reset (Title left, Controls right) -->
    <div class="row align-items-center mb-3">
    <!-- Title -->
    <div class="col">
        <h3 class="fw-bold mb-0 text-dark">
        <i class="fa-solid fa-people-line me-2 text-success"></i>Queue Display
        </h3>
    </div>

    <!-- Filter + Reset (aligned right) -->
    <div class="col-auto d-flex align-items-center gap-2">
        <form id="filterForm" method="get" class="d-flex align-items-center mb-0">
        <label for="office_id" class="form-label fw-semibold me-2 mb-0">Filter by Office:</label>
        <select name="office_id" id="office_id" class="form-select w-auto"
                onchange="document.getElementById('filterForm').submit()">
            <option value="0">All</option>
            <?php foreach($offices as $o): ?>
            <option value="<?= $o['office_id'] ?>" <?= ($officeFilter == $o['office_id'] ? 'selected' : '') ?>>
                <?= htmlspecialchars($o['office_description']) ?>
            </option>
            <?php endforeach; ?>
        </select>
        </form>

        <button id="resetBtn" onclick="resetQueues()" class="btn btn-danger">
        <i class="fa-solid fa-rotate-left me-1"></i> Reset All
        </button>
    </div>
    </div>

    <!-- Queue Table -->
    <div class="card shadow-sm border-0">
        <div class="card-header queue-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fa-solid fa-list me-2"></i>Queues</h5>
        </div>
        <div class="card-body">
        <div class="table-responsive">
            <table id="queueTable" class="table table-hover table-bordered align-middle w-100">
            <thead>
                <tr>
                <th>#</th>
                <th>Queue Code</th>
                <th>Office</th>
                <th>Services</th>
                <th>Created</th>
                <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $counter = 1;
                while($row = $res->fetch_assoc()): 
                    $qst = $conn->prepare("SELECT s.service_description FROM queue_services qs JOIN services s ON qs.service_id = s.service_id WHERE qs.queue_id=?");
                    $qst->bind_param("i",$row['queue_id']);
                    $qst->execute();
                    $gr = $qst->get_result();
                    $svc = [];
                    while($r2 = $gr->fetch_assoc()) $svc[] = $r2['service_description'];
                    $qst->close();
                ?>
                <tr>
                <td><?= $counter++ ?></td>
                <td><?= htmlspecialchars($row['queue_code']) ?></td>
                <td><?= htmlspecialchars($row['office_description']) ?></td>
                <td><?= htmlspecialchars(implode(', ', $svc)) ?></td>
                <td><?= htmlspecialchars($row['created_at']) ?></td>
                <td class="status <?= strtolower($row['status']) ?>"><?= htmlspecialchars($row['status']) ?></td>
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

    <script>
    $(document).ready(function() {
    $('#queueTable').DataTable({
        responsive: true,
        ordering: false,
        pageLength: 10,
        lengthMenu: [[10,25,50,-1],[10,25,50,'All']],
        language: { 
        lengthMenu: 'Show _MENU_ entries', 
        info: 'Showing _START_ to _END_ of _TOTAL_ queues' 
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
        if (!response.ok || (json && json.success === false)) {
        alert('Reset failed: ' + (json.message || 'Server error'));
        throw new Error(json.message || 'Reset failed');
        }
        location.reload();
    })
    .catch(err => console.error('Reset failed', err));
    }
    </script>
</body>
</html>
