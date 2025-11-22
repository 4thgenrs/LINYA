<?php
/**
 * Admin endpoint: delete a client and their face recognition data.
 * 
 * Accepts POST JSON: { "client_id": 123 }
 * 
 * Process:
 * 1. Look up client record (safe_name, face_path)
 * 2. Call Python API to delete GCS data
 * 3. On success, delete DB record (transaction)
 * 4. Always return valid JSON, never HTML
 */

// Suppress all PHP errors from outputting HTML
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

// Always set JSON header first
header('Content-Type: application/json; charset=utf-8');

// Temporary debug logging
$debug_log = __DIR__ . '/delete_debug.log';
file_put_contents($debug_log, date('Y-m-d H:i:s') . " - Script started\n", FILE_APPEND);

try {
    // Include database config safely
    if (!file_exists('../linya_db/config.php')) {
        file_put_contents($debug_log, "DB config file not found\n", FILE_APPEND);
        throw new Exception('Database configuration file not found');
    }
    include '../linya_db/config.php';
    file_put_contents($debug_log, "DB config loaded\n", FILE_APPEND);
    
    // Include Python API config for PYTHON_API_BASE constant
    if (file_exists('../config.php')) {
        include '../config.php';
        file_put_contents($debug_log, "Python API config loaded\n", FILE_APPEND);
    }
    
    session_start();
    file_put_contents($debug_log, "Session started\n", FILE_APPEND);

    // Require admin access
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || intval($_SESSION['user_type']) !== 1) {
        http_response_code(403);
        echo json_encode(['status'=>'error','message'=>'Unauthorized']);
        exit;
    }

    // Parse JSON request
    $raw = @file_get_contents('php://input');
    file_put_contents($debug_log, "Raw input: " . $raw . "\n", FILE_APPEND);
    if ($raw === false) {
        throw new Exception('Failed to read request body');
    }
    
    $data = json_decode($raw, true);
    file_put_contents($debug_log, "Decoded data: " . print_r($data, true) . "\n", FILE_APPEND);
    if (!is_array($data) || !isset($data['client_id'])) {
        http_response_code(400);
        echo json_encode(['status'=>'error','message'=>'Missing client_id']);
        exit;
    }

    $client_id = intval($data['client_id']);
    if ($client_id <= 0) {
        http_response_code(400);
        echo json_encode(['status'=>'error','message'=>'Invalid client_id']);
        exit;
    }

    // Verify DB connection
    if (!isset($conn) || !$conn) {
        throw new Exception('Database connection not available');
    }

    // Fetch client record
    $stmt = $conn->prepare('SELECT safe_name, face_path FROM clients WHERE client_id = ?');
    if (!$stmt) {
        error_log("DB prepare failed: " . $conn->error);
        http_response_code(500);
        echo json_encode(['status'=>'error','message'=>'DB prepare failed']);
        exit;
    }

    $stmt->bind_param('i', $client_id);
    if (!$stmt->execute()) {
        error_log("DB execute failed: " . $stmt->error);
        http_response_code(500);
        echo json_encode(['status'=>'error','message'=>'DB execute failed']);
        $stmt->close();
        exit;
    }

    $result = $stmt->get_result();
    $client = $result->fetch_assoc();
    $stmt->close();

    if (!$client) {
        http_response_code(404);
        echo json_encode(['status'=>'error','message'=>'Client not found']);
        exit;
    }

    $safe_name = isset($client['safe_name']) ? $client['safe_name'] : '';
    $face_path = isset($client['face_path']) ? $client['face_path'] : '';

    file_put_contents($debug_log, "Client found: safe_name=$safe_name, face_path=$face_path\n", FILE_APPEND);

    // Call Python API to delete GCS data
    // Priority: 1) Environment var, 2) PYTHON_API_BASE constant, 3) Default placeholder
    $python_api_url = getenv('PYTHON_API_URL');
    if (!$python_api_url && defined('PYTHON_API_BASE')) {
        $python_api_url = PYTHON_API_BASE;
    }
    if (!$python_api_url) {
        $python_api_url = 'https://face-recognition-api-546052063562.asia-southeast1.run.app';
    }
    
    file_put_contents($debug_log, "Python API URL: $python_api_url\n", FILE_APPEND);
    
    $admin_key = getenv('ADMIN_KEY');
    
    $delete_url = rtrim($python_api_url, '/') . '/admin/delete_user';
    file_put_contents($debug_log, "Delete URL: $delete_url\n", FILE_APPEND);

    // Prepare request payload
    $py_payload = [];
    if ($face_path && strpos($face_path, 'gs://') === 0) {
        $py_payload['folder'] = $face_path;
    } elseif ($safe_name) {
        $py_payload['safe_name'] = $safe_name;
    } else {
        http_response_code(400);
        echo json_encode(['status'=>'error','message'=>'No safe_name or face_path available']);
        exit;
    }
    
    file_put_contents($debug_log, "Payload: " . json_encode($py_payload) . "\n", FILE_APPEND);

    // Build cURL headers
    $headers = ['Content-Type: application/json'];
    if ($admin_key && trim($admin_key) !== '') {
        $headers[] = 'X-ADMIN-KEY: ' . $admin_key;
    }

    // Make HTTP request to Python API
    $ch = curl_init($delete_url);
    if (!$ch) {
        throw new Exception('Failed to initialize cURL');
    }
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($py_payload));
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_FAILONERROR, false);

    $py_response = curl_exec($ch);
    $py_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $py_error = curl_error($ch);
    curl_close($ch);

    file_put_contents($debug_log, "cURL response code: $py_http_code\n", FILE_APPEND);
    file_put_contents($debug_log, "cURL error: $py_error\n", FILE_APPEND);
    file_put_contents($debug_log, "Python response: $py_response\n", FILE_APPEND);

    // Handle cURL failure
    if ($py_response === false) {
        error_log("cURL error: " . $py_error);
        http_response_code(500);
        echo json_encode([
            'status'=>'error',
            'message'=>'Failed to connect to Python API: ' . $py_error
        ]);
        exit;
    }

    // Handle non-200 HTTP response
    if ($py_http_code !== 200) {
        $py_json = json_decode($py_response, true);
        $py_message = (is_array($py_json) && isset($py_json['message'])) ? $py_json['message'] : 'Unknown error';
        error_log("Python API returned HTTP $py_http_code: " . $py_response);
        http_response_code(500);
        echo json_encode([
            'status'=>'error',
            'message'=>'Failed to delete GCS data'
        ]);
        exit;
    }

    // Parse Python API response
    $py_json = json_decode($py_response, true);
    if (!is_array($py_json)) {
        error_log("Python API returned invalid JSON: " . $py_response);
        http_response_code(500);
        echo json_encode([
            'status'=>'error',
            'message'=>'Python API returned invalid response'
        ]);
        exit;
    }

    // Check Python API status
    if (!isset($py_json['status']) || $py_json['status'] !== 'ok') {
        $py_message = isset($py_json['message']) ? $py_json['message'] : 'Unknown error';
        error_log("Python API reported failure: " . $py_message);
        http_response_code(500);
        echo json_encode([
            'status'=>'error',
            'message'=>'Python API reported failure'
        ]);
        exit;
    }

    // Python deletion succeeded - now delete DB record in transaction
    $conn->begin_transaction();
    
    try {
        $stmt = $conn->prepare('DELETE FROM clients WHERE client_id = ?');
        if (!$stmt) {
            throw new Exception('DB prepare failed for deletion: ' . $conn->error);
        }

        $stmt->bind_param('i', $client_id);
        if (!$stmt->execute()) {
            throw new Exception('DB delete failed: ' . $stmt->error);
        }

        $affected = $stmt->affected_rows;
        $stmt->close();

        $conn->commit();

        if ($affected > 0) {
            echo json_encode(['status'=>'ok', 'message'=>'Client and face data deleted successfully']);
        } else {
            echo json_encode(['status'=>'error','message'=>'DB record not found']);
        }
        
    } catch (Exception $db_ex) {
        $conn->rollback();
        error_log("DB transaction error: " . $db_ex->getMessage());
        http_response_code(500);
        echo json_encode(['status'=>'error','message'=>'DB delete failed']);
        exit;
    }

} catch (Exception $ex) {
    $error_details = "Exception: " . $ex->getMessage() . "\nTrace: " . $ex->getTraceAsString();
    error_log("Unexpected error in clients_delete.php: " . $ex->getMessage());
    file_put_contents($debug_log, $error_details . "\n", FILE_APPEND);
    http_response_code(500);
    echo json_encode([
        'status'=>'error',
        'message'=>'Internal server error',
        'debug'=>$ex->getMessage()
    ]);
}

exit;
?>
