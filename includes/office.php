<?php
include('../backend/officeData.php');
include('../linya_db/config.php');

$offices = getOfficeData($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Offices Table</title>
    <link rel="stylesheet" type="text/css" href="../css/office.css">
</head>
<body>
    <h2>Offices List</h2>
    <table>
        <thead>
            <tr>
                <th>Office ID</th>
                <th>Office Description</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($offices)) : ?>
                <?php foreach ($offices as $office) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($office['office_id']); ?></td>
                        <td><?php echo htmlspecialchars($office['office_description']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="2">No data found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>