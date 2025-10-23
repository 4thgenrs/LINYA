<?php include '../backend/queueDisplayData.php'; ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Queue Display</title>
    <link rel="stylesheet" href="../css/queueDisplay.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div class="container">
        <header>
            <h1>Queue Display</h1>
        </header>

        <div class="controls">
            <form id="filterForm" method="get">
                <label for="office_id">Filter by Office:</label>
                <select name="office_id" id="office_id" onchange="document.getElementById('filterForm').submit()">
                    <option value="0">All</option>
                    <?php foreach($offices as $o): ?>
                        <option value="<?= $o['office_id'] ?>" <?= ($officeFilter == $o['office_id'] ? 'selected' : '') ?>>
                            <?= htmlspecialchars($o['office_description']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>

            <button id="resetBtn" onclick="resetQueues()">Reset All</button>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Queue Code</th>
                        <th>Office</th>
                        <th>Services</th>
                        <th>Created</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $res->fetch_assoc()): ?>
                        <?php
                            $qst = $conn->prepare("SELECT s.service_description FROM queue_services qs JOIN services s ON qs.service_id = s.service_id WHERE qs.queue_id=?");
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
                            <td><?= htmlspecialchars($row['created_at']) ?></td>
                            <td class="status <?= strtolower($row['status']) ?>"><?= htmlspecialchars($row['status']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    function resetQueues() {
        if (confirm('Reset all queues?')) {
            fetch('../backend/queueReset.php', {method:'POST'})
                .then(() => location.reload());
        }
    }
    </script>
</body>
</html>
