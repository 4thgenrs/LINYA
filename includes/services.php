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

<form id="serviceForm" method="POST">
    <input type="hidden" name="update_id" id="update_id" value="">
    <input type="text" name="service_description" id="service_description" placeholder="Enter service description" required>
    <select name="office_id" id="office_id" required>
        <option value="">Select Office</option>
        <?php foreach ($offices as $o) { ?>
            <option value="<?php echo $o['office_id']; ?>"><?php echo $o['office_description']; ?></option>
        <?php } ?>
    </select>
    <input type="submit" id="submitBtn" value="Add Service">
</form>


<!-- Services Table -->
<table id="servicesTable">
    <tr>
        <th>#</th>
        <th>Service Description</th>
        <th>Office</th>
        <th>Action</th>
    </tr>
    <?php if (!empty($services)) {
        $counter = 1;
        foreach ($services as $row) { ?>
            <tr 
                data-id="<?php echo $row['service_id']; ?>"
                data-description="<?php echo htmlspecialchars($row['service_description']); ?>"
                data-office="<?php echo $row['office_description']; ?>" 
            >
                <td><?php echo $counter++; ?></td>
                <td><?php echo $row['service_description']; ?></td>
                <td><?php echo $row['office_description']; ?></td>
                <td>
                    <button type="button" class="editBtn">Edit</button>
                    
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="delete_id" value="<?php echo $row['service_id']; ?>">
                        <input type="submit" value="Delete" onclick="return confirm('Are you sure you want to delete this service?');">
                    </form>
                </td>
            </tr>
    <?php }
    } else { ?>
        <tr><td colspan="4">No data found</td></tr>
    <?php } ?>
</table>

<!-- External JS -->
<script src="../js/services.js"></script>
</body>
</html>
