<?php
include('../backend/servicesData.php');
include('../linya_db/config.php');

$services = getServiceData($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Service List</title>
    <style>
        table {
            width: 60%;
            border-collapse: collapse;
            margin: 20px auto;
        }
        th, td {
            border: 1px solid #333;
            padding: 8px 12px;
            text-align: center;
        }
        th {
            background: #f2f2f2;
        }
    </style>
</head>
<body>

<h2 style="text-align:center;">Service List</h2>

<table>
    <tr>
        <th>Service ID</th>
        <th>Service Description</th>
        <th>Office</th>
    </tr>
    <?php if (!empty($services)) { ?>
        <?php foreach ($services as $row) { ?>
            <tr>
                <td><?php echo $row['service_id']; ?></td>
                <td><?php echo $row['service_description']; ?></td>
                <td><?php echo $row['office']; ?></td>
            </tr>
        <?php } ?>
    <?php } else { ?>
        <tr><td colspan="3">No data found</td></tr>
    <?php } ?>
</table>

</body>
</html>
