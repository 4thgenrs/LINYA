<?php
header('Content-Type: application/json; charset=utf-8');
include '../linya_db/config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $action = $_GET['action'] ?? '';

    if ($action === 'list_offices') {
        $rows = [];
        $res = $conn->query("SELECT office_id, office_description FROM offices ORDER BY office_description");
        while ($r = $res->fetch_assoc()) $rows[] = $r;
        echo json_encode(['success'=>true,'offices'=>$rows]);
        exit;
    }

    if ($action === 'list_staffs') {
        $officeId = intval($_GET['office_id'] ?? 0);
        $rows = [];
        $stmt = $conn->prepare("SELECT user_id, name, user_name FROM accounts WHERE user_type = 2 AND office = ? ORDER BY name");
        $stmt->bind_param("i",$officeId);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($r = $res->fetch_assoc()) $rows[] = $r;
        $stmt->close();
        echo json_encode(['success'=>true,'staffs'=>$rows]);
        exit;
    }

    // new: get next available window number for office
    if ($action === 'get_next_window_no') {
        $officeId = intval($_GET['office_id'] ?? 0);
        if (!$officeId) { echo json_encode(['success'=>false,'error'=>'Missing office_id']); exit; }
        $stmt = $conn->prepare("SELECT COALESCE(MAX(window_no),0)+1 AS next_no FROM office_windows WHERE office_id = ?");
        $stmt->bind_param("i",$officeId);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        echo json_encode(['success'=>true,'next_no'=>intval($res['next_no'] ?? 1)]);
        exit;
    }

    if ($action === 'list_windows') {
        $rows = [];
        $sql = "SELECT ow.window_id, ow.office_id, ow.window_no, ow.assigned_staff_id, ow.enabled, o.office_description,
                       a.user_id AS staff_user_id, a.name AS staff_name, a.user_name AS staff_username
                FROM office_windows ow
                JOIN offices o ON ow.office_id = o.office_id
                LEFT JOIN accounts a ON ow.assigned_staff_id = a.user_id
                ORDER BY o.office_description, ow.window_no";
        $res = $conn->query($sql);
        if ($res === false) {
            // return DB error to frontend for easier debugging
            http_response_code(500);
            echo json_encode(['success'=>false,'error'=>'DB error: ' . $conn->error]);
            exit;
        }
        while ($r = $res->fetch_assoc()) $rows[] = $r;
        echo json_encode(['success'=>true,'windows'=>$rows]);
        exit;
    }

    echo json_encode(['success'=>false,'error'=>'Unknown action']);
    exit;
}

if ($method === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_window') {
        $officeId = intval($_POST['office_id'] ?? 0);
        $windowNo = intval($_POST['window_no'] ?? 0); // 0 means "auto"
        $staffId = isset($_POST['staff_id']) && $_POST['staff_id'] !== '' ? intval($_POST['staff_id']) : null;

        if (!$officeId) {
            http_response_code(400);
            echo json_encode(['success'=>false,'error'=>'Missing office']);
            exit;
        }

        // if windowNo == 0, compute next available
        if ($windowNo <= 0) {
            $stmt = $conn->prepare("SELECT COALESCE(MAX(window_no),0)+1 AS next_no FROM office_windows WHERE office_id = ?");
            $stmt->bind_param("i",$officeId);
            $stmt->execute();
            $windowNo = intval($stmt->get_result()->fetch_assoc()['next_no'] ?? 1);
            $stmt->close();
        } else {
            // check duplicate window number for same office
            $stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM office_windows WHERE office_id = ? AND window_no = ?");
            $stmt->bind_param("ii", $officeId, $windowNo);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if (intval($res['cnt'] ?? 0) > 0) {
                http_response_code(409);
                echo json_encode(['success'=>false,'error'=>'Window number already exists for this office']);
                exit;
            }
        }

        // validate staff belongs to office if provided
        if ($staffId) {
            $stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM accounts WHERE user_type = 2 AND user_id = ? AND office = ?");
            $stmt->bind_param("ii", $staffId, $officeId);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if (!$res || intval($res['cnt']) === 0) {
                http_response_code(400);
                echo json_encode(['success'=>false,'error'=>'Selected staff not valid for office']);
                exit;
            }

            // ensure staff not already assigned to another window
            $stmt = $conn->prepare("SELECT window_id FROM office_windows WHERE assigned_staff_id = ?");
            $stmt->bind_param("i", $staffId);
            $stmt->execute();
            $already = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if ($already) {
                http_response_code(409);
                echo json_encode(['success'=>false,'error'=>'This staff is already assigned to another window (unassign first)']);
                exit;
            }
        }

        try {
            if ($staffId === null) {
                $stmt = $conn->prepare("INSERT INTO office_windows (office_id, window_no) VALUES (?, ?)");
                $stmt->bind_param("ii", $officeId, $windowNo);
            } else {
                $stmt = $conn->prepare("INSERT INTO office_windows (office_id, window_no, assigned_staff_id) VALUES (?, ?, ?)");
                $stmt->bind_param("iii", $officeId, $windowNo, $staffId);
            }
            $ok = $stmt->execute();
            if (!$ok) {
                http_response_code(500);
                echo json_encode(['success'=>false,'error'=>$stmt->error]);
                $stmt->close();
                exit;
            }
            $id = $conn->insert_id;
            $stmt->close();
            echo json_encode(['success'=>true,'window_id'=>$id,'window_no'=>$windowNo]);
            exit;
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
            exit;
        }
    }

    if ($action === 'update_window_no') {
        $windowId = intval($_POST['window_id'] ?? 0);
        $newNo = intval($_POST['window_no'] ?? 0);
        if (!$windowId || $newNo <= 0) { http_response_code(400); echo json_encode(['success'=>false,'error'=>'Missing params']); exit; }

        // get office_id
        $stmt = $conn->prepare("SELECT office_id FROM office_windows WHERE window_id = ?");
        $stmt->bind_param("i",$windowId);
        $stmt->execute();
        $info = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$info) { http_response_code(404); echo json_encode(['success'=>false,'error'=>'Window not found']); exit; }
        $officeId = intval($info['office_id']);

        // check duplicate number (exclude this window)
        $stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM office_windows WHERE office_id = ? AND window_no = ? AND window_id != ?");
        $stmt->bind_param("iii",$officeId,$newNo,$windowId);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (intval($res['cnt'] ?? 0) > 0) { http_response_code(409); echo json_encode(['success'=>false,'error'=>'Window number already in use for this office']); exit; }

        $stmt = $conn->prepare("UPDATE office_windows SET window_no = ? WHERE window_id = ?");
        $stmt->bind_param("ii",$newNo,$windowId);
        $stmt->execute();
        $ok = $stmt->affected_rows >= 0;
        $stmt->close();
        echo json_encode(['success'=>$ok,'window_no'=>$newNo]);
        exit;
    }

    if ($action === 'assign_staff') {
        $windowId = intval($_POST['window_id'] ?? 0);
        $staffId = isset($_POST['staff_id']) && $_POST['staff_id'] !== '' ? intval($_POST['staff_id']) : null;
        if (!$windowId) { http_response_code(400); echo json_encode(['success'=>false,'error'=>'Missing window']); exit; }

        // validate window exists and get office
        $stmt = $conn->prepare("SELECT office_id FROM office_windows WHERE window_id = ?");
        $stmt->bind_param("i", $windowId);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$res) { http_response_code(404); echo json_encode(['success'=>false,'error'=>'Window not found']); exit; }
        $officeId = intval($res['office_id']);

        if ($staffId) {
            $stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM accounts WHERE user_type = 2 AND user_id = ? AND office = ?");
            $stmt->bind_param("ii", $staffId, $officeId);
            $stmt->execute();
            $cnt = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if (!$cnt || intval($cnt['cnt']) === 0) { http_response_code(400); echo json_encode(['success'=>false,'error'=>'Staff not valid for this office']); exit; }

            // ensure this staff is not assigned to another window (exclude this window)
            $stmt = $conn->prepare("SELECT window_id FROM office_windows WHERE assigned_staff_id = ? AND window_id != ?");
            $stmt->bind_param("ii", $staffId, $windowId);
            $stmt->execute();
            $other = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if ($other) { http_response_code(409); echo json_encode(['success'=>false,'error'=>'Staff already assigned to another window (unassign first)']); exit; }
        }

        if ($staffId === null) {
            $stmt = $conn->prepare("UPDATE office_windows SET assigned_staff_id = NULL WHERE window_id = ?");
            $stmt->bind_param("i", $windowId);
        } else {
            $stmt = $conn->prepare("UPDATE office_windows SET assigned_staff_id = ? WHERE window_id = ?");
            $stmt->bind_param("ii", $staffId, $windowId);
        }
        $stmt->execute();
        $ok = $stmt->affected_rows >= 0;
        $stmt->close();
        echo json_encode(['success'=>$ok]);
        exit;
    }

    if ($action === 'delete_window') {
        $windowId = intval($_POST['window_id'] ?? 0);
        if (!$windowId) { http_response_code(400); echo json_encode(['success'=>false,'error'=>'Missing window']); exit; }
        $stmt = $conn->prepare("DELETE FROM office_windows WHERE window_id = ?");
        $stmt->bind_param("i",$windowId);
        $stmt->execute();
        $ok = $stmt->affected_rows > 0;
        $stmt->close();
        echo json_encode(['success'=>$ok]);
        exit;
    }

    if ($action === 'toggle_window') {
        $windowId = intval($_POST['window_id'] ?? 0);
        $enabled = isset($_POST['enabled']) ? intval($_POST['enabled']) : 0;
        if (!$windowId) { http_response_code(400); echo json_encode(['success'=>false,'error'=>'Missing window']); exit; }
        $stmt = $conn->prepare("UPDATE office_windows SET enabled = ? WHERE window_id = ?");
        $stmt->bind_param("ii", $enabled, $windowId);
        $stmt->execute();
        $ok = $stmt->affected_rows >= 0;
        $stmt->close();
        echo json_encode(['success'=>$ok]);
        exit;
    }

    echo json_encode(['success'=>false,'error'=>'Unknown action']);
    exit;
}

http_response_code(405);
echo json_encode(['success'=>false,'error'=>'Method not allowed']);
?>