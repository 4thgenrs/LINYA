<?php
include('../linya_db/config.php');

// --- AJAX uniqueness check -------------------------------------------------
// Usage: GET ../backend/accountsData.php?action=check&username=foo&name=Bar&exclude=3
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'check') {
    header('Content-Type: application/json; charset=utf-8');

    $username = trim((string)($_GET['username'] ?? ''));
    $name = trim((string)($_GET['name'] ?? ''));
    $exclude = intval($_GET['exclude'] ?? 0);

    $out = [
        'exists_username' => false,
        'exists_name' => false,
    ];

    // check username
    if ($username !== '') {
        if ($exclude > 0) {
            $s = $conn->prepare("SELECT user_id FROM accounts WHERE user_name = ? AND user_id != ? LIMIT 1");
            $s->bind_param("si", $username, $exclude);
        } else {
            $s = $conn->prepare("SELECT user_id FROM accounts WHERE user_name = ? LIMIT 1");
            $s->bind_param("s", $username);
        }
        if ($s) {
            $s->execute();
            $res = $s->get_result();
            $out['exists_username'] = ($res && $res->num_rows > 0);
            $s->close();
        }
    }

    // check name
    if ($name !== '') {
        if ($exclude > 0) {
            $s2 = $conn->prepare("SELECT user_id FROM accounts WHERE name = ? AND user_id != ? LIMIT 1");
            $s2->bind_param("si", $name, $exclude);
        } else {
            $s2 = $conn->prepare("SELECT user_id FROM accounts WHERE name = ? LIMIT 1");
            $s2->bind_param("s", $name);
        }
        if ($s2) {
            $s2->execute();
            $res2 = $s2->get_result();
            $out['exists_name'] = ($res2 && $res2->num_rows > 0);
            $s2->close();
        }
    }

    echo json_encode($out);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ----- HANDLE DELETE FIRST (fix) -----
    // When Delete button posts only delete_id we must handle it before
    // the usual add/update validation which expects other fields.
    if (isset($_POST['delete_id'])) {
        $delete_id = intval($_POST['delete_id']);
        if ($delete_id > 0) {
            deleteAccount($conn, $delete_id);
            header("Location: accounts.php?success=deleted");
            exit;
        }
    }
    // ----- end delete handling -----

    // Get and sanitize form inputs
    $user_id = intval($_POST['user_id'] ?? 0);
    $account_username = trim($_POST['account_username'] ?? '');
    $account_password = trim($_POST['account_password'] ?? '');
    $account_name = trim($_POST['account_name'] ?? '');
    $role_id = intval($_POST['role_id'] ?? 0);
    $office_id = intval($_POST['office_id'] ?? 0);

    // helper: check duplicates
    function accountUsernameExists($conn, $username, $excludeId = 0) {
        $sql = "SELECT user_id FROM accounts WHERE user_name = ? LIMIT 1";
        if ($excludeId > 0) {
            $sql = "SELECT user_id FROM accounts WHERE user_name = ? AND user_id != ? LIMIT 1";
        }
        $stmt = $conn->prepare($sql);
        if (!$stmt) return false;
        if ($excludeId > 0) {
            $stmt->bind_param("si", $username, $excludeId);
        } else {
            $stmt->bind_param("s", $username);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        $exists = $res && $res->num_rows > 0;
        $stmt->close();
        return $exists;
    }

    function accountNameExists($conn, $name, $excludeId = 0) {
        $sql = "SELECT user_id FROM accounts WHERE name = ? LIMIT 1";
        if ($excludeId > 0) {
            $sql = "SELECT user_id FROM accounts WHERE name = ? AND user_id != ? LIMIT 1";
        }
        $stmt = $conn->prepare($sql);
        if (!$stmt) return false;
        if ($excludeId > 0) {
            $stmt->bind_param("si", $name, $excludeId);
        } else {
            $stmt->bind_param("s", $name);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        $exists = $res && $res->num_rows > 0;
        $stmt->close();
        return $exists;
    }

    // validation: required fields
    if ($account_username === '' || $account_password === '' || $account_name === '' || $role_id <= 0 || $office_id <= 0) {
        header("Location: accounts.php?error=missing_fields");
        exit;
    }

    // ✅ UPDATE account (if user_id exists)
    if ($user_id > 0) {
        // check duplicates excluding current record
        if (accountUsernameExists($conn, $account_username, $user_id)) {
            header("Location: accounts.php?error=duplicate_username");
            exit;
        }
        if (accountNameExists($conn, $account_name, $user_id)) {
            header("Location: accounts.php?error=duplicate_name");
            exit;
        }

        updateAccount($conn, $user_id, $account_username, $account_password, $account_name, $role_id, $office_id);
        header("Location: accounts.php?success=updated");
        exit;
    }

    // ✅ ADD account (if no user_id)
    if ($user_id === 0) {
        // check duplicates
        if (accountUsernameExists($conn, $account_username)) {
            header("Location: accounts.php?error=duplicate_username");
            exit;
        }
        if (accountNameExists($conn, $account_name)) {
            header("Location: accounts.php?error=duplicate_name");
            exit;
        }

        addAccount($conn, $account_username, $account_password, $account_name, $role_id, $office_id);
        header("Location: accounts.php?success=added");
        exit;
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