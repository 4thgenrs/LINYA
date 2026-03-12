<?php
// ../backend/face_login.php
header('Content-Type: application/json; charset=utf-8');

// Safer runtime: keep errors in logs, not in JSON output
error_reporting(E_ALL);
ini_set('display_errors', 0);

session_start();
include '../linya_db/config.php';

function req_id(): string {
    return date('YmdHis') . '-' . substr(bin2hex(random_bytes(4)), 0, 8);
}

function slog(string $rid, string $msg, array $ctx = []): void {
    $line = "[face_login][$rid] " . $msg;
    if (!empty($ctx)) $line .= " " . json_encode($ctx, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    error_log($line);
}

function respond(array $data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function normalize_safe_name(string $s): string {
    $s = trim($s);
    $s = preg_replace('/[^0-9a-zA-Z]+/', '_', $s);
    $s = strtolower((string)$s);
    // collapse underscores + trim
    $s = preg_replace('/_+/', '_', $s);
    $s = trim($s, '_');
    return $s;
}

$rid = req_id();

// Validate DB connection
if (!isset($conn) || !$conn instanceof mysqli) {
    slog($rid, "DB connection object not set");
    respond(['status' => 'error', 'code' => 'db_conn_missing', 'message' => 'Database connection not available', 'request_id' => $rid], 500);
}
if ($conn->connect_error) {
    slog($rid, "DB connection failed", ['err' => $conn->connect_error]);
    respond(['status' => 'error', 'code' => 'db_conn_failed', 'message' => 'Database connection failed', 'request_id' => $rid], 500);
}

// Expect JSON body or form POST with name or safe_name
$raw = file_get_contents('php://input');
$data = [];
if ($raw) {
    $d = json_decode($raw, true);
    if (is_array($d)) $data = $d;
}

// Accept client_id, safe_name or name in request body
$client_id = isset($data['client_id']) ? intval($data['client_id']) : (isset($_POST['client_id']) ? intval($_POST['client_id']) : 0);
$safe_name_in = trim($data['safe_name'] ?? $_POST['safe_name'] ?? '');
$name_in = trim($data['name'] ?? $_POST['name'] ?? '');

$sn_norm = $safe_name_in !== '' ? normalize_safe_name($safe_name_in) : '';
$name_norm_safe = $name_in !== '' ? normalize_safe_name($name_in) : '';

slog($rid, "Request received", [
    'has_client_id' => $client_id ? 'yes' : 'no',
    'safe_name_len' => strlen($safe_name_in),
    'safe_name_norm_len' => strlen($sn_norm),
    'name_len' => strlen($name_in),
    'name_norm_safe_len' => strlen($name_norm_safe),
]);

// search priority: client_id > safe_name > name
$client = null;

// 1) client_id
if ($client_id) {
    $stmt = $conn->prepare('SELECT client_id, name, safe_name, phone, email, face_path FROM clients WHERE client_id = ? LIMIT 1');
    if (!$stmt) {
        slog($rid, "Prepare failed (client_id)", ['err' => $conn->error]);
        respond(['status' => 'error', 'code' => 'db_prepare_failed', 'message' => 'Database error', 'request_id' => $rid], 500);
    }
    $stmt->bind_param('i', $client_id);
    if (!$stmt->execute()) {
        slog($rid, "Execute failed (client_id)", ['err' => $stmt->error]);
        $stmt->close();
        respond(['status' => 'error', 'code' => 'db_execute_failed', 'message' => 'Database error', 'request_id' => $rid], 500);
    }
    $res = $stmt->get_result();
    $client = $res ? $res->fetch_assoc() : null;
    $stmt->close();
    slog($rid, "Lookup by client_id", ['found' => $client ? 'yes' : 'no']);
}

// 2) safe_name (try raw + normalized)
if (!$client && $safe_name_in !== '') {
    $candidates = array_values(array_unique(array_filter([$safe_name_in, $sn_norm])));
    foreach ($candidates as $cand) {
        $stmt = $conn->prepare('SELECT client_id, name, safe_name, phone, email, face_path FROM clients WHERE safe_name = ? LIMIT 1');
        if (!$stmt) {
            slog($rid, "Prepare failed (safe_name)", ['err' => $conn->error]);
            respond(['status' => 'error', 'code' => 'db_prepare_failed', 'message' => 'Database error', 'request_id' => $rid], 500);
        }
        $stmt->bind_param('s', $cand);
        if (!$stmt->execute()) {
            slog($rid, "Execute failed (safe_name)", ['err' => $stmt->error]);
            $stmt->close();
            respond(['status' => 'error', 'code' => 'db_execute_failed', 'message' => 'Database error', 'request_id' => $rid], 500);
        }
        $res = $stmt->get_result();
        $client = $res ? $res->fetch_assoc() : null;
        $stmt->close();

        slog($rid, "Lookup by safe_name", ['candidate' => $cand, 'found' => $client ? 'yes' : 'no']);
        if ($client) break;
    }
}

// 3) name (try normalized safe + raw name)
if (!$client && $name_in !== '') {
    $derived_safe = $name_norm_safe;

    $stmt = $conn->prepare('SELECT client_id, name, safe_name, phone, email, face_path FROM clients WHERE safe_name = ? OR name = ? LIMIT 1');
    if (!$stmt) {
        slog($rid, "Prepare failed (name)", ['err' => $conn->error]);
        respond(['status' => 'error', 'code' => 'db_prepare_failed', 'message' => 'Database error', 'request_id' => $rid], 500);
    }
    $stmt->bind_param('ss', $derived_safe, $name_in);
    if (!$stmt->execute()) {
        slog($rid, "Execute failed (name)", ['err' => $stmt->error]);
        $stmt->close();
        respond(['status' => 'error', 'code' => 'db_execute_failed', 'message' => 'Database error', 'request_id' => $rid], 500);
    }
    $res = $stmt->get_result();
    $client = $res ? $res->fetch_assoc() : null;
    $stmt->close();

    slog($rid, "Lookup by name/derived_safe", ['derived_safe' => $derived_safe, 'found' => $client ? 'yes' : 'no']);
}

if (!$client) {
    slog($rid, "Client not found", [
        'client_id' => $client_id ?: null,
        'safe_name_in' => $safe_name_in !== '' ? '[provided]' : null,
        'safe_name_norm' => $sn_norm !== '' ? $sn_norm : null,
        'name_in' => $name_in !== '' ? '[provided]' : null,
        'name_norm_safe' => $name_norm_safe !== '' ? $name_norm_safe : null,
    ]);
    respond(['status' => 'error', 'code' => 'client_not_found', 'message' => 'Client not found', 'request_id' => $rid], 404);
}

// Before creating session, ensure client has no pending/ongoing/processing queue
$hasPending = false;
try {
    $resC = $conn->query("SHOW COLUMNS FROM queues LIKE 'customer_id'");
    $hasCustomerIdCol = ($resC && $resC->num_rows > 0);

    if ($hasCustomerIdCol) {
        $checkSt = $conn->prepare("SELECT queue_id FROM queues WHERE status IN ('pending','ongoing','processing') AND customer_id = ? LIMIT 1");
        if ($checkSt) {
            $checkSt->bind_param('i', $client['client_id']);
            $checkSt->execute();
            $r = $checkSt->get_result();
            if ($r && $r->num_rows > 0) $hasPending = true;
            $checkSt->close();
        }
    } else {
        $email = $client['email'] ?? '';
        $phone = $client['phone'] ?? '';
        $conds = [];
        $params = [];
        $types = '';
        if ($email !== '') { $conds[] = 'customer_email = ?'; $params[] = $email; $types .= 's'; }
        if ($phone !== '') { $conds[] = 'customer_phone = ?'; $params[] = $phone; $types .= 's'; }

        if (count($conds) > 0) {
            $sql = "SELECT queue_id FROM queues WHERE status IN ('pending','ongoing','processing') AND (" . implode(' OR ', $conds) . ") LIMIT 1";
            $checkSt = $conn->prepare($sql);
            if ($checkSt) {
                if (count($params) === 1) {
                    $checkSt->bind_param($types, $params[0]);
                } elseif (count($params) === 2) {
                    $checkSt->bind_param($types, $params[0], $params[1]);
                }
                $checkSt->execute();
                $r = $checkSt->get_result();
                if ($r && $r->num_rows > 0) $hasPending = true;
                $checkSt->close();
            }
        }
    }
} catch (Exception $e) {
    slog($rid, "Pending check failed", ['err' => $e->getMessage()]);
}

if ($hasPending) {
    slog($rid, "Client has pending queue, rejecting login", ['client_id' => $client['client_id']]);
    respond([
        'status' => 'error',
        'code' => 'duplicate_client_pending',
        'message' => 'You already have an existing queue (pending or in progress). Please wait until it is completed.',
        'request_id' => $rid
    ], 200);
}

// store client data in session
$_SESSION['client'] = [
    'client_id' => $client['client_id'],
    'name' => $client['name'],
    'safe_name' => $client['safe_name'],
    'phone' => $client['phone'],
    'email' => $client['email']
];

slog($rid, "Session created", ['client_id' => $client['client_id']]);

respond(['status' => 'ok', 'message' => 'Session created', 'client' => $_SESSION['client'], 'request_id' => $rid], 200);