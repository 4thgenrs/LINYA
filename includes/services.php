<?php
include('../backend/servicesData.php');
include('../linya_db/config.php');

$services = getServiceData($conn);
$offices = getOfficeData($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Service List</title>
    <link rel="stylesheet" href="../css/services.css">
</head>
<body>

<h2 style="text-align:center;">Service List</h2>

<form method="POST">
    <input type="text" name="service_description" placeholder="Enter service description" required>
    <select name="office_id" required>
        <option value="">Select Office</option>
        <?php foreach ($offices as $o) { ?>
            <option value="<?php echo $o['office_id']; ?>">
                <?php echo $o['office_description']; ?>
            </option>
        <?php } ?>
    </select>
    <input type="submit" value="Add Service">
</form>

<table>
    <tr>
        <th>#</th>
        <th>Service Description</th>
        <th>Office</th>
        <th>Action</th>
    </tr>
    <?php if (!empty($services)) {
            $counter = 1;
            foreach ($services as $row) { ?>
                <tr>
                    <td><?php echo $counter++; ?></td>
                    <td><?php echo $row['service_description']; ?></td>
                    <td><?php echo $row['office_description']; ?></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="edit_request_id" value="<?php echo $row['service_id']; ?>">
                            <input type="submit" value="Edit">
                        </form>

                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="delete_id" value="<?php echo $row['service_id']; ?>">
                            <input type="submit" value="Delete" onclick="return confirm('Are you sure you want to delete this service?');">
                        </form>
                    </td>
                </tr>
        <?php }
        } else { ?>
        <tr><td colspan="3">No data found</td></tr>
    <?php } ?>
</table>
</body>
</html>