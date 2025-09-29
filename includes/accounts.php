<?php
include('../backend/accountsData.php');
include('../linya_db/config.php');

$accounts = getAccountsData($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Accounts Table</title>
    <link rel="stylesheet"  href="../css/accounts.css">
</head>
<body>
    <h2>Accounts List</h2>
    <table>
        <thead>
            <tr>
                <th>User ID</th>
                <th>User Name</th>
                <th>Password</th>
                <th>Name</th>
                <th>User Type</th>
                <th>Office</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($accounts)) : ?>
                <?php foreach ($accounts as $acc) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($acc['user_id']); ?></td>
                        <td><?php echo htmlspecialchars($acc['user_name']); ?></td>
                        <td><?php echo htmlspecialchars($acc['password']); ?></td>
                        <td><?php echo htmlspecialchars($acc['name']); ?></td>
                        <td><?php echo htmlspecialchars($acc['role_name']); ?></td>
                        <td><?php echo htmlspecialchars($acc['office_description']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="6">No data found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>