<?php
include('../linya_db/config.php');


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get and sanitize form inputs
    $user_id = intval($_POST['user_id'] ?? 0);
    $account_username = trim($_POST['account_username'] ?? '');
    $account_password = trim($_POST['account_password'] ?? '');
    $account_name = trim($_POST['account_name'] ?? '');
    $role_id = intval($_POST['role_id'] ?? 0);
    $office_id = intval($_POST['office_id'] ?? 0);

    // ✅ UPDATE account (if user_id exists)
    if ($user_id > 0) {
        if (
            $account_username !== '' &&
            $account_password !== '' &&
            $account_name !== '' &&
            $role_id > 0 &&
            $office_id > 0
        ) {
            updateAccount($conn, $user_id, $account_username, $account_password, $account_name, $role_id, $office_id);
            header("Location: accounts.php");
            exit;
        }
    }

    // ✅ ADD account (if no user_id)
    if ($user_id === 0) {
        if (
            $account_username !== '' &&
            $account_password !== '' &&
            $account_name !== '' &&
            $role_id > 0 &&
            $office_id > 0
        ) {
            addAccount($conn, $account_username, $account_password, $account_name, $role_id, $office_id);
            header("Location: accounts.php");
            exit;
        }
    }

    // ✅ DELETE account
    if (isset($_POST['delete_id'])) {
        $delete_id = intval($_POST['delete_id']);
        if ($delete_id > 0) {
            deleteAccount($conn, $delete_id);
            header("Location: accounts.php");
            exit;
        }
    }
}


function getAccountsData($conn) {
    $data = [];
    $sql = "SELECT 
                A.user_id, 
                A.user_name, 
                A.password, 
                A.name, 
                A.user_type, 
                A.office, 
                R.role_name, 
                O.office_description 
            FROM accounts A 
            INNER JOIN offices O ON A.office = O.office_id 
            INNER JOIN roles R ON A.user_type = R.role_id";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    return $data;
}

function getRolesData($conn) {
    $sql = "SELECT role_id, role_name FROM roles";
    $result = $conn->query($sql);

    $data = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    return $data;
}

function addAccount($conn, $account_username, $account_password, $account_name, $role_id, $office_id) {
    $stmt = $conn->prepare("INSERT INTO accounts (user_name, password, name, user_type, office) VALUES (?, ?, ?, ?, ?)");
    // 3 strings + 2 integers → use "sssii"
    $stmt->bind_param("sssii", $account_username, $account_password, $account_name, $role_id, $office_id);
    return $stmt->execute();
}

function updateAccount($conn, $user_id, $account_username, $account_password, $account_name, $role_id, $office_id) {
    $stmt = $conn->prepare("UPDATE accounts SET user_name = ?, password = ?, name = ?, user_type = ?, office = ? WHERE user_id = ?");
    $stmt->bind_param("sssiii", $account_username, $account_password, $account_name, $role_id, $office_id, $user_id);
    return $stmt->execute();
}

function deleteAccount($conn, $user_id) {
    $stmt = $conn->prepare("DELETE FROM accounts WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    return $stmt->execute();
}