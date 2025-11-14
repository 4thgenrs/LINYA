<?php
include('../backend/officeData.php');
include('../linya_db/config.php');

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_office'])) {
        $code = trim($_POST['office_code']);
        $desc = trim($_POST['office_description']);
        if ($code !== '' && $desc !== '') {
            addOffice($conn, $code, $desc);
        }
    }

    if (isset($_POST['update_office'])) {
        $id = intval($_POST['office_id']);
        $code = trim($_POST['office_code']);
        $desc = trim($_POST['office_description']);
        if ($id > 0 && $code !== '' && $desc !== '') {
            updateOffice($conn, $id, $code, $desc);
        }
    }

    if (isset($_POST['delete_office'])) {
        $id = intval($_POST['office_id']);
        if ($id > 0) {
            deleteOffice($conn, $id);
        }
    }
}

// Fetch data
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
    <form id="officeForm" method="POST">
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
                    >
                        <td><?php echo $counter++; ?></td>
                        <td><?php echo htmlspecialchars($office['office_code']); ?></td>
                        <td><?php echo htmlspecialchars($office['office_description']); ?></td>
                        <td>
                            <button type="button" class="editBtn">Edit</button>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="office_id" value="<?php echo $office['office_id']; ?>">
                                <input type="submit" name="delete_office" value="Delete" onclick="return confirm('Are you sure you want to delete this office?');">
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr><td colspan="4">No data found</td></tr>
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
