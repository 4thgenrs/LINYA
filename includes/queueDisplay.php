<?php
session_start();
include '../backend/queueDisplayData.php';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Queue Display</title>

    <!-- Bootstrap + FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    
    <!-- DataTables Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="../css/queueDisplay.css">
    <link rel="stylesheet" href="../css/table-unified.css">
</head>

<body>

<div class="container-fluid">

<?php
$queueRows = [];
while ($row = $res->fetch_assoc()):
    $qst = $conn->prepare("SELECT s.service_description 
                           FROM queue_services qs 
                           JOIN services s ON qs.service_id = s.service_id 
                           WHERE qs.queue_id=?");
    $qst->bind_param("i", $row['queue_id']);
    $qst->execute();
    $gr = $qst->get_result();
    $svc = [];
    while ($r2 = $gr->fetch_assoc()) $svc[] = $r2['service_description'];
    $qst->close();
    $row['service_list'] = implode(', ', $svc);
    $queueRows[] = $row;
endwhile;
?>

    <section class="queue-display-hero">
        <div class="queue-display-hero-copy">
            <span class="queue-display-hero-badge">
                <i class="fa-solid fa-list-ol"></i>
                Queue Overview
            </span>
            <h1><i class="fa-solid fa-list-ol me-2"></i>Queue Display</h1>
            <p class="queue-display-hero-subtitle">Monitor live queue entries, review office-specific activity, and reset displayed queues when needed from one centralized overview.</p>
        </div>
        <div class="queue-display-hero-side">
            <div class="queue-display-status-pill">
                <span class="queue-display-status-dot"></span>
                Live queue monitor
            </div>
            <span class="queue-display-status-copy">Filter queue records by office, inspect current statuses, and perform a reset action from the toolbar below.</span>
        </div>
    </section>

    <!-- FILTER + RESET CONTROLS -->
    <section class="queue-display-toolbar card shadow-sm border-0">
        <div class="card-body">
            <div class="queue-display-toolbar-row">
                <div class="queue-display-toolbar-copy">
                    <span class="queue-display-section-eyebrow">Display Controls</span>
                    <h2>Queue Filters</h2>
                    <p>Choose an office to narrow the list or reset the active queue display when you need a clean slate.</p>
                </div>

                <form id="filterForm" method="get" class="queue-display-toolbar-controls">

                <!-- Office Filter -->
                <div class="queue-display-filter-group">
                    <label class="form-label fw-semibold mb-0" for="office_id">Filter by Office</label>
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
                <div class="queue-display-action-group">
                    <button id="resetBtn" type="button" onclick="resetQueues()" 
                            class="btn btn-danger fw-semibold queue-display-reset-btn">
                        <i class="fa-solid fa-rotate-left me-1"></i> Reset All
                    </button>
                </div>

            </form>
            </div>
        </div>
    </section>


    <!-- QUEUE TABLE CARD -->
    <div class="card shadow-sm border-0 queue-display-table-card">

        <!-- Card Header -->
        <div class="card-header text-white queue-display-table-header">
            <div>
                <span class="queue-display-section-eyebrow is-light">Queue Directory</span>
                <h5 class="mb-0"><i class="fa-solid fa-list me-2"></i>Queue List</h5>
            </div>
            <div class="queue-display-table-icon">
                <i class="fa-solid fa-table-list"></i>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive d-none d-md-block">
                <table id="queueTable" class="table table-hover table-bordered align-middle nowrap w-100">
                    <thead class="table-success">
                        <tr>
                            <th>Queue Code</th>
                            <th>Office</th>
                            <th>Services</th>
                            <th>Created</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($queueRows as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['queue_code']) ?></td>
                                <td><?= htmlspecialchars($row['office_description']) ?></td>
                                <td><?= htmlspecialchars($row['service_list']) ?></td>
                                <td><?= htmlspecialchars($row['created_at']) ?></td>
                                <td class="status <?= strtolower($row['status']) ?>">
                                    <?= htmlspecialchars($row['status']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="d-block d-md-none mt-2">
                <div class="mobile-table-search">
                    <input type="text" class="form-control" id="queueMobileSearch" placeholder="Search queues...">
                </div>
                <div id="queueMobileEmpty" class="mobile-table-empty" style="display:none;">No queues found.</div>
                <div id="queueMobileList" class="mobile-table-list">
                    <?php foreach($queueRows as $index => $row): ?>
                        <?php $collapseId = 'queueMobile' . ($index + 1); ?>
                        <div class="mobile-table-card" data-search-text="<?= htmlspecialchars(($row['queue_code'] ?? '') . ' ' . ($row['office_description'] ?? '') . ' ' . ($row['service_list'] ?? '') . ' ' . ($row['status'] ?? ''), ENT_QUOTES) ?>">
                            <button class="mobile-table-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $collapseId ?>" aria-expanded="false" aria-controls="<?= $collapseId ?>">
                                <span class="mobile-table-icon"><i class="fa-solid fa-list-ol"></i></span>
                                <span class="mobile-table-summary">
                                    <span class="mobile-table-title"><?= htmlspecialchars($row['queue_code']) ?></span>
                                    <span class="mobile-table-meta"><?= htmlspecialchars($row['office_description']) ?> · <?= htmlspecialchars($row['status']) ?></span>
                                </span>
                                <span class="mobile-table-chevron"><i class="fa-solid fa-chevron-down"></i></span>
                            </button>
                            <div id="<?= $collapseId ?>" class="collapse mobile-table-body">
                                <div class="mobile-table-grid">
                                    <div class="mobile-table-field"><span class="mobile-table-label">Office</span><span class="mobile-table-value"><?= htmlspecialchars($row['office_description']) ?></span></div>
                                    <div class="mobile-table-field"><span class="mobile-table-label">Services</span><span class="mobile-table-value"><?= htmlspecialchars($row['service_list']) ?: '—' ?></span></div>
                                    <div class="mobile-table-field"><span class="mobile-table-label">Created</span><span class="mobile-table-value"><?= htmlspecialchars($row['created_at']) ?></span></div>
                                    <div class="mobile-table-field"><span class="mobile-table-label">Status</span><span class="mobile-table-value"><span class="status <?= strtolower($row['status']) ?>"><?= htmlspecialchars($row['status']) ?></span></span></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- JS Libraries -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/tableMobileCards.js"></script>

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

    if (window.mobileTableCards) {
        window.mobileTableCards.register('queueMobileSearch', 'queueMobileList', 'queueMobileEmpty', {
            emptyText: 'No queues found.',
            emptySearchText: 'No queues match your search.'
        });
    }
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
