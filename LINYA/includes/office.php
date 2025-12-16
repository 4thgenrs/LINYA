<?php
include_once('../backend/officeData.php'); // provides getOfficeData(), add/update helpers (no POST runs now)
$offices = getOfficeData($conn);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Offices CRUD</title>
    <link rel="stylesheet" href="../css/office.css">
</head>
<body>
    <?php
    // show simple alerts for known errors/success states
    $msg = '';
    if (isset($_GET['error'])) {
        switch ($_GET['error']) {
            case 'duplicate_office':
                $msg = 'An office with that code or description already exists.';
                break;
            case 'active_queues':
                $msg = 'Cannot delete office: there are active queues for this office.';
                break;
            default:
                $msg = 'An error occurred.';
        }
    }
    if (!empty($msg)) {
        echo '<div class="alert alert-error" style="background:#ffecec;border:1px solid #f5c6cb;padding:10px;margin:12px;border-radius:6px;color:#611;">' . htmlspecialchars($msg) . '</div>';
    }
    if (isset($_GET['success']) && $_GET['success'] === 'added') {
        echo '<div class="alert alert-success" style="background:#ecffef;border:1px solid #b6e6c1;padding:10px;margin:12px;border-radius:6px;color:#064">Office saved successfully.</div>';
    }
    ?>
    <h2>Office Management</h2>

    <!-- Add/Update Form -->
    <form id="officeForm" method="POST" action="../backend/officeData.php">
        <input type="hidden" name="office_id" id="office_id" value="">
        
        <input type="text" name="office_code" id="office_code" placeholder="Enter office code" required>
        <input type="text" name="office_description" id="office_description" placeholder="Enter office description" required>
        
        <input type="submit" name="add_office" id="submitBtn" value="Add Office">
    </form>

    <!-- Offices Table -->
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Office Code</th>
                <th>Office Description</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($offices)) : ?>
                <?php $counter = 1; ?>
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
                        <td>
                            <button type="button" class="editBtn">Edit</button>

                            <form method="POST" action="../backend/officeData.php" style="display:inline;">
                                <input type="hidden" name="office_id" value="<?php echo $office['office_id']; ?>">
                                <input type="submit" name="toggle_office" value="<?php echo intval($office['enabled']) ? 'Disable' : 'Enable'; ?>" onclick="return confirm('Are you sure?');">
                            </form>

                            <form method="POST" action="../backend/officeData.php" style="display:inline;">
                                <input type="hidden" name="office_id" value="<?php echo $office['office_id']; ?>">
                                <input type="submit" name="delete_office" value="Delete" onclick="return confirm('Are you sure?');">
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr><td colspan="5">No data found</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <script>
    // JS for Edit button functionality
    document.querySelectorAll('.editBtn').forEach(button => {
        button.addEventListener('click', function() {
            const row = this.closest('tr');
            document.getElementById('office_id').value = row.dataset.id;
            document.getElementById('office_code').value = row.dataset.code;
            document.getElementById('office_description').value = row.dataset.description;
            
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.value = 'Update Office';
            submitBtn.name = 'update_office';
        });
    });
    </script>

</body>
</html>
