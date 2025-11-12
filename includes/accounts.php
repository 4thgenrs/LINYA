<?php
include('../backend/accountsData.php');
include('../backend/servicesData.php');
include('../linya_db/config.php');

$accounts = getAccountsData($conn);
$offices = getOfficeData($conn);
$roles = getRolesData($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Accounts Table</title>
    <link rel="stylesheet"  href="../css/accounts.css">
</head>
<body>
    <h2>Accounts List</h2>

    <form id="accountsForm" method="POST">
        <input type="hidden" name="user_id" id="user_id" value="">
        <input type="text" name="account_username" id="account_username" placeholder="Enter username" required>
        <input type="text" name="account_password" id="account_password" placeholder="Enter password" required>
        <input type="text" name="account_name" id="account_name" placeholder="Enter name" required>
         <select name="role_id" id="role_id" required>
        <option value="">Select User Type</option>
        <?php foreach ($roles as $r) { ?>
            <option value="<?php echo $r['role_id']; ?>"><?php echo $r['role_name']; ?></option>
        <?php } ?>
        </select>
         <select name="office_id" id="office_id" required>
        <option value="">Select Office</option>
        <?php foreach ($offices as $o) { ?>
            <option value="<?php echo $o['office_id']; ?>"><?php echo $o['office_description']; ?></option>
        <?php } ?>
        </select>   
        <input type="submit" name="add_office" id="submitBtn" value="Add Account"> 
    </form>
    <table>
        <thead>
            <tr>
                <th>User ID</th>
                <th>User Name</th>
                <th>Password</th>
                <th>Name</th>
                <th>User Type</th>
                <th>Office</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($accounts)) : $counter = 1;?>
                
                <?php foreach ($accounts as $acc) : ?>
                    <tr
                        data-id="<?php echo htmlspecialchars($acc['user_id']); ?>"
                        data-username="<?php echo htmlspecialchars($acc['user_name']); ?>"
                        data-password="<?php echo htmlspecialchars($acc['password']); ?>"
                        data-name="<?php echo htmlspecialchars($acc['name']); ?>"
                        data-role="<?php echo htmlspecialchars($acc['user_type']); ?>"
                        data-office="<?php echo htmlspecialchars($acc['office']); ?>"
                    >
                        <td><?php echo $counter++; ?></td>
                        <td><?php echo htmlspecialchars($acc['user_name']); ?></td>
                        <td><?php echo htmlspecialchars($acc['password']); ?></td>
                        <td><?php echo htmlspecialchars($acc['name']); ?></td>
                        <td><?php echo htmlspecialchars($acc['role_name']); ?></td>
                        <td><?php echo htmlspecialchars($acc['office_description']); ?></td>
                        
                        <!-- Action buttons -->
                        <td>
                            <button type="button" class="editBtn">Edit</button>

                            <!-- Delete button -->
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="delete_id" value="<?php echo $acc['user_id']; ?>">
                                <input type="submit" name="delete_account" value="Delete" onclick="return confirm('Are you sure you want to delete this account?');">
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="7">No data found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <script src="../js/accounts.js"></script>
    
</body>
</html>