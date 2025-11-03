
<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/queueData.php'; // queueData.php includes config and defines helper functions

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'state';
$window = isset($_REQUEST['window']) ? intval($_REQUEST['window']) : 1;
$office = isset($_REQUEST['office']) ? intval($_REQUEST['office']) : 1;

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