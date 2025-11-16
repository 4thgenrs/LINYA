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
