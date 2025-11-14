<?php
include('../backend/accountsData.php');
include('../backend/servicesData.php');
include('../linya_db/config.php');



$accounts = getAccountsData($conn);
$offices = getOfficeData($conn);
$roles = getRolesData($conn);

// server-side messages (from redirect)
$message = '';
if (isset($_GET['error'])) {
    $err = $_GET['error'];
    if ($err === 'duplicate_username') $message = 'Username already exists. Please choose another.';
    if ($err === 'duplicate_name') $message = 'Name already exists. Please choose another.';
    if ($err === 'missing_fields') $message = 'Please fill all required fields.';
}
if (isset($_GET['success'])) {
    $s = $_GET['success'];
    if ($s === 'added') $message = 'Account added successfully.';
    if ($s === 'updated') $message = 'Account updated successfully.';
    if ($s === 'deleted') $message = 'Account deleted.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Accounts Management</title>

    <!-- Bootstrap, FontAwesome & DataTables -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <style>
        /* Match example UI theme */
        body {
            background-color: #f8f9fa;
        }
        #accountsTable thead th {
            background-color: #d8efe1 !important;
            color: #0b3b21;
            font-weight: 700;
            text-align: center;
        }
        #accountsTable tbody td {
            text-align: center;
            vertical-align: middle;
        }
        .dataTables_wrapper .dataTables_filter input {
            width: 250px;
        }
    </style>
</head>

<body>
    <h2>Accounts List</h2>

    <?php if ($message): ?>
      <div id="serverMessage" style="max-width:800px;margin:8px auto;padding:10px;background:#fff9c4;border:1px solid #ffe58a;border-radius:6px;text-align:center;">
        <?php echo htmlspecialchars($message); ?>
      </div>
    <?php endif; ?>

    <form id="accountsForm" method="POST">
        <input type="hidden" name="user_id" id="user_id" value="">
        <div style="max-width:800px;margin:12px auto;display:flex;gap:8px;flex-wrap:wrap">
            <div style="flex:1;min-width:200px">
                <input type="text" name="account_username" id="account_username" placeholder="Enter username" required>
                <div id="usernameFeedback" style="color:#b00020;font-size:13px;display:none;margin-top:4px"></div>
            </div>

            <div style="flex:1;min-width:200px">
                <input type="text" name="account_password" id="account_password" placeholder="Enter password" required>
            </div>

            <div style="flex:1;min-width:200px">
                <input type="text" name="account_name" id="account_name" placeholder="Enter name" required>
                <div id="nameFeedback" style="color:#b00020;font-size:13px;display:none;margin-top:4px"></div>
            </div>

            <div style="min-width:160px">
                <select name="role_id" id="role_id" required>
                  <option value="">Select User Type</option>
                  <?php foreach ($roles as $r) { ?>
                    <option value="<?php echo $r['role_id']; ?>"><?php echo $r['role_name']; ?></option>
                  <?php } ?>
                </select>
            </div>

            <div style="min-width:160px">
                <select name="office_id" id="office_id" required>
                  <option value="">Select Office</option>
                  <?php foreach ($offices as $o) { ?>
                    <option value="<?php echo $o['office_id']; ?>"><?php echo $o['office_description']; ?></option>
                  <?php } ?>
                </select>
            </div>

            <div style="min-width:160px;align-self:end">
                <input type="submit" name="add_office" id="submitBtn" value="Add Account">
            </div>
        </div>
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
