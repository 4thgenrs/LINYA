<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/queueData.php'; // queueData.php includes config and defines helper functions

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'state';

// default request values (from query)
$window = isset($_REQUEST['window']) ? intval($_REQUEST['window']) : 0;
$office = isset($_REQUEST['office']) ? intval($_REQUEST['office']) : 0;

// if a staff session exists prefer session's office/window
if (isset($_SESSION['user_id']) && isset($_SESSION['user_type']) && intval($_SESSION['user_type']) === 2) {
    $office = isset($_SESSION['office_id']) ? intval($_SESSION['office_id']) : $office;
    // prefer window_no if provided, otherwise window_id
    $window = isset($_SESSION['window_no']) && intval($_SESSION['window_no']) > 0 ? intval($_SESSION['window_no']) : (isset($_SESSION['window_id']) ? intval($_SESSION['window_id']) : $window);
    // note: getNextLine expects a numeric window id/identifier; queueData functions use $window as passed
}

try {
    if ($action === 'next') {
        $res = getNextLine($conn, $office, $window);
        if ($res) {
            echo json_encode(['success' => true, 'data' => $res]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No waiting queue item to call.']);
        }
        exit;
    }

    if ($action === 'finish') {
        $res = finishCurrentQueue($conn, $window);
        if ($res) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to finish the current queue.']);
        }
        exit;
    }

    if ($action === 'break') {
        $isBreak = isset($_REQUEST['isBreak']) ? filter_var($_REQUEST['isBreak'], FILTER_VALIDATE_BOOLEAN) : true;
        $res = setWindowBreak($conn, $window, $isBreak);
        if ($res) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update break status.']);
        }
        exit;
    }

    // Default: state — return queue list and on-air for given office/window
    $queue_list = getOfficeQueue($conn, $office);
    $on_air = displayOnAir($conn, $window);

    echo json_encode([
        'success' => true,
        'data' => [
            'queue_list' => $queue_list,
            'on_air' => $on_air,
            'office' => $office,
            'window' => $window
        ]
    ]);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
    exit;
}