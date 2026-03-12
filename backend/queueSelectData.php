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
    // prefer the resolved PK first (window_id), then fall back to window_no (legacy)
    $window = (isset($_SESSION['window_id']) && intval($_SESSION['window_id']) > 0)
        ? intval($_SESSION['window_id'])
        : (isset($_SESSION['window_no']) ? intval($_SESSION['window_no']) : $window);
    // note: getNextLine expects a numeric window id/identifier; queueData functions use $window as passed
}

// Server-side permission: staff may only use their assigned window
if (isset($_SESSION['user_id']) && isset($_SESSION['user_type']) && intval($_SESSION['user_type']) === 2) {
    $staffId = intval($_SESSION['user_id']);
    // resolve assigned window for staff
    $aw = $conn->prepare("SELECT window_id FROM office_windows WHERE assigned_staff_id = ? LIMIT 1");
    if ($aw) {
        $aw->bind_param('i', $staffId);
        $aw->execute();
        $awr = $aw->get_result();
        $assigned = $awr ? $awr->fetch_assoc() : null;
        $aw->close();

        if (!$assigned) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Forbidden: you are not assigned to any window']);
            exit;
        }

        // the API calls must refer to the staff's actual assigned window
        if (intval($assigned['window_id']) !== intval($window)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Forbidden: window mismatch']);
            exit;
        }
    }
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

    // Mark the current on-air queue as 'processing' when staff clicks Start Processing
    if ($action === 'start') {
        $onAir = displayOnAir($conn, $window);
        if (!$onAir || empty($onAir['queue_id'])) {
            echo json_encode(['success' => false, 'message' => 'No active queue on this window to start processing.']);
            exit;
        }
        $queue_id = intval($onAir['queue_id']);
        $u = $conn->prepare("UPDATE queues SET status = 'processing' WHERE queue_id = ?");
        if ($u) {
            $u->bind_param('i', $queue_id);
            if ($u->execute()) {
                $u->close();
                echo json_encode(['success' => true]);
                exit;
            }
            $u->close();
        }
        echo json_encode(['success' => false, 'message' => 'Failed to mark queue as processing.']);
        exit;
    }

    if ($action === 'details') {
        $queueParam = isset($_REQUEST['queue']) ? $_REQUEST['queue'] : null;
        if (!$queueParam) {
            echo json_encode(['success' => false, 'message' => 'Missing queue identifier']);
            exit;
        }

        $queueRow = null;
        // try by queue_id when numeric
        if (is_numeric($queueParam) && intval($queueParam) > 0) {
            $qid = intval($queueParam);
            $qSt = $conn->prepare("SELECT * FROM queues WHERE queue_id = ? LIMIT 1");
            if ($qSt) {
                $qSt->bind_param('i', $qid);
                $qSt->execute();
                $qRes = $qSt->get_result();
                $queueRow = $qRes ? $qRes->fetch_assoc() : null;
                $qSt->close();
            }
        }

        // fallback: try by queue_no (numeric or string)
        if (!$queueRow) {
            $qSt = $conn->prepare("SELECT * FROM queues WHERE queue_no = ? LIMIT 1");
            if ($qSt) {
                $qSt->bind_param('s', $queueParam);
                $qSt->execute();
                $qRes = $qSt->get_result();
                $queueRow = $qRes ? $qRes->fetch_assoc() : null;
                $qSt->close();
            }
        }

        // final fallback: try by queue_code
        if (!$queueRow) {
            $qSt = $conn->prepare("SELECT * FROM queues WHERE queue_code = ? LIMIT 1");
            if ($qSt) {
                $qSt->bind_param('s', $queueParam);
                $qSt->execute();
                $qRes = $qSt->get_result();
                $queueRow = $qRes ? $qRes->fetch_assoc() : null;
                $qSt->close();
            }
        }

        if (!$queueRow) {
            echo json_encode(['success' => false, 'message' => 'Queue not found']);
            exit;
        }

        // fetch services for this queue
        $services = [];
        $svcStmt2 = $conn->prepare(
            "SELECT s.service_description, qs.soft_copy FROM queue_services qs JOIN services s ON qs.service_id = s.service_id WHERE qs.queue_id = ?"
        );
        if ($svcStmt2) {
            $qid = intval($queueRow['queue_id']);
            $svcStmt2->bind_param('i', $qid);
            $svcStmt2->execute();
            $sres = $svcStmt2->get_result();
            while ($r = $sres->fetch_assoc()) {
                $services[] = ['service_description' => $r['service_description'], 'soft_copy' => (isset($r['soft_copy']) && intval($r['soft_copy']) === 1) ? 1 : 0];
            }
            $svcStmt2->close();
        }
        $queueRow['services'] = $services;

        // enrich with client info if client_id present
        if (isset($queueRow['client_id']) && intval($queueRow['client_id']) > 0) {
            $cid = intval($queueRow['client_id']);
            $cst = $conn->prepare("SELECT name, student_number, course FROM clients WHERE client_id = ? LIMIT 1");
            if ($cst) {
                $cst->bind_param('i', $cid);
                $cst->execute();
                $cres = $cst->get_result();
                if ($cres && ($crow = $cres->fetch_assoc())) {
                    if (empty($queueRow['customer_name']) && isset($crow['name'])) $queueRow['customer_name'] = $crow['name'];
                    if (isset($crow['student_number'])) $queueRow['student_number'] = $crow['student_number'];
                    if (isset($crow['course'])) $queueRow['course'] = $crow['course'];
                }
                $cst->close();
            }
        }

        // If student/course still missing, try to locate a client by contact (phone/email/name)
        $needsStudent = !isset($queueRow['student_number']) || $queueRow['student_number'] === null || $queueRow['student_number'] === '';
        $needsCourse = !isset($queueRow['course']) || $queueRow['course'] === null || $queueRow['course'] === '';
        if (($needsStudent || $needsCourse) && (empty($queueRow['client_id']) || intval($queueRow['client_id']) <= 0)) {
            $found = false;
            // try by phone
            $phoneToCheck = $queueRow['customer_phone'] ?? $queueRow['phone'] ?? null;
            if ($phoneToCheck) {
                $cst2 = $conn->prepare("SELECT client_id, name, student_number, course FROM clients WHERE phone = ? LIMIT 1");
                if ($cst2) {
                    $cst2->bind_param('s', $phoneToCheck);
                    $cst2->execute();
                    $cres2 = $cst2->get_result();
                    if ($cres2 && ($crow2 = $cres2->fetch_assoc())) {
                        if (isset($crow2['name']) && empty($queueRow['customer_name'])) $queueRow['customer_name'] = $crow2['name'];
                        if (!isset($queueRow['student_number']) || $queueRow['student_number'] === null || $queueRow['student_number'] === '') $queueRow['student_number'] = $crow2['student_number'];
                        if (!isset($queueRow['course']) || $queueRow['course'] === null || $queueRow['course'] === '') $queueRow['course'] = $crow2['course'];
                        $found = true;
                    }
                    $cst2->close();
                }
            }
            // try by email
            if (!$found) {
                $emailToCheck = $queueRow['customer_email'] ?? $queueRow['email'] ?? null;
                if ($emailToCheck) {
                    $cst3 = $conn->prepare("SELECT client_id, name, student_number, course FROM clients WHERE email = ? LIMIT 1");
                    if ($cst3) {
                        $cst3->bind_param('s', $emailToCheck);
                        $cst3->execute();
                        $cres3 = $cst3->get_result();
                        if ($cres3 && ($crow3 = $cres3->fetch_assoc())) {
                            if (isset($crow3['name']) && empty($queueRow['customer_name'])) $queueRow['customer_name'] = $crow3['name'];
                            if (!isset($queueRow['student_number']) || $queueRow['student_number'] === null || $queueRow['student_number'] === '') $queueRow['student_number'] = $crow3['student_number'];
                            if (!isset($queueRow['course']) || $queueRow['course'] === null || $queueRow['course'] === '') $queueRow['course'] = $crow3['course'];
                            $found = true;
                        }
                        $cst3->close();
                    }
                }
            }
            // try by exact name match as last resort
            if (!$found) {
                $nameToCheck = $queueRow['customer_name'] ?? $queueRow['name'] ?? null;
                if ($nameToCheck) {
                    $cst4 = $conn->prepare("SELECT client_id, name, student_number, course FROM clients WHERE name = ? LIMIT 1");
                    if ($cst4) {
                        $cst4->bind_param('s', $nameToCheck);
                        $cst4->execute();
                        $cres4 = $cst4->get_result();
                        if ($cres4 && ($crow4 = $cres4->fetch_assoc())) {
                            if (isset($crow4['name']) && empty($queueRow['customer_name'])) $queueRow['customer_name'] = $crow4['name'];
                            if (!isset($queueRow['student_number']) || $queueRow['student_number'] === null || $queueRow['student_number'] === '') $queueRow['student_number'] = $crow4['student_number'];
                            if (!isset($queueRow['course']) || $queueRow['course'] === null || $queueRow['course'] === '') $queueRow['course'] = $crow4['course'];
                            $found = true;
                        }
                        $cst4->close();
                    }
                }
            }
        }

        echo json_encode(['success' => true, 'data' => $queueRow]);
        exit;
    }

    // Modified finish action: archive finished queue into finished_queues, include services and visitor info,
    // record which staff processed it and which window, then remove the original queue and its queue_services.
    if ($action === 'finish') {
        // get the current on-air queue for this window
        $onAir = displayOnAir($conn, $window);
        if (!$onAir || empty($onAir['queue_id'])) {
            echo json_encode(['success' => false, 'message' => 'No active queue on this window to finish.']);
            exit;
        }
        $queue_id = intval($onAir['queue_id']);

        // lock and fetch the queue row
        $qSt = $conn->prepare("SELECT * FROM queues WHERE queue_id = ? FOR UPDATE");
        $qSt->bind_param('i', $queue_id);
        $qSt->execute();
        $qRes = $qSt->get_result();
        $queueRow = $qRes->fetch_assoc();
        $qSt->close();

        if (!$queueRow) {
            echo json_encode(['success' => false, 'message' => 'Queue not found']);
            exit;
        }

        // collect services for this queue
        $sSt = $conn->prepare("SELECT s.service_description, qs.soft_copy FROM queue_services qs JOIN services s ON qs.service_id = s.service_id WHERE qs.queue_id = ?");
        $sSt->bind_param('i', $queue_id);
        $sSt->execute();
        $sRes = $sSt->get_result();
        $servicesArr = [];
        while ($r = $sRes->fetch_assoc()) {
            $servicesArr[] = $r['service_description'] . ((isset($r['soft_copy']) && intval($r['soft_copy']) === 1) ? ' (PDF)' : '');
        }
        $sSt->close();
        $servicesText = count($servicesArr) ? implode(', ', $servicesArr) : null;

        // prepare archive insert (removed queue_code column)
        $processed_by = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : null;
        $finishedAt = date('Y-m-d H:i:s');

        // optional processing timestamps and remarks provided by client
        $process_start = isset($_REQUEST['process_start']) ? $_REQUEST['process_start'] : null;
        $process_finished = isset($_REQUEST['process_finished']) ? $_REQUEST['process_finished'] : null;
        $processing_duration = isset($_REQUEST['processing_duration']) ? $_REQUEST['processing_duration'] : null;
        $remarks = isset($_REQUEST['remarks']) ? trim($_REQUEST['remarks']) : null;
        $isVoid = isset($_REQUEST['isVoid']) && ($_REQUEST['isVoid'] === '1' || $_REQUEST['isVoid'] === 1 || $_REQUEST['isVoid'] === 'true');

        if ($process_finished) {
            $finishedAt = $process_finished;
        }

        // --- RESOLVE the actual window NUMBER that processed this queue ---
        // prefer session window_no; if only session.window_id (PK) exist, resolve to its window_no;
        // otherwise try to resolve from the queue row's window_id (legacy PK), else fallback to raw value.
        $window_no_to_store = 0;
        if (isset($_SESSION['window_no']) && intval($_SESSION['window_no']) > 0) {
            $window_no_to_store = intval($_SESSION['window_no']);
        } elseif (isset($_SESSION['window_id']) && intval($_SESSION['window_id']) > 0) {
            $tmpWid = intval($_SESSION['window_id']);
            $wr = $conn->prepare("SELECT window_no FROM office_windows WHERE window_id = ? LIMIT 1");
            if ($wr) {
                $wr->bind_param('i', $tmpWid);
                $wr->execute();
                $wres = $wr->get_result();
                if ($wres && ($rw = $wres->fetch_assoc())) {
                    $window_no_to_store = intval($rw['window_no']);
                }
                $wr->close();
            }
        } else {
            // try resolving queueRow.window_id as PK -> window_no
            $maybe = intval($queueRow['window_id'] ?? 0);
            if ($maybe > 0) {
                $wr = $conn->prepare("SELECT window_no FROM office_windows WHERE window_id = ? LIMIT 1");
                if ($wr) {
                    $wr->bind_param('i', $maybe);
                    $wr->execute();
                    $wres = $wr->get_result();
                    if ($wres && ($rw = $wres->fetch_assoc())) {
                        $window_no_to_store = intval($rw['window_no']);
                    }
                    $wr->close();
                }
            }
        }
        // final fallback: use whatever numeric was in queue row
        if ($window_no_to_store <= 0) {
            $window_no_to_store = intval($queueRow['window_id'] ?? 0);
        }
        // --- end resolve ---

        // transaction: insert into finished_queues, delete queue_services, delete queues
        $conn->begin_transaction();
        try {
            // --- ensure we have student/course information when possible ---
            $student_number = $queueRow['student_number'] ?? null;
            $course = $queueRow['course'] ?? null;
            if ((empty($student_number) || $student_number === null) && isset($queueRow['client_id']) && intval($queueRow['client_id']) > 0) {
                $tmpCid = intval($queueRow['client_id']);
                $cr = $conn->prepare("SELECT student_number, course FROM clients WHERE client_id = ? LIMIT 1");
                if ($cr) {
                    $cr->bind_param('i', $tmpCid);
                    $cr->execute();
                    $cres = $cr->get_result();
                    if ($cres && ($crow = $cres->fetch_assoc())) {
                        if (isset($crow['student_number'])) $student_number = $crow['student_number'];
                        if (isset($crow['course'])) $course = $crow['course'];
                    }
                    $cr->close();
                }
            }
            // fallback: try by phone/email/name if still missing
            if ((empty($student_number) || $student_number === null || $student_number === '') || (empty($course) || $course === null || $course === '')) {
                $tryPhone = $queueRow['customer_phone'] ?? $queueRow['phone'] ?? null;
                $tryEmail = $queueRow['customer_email'] ?? $queueRow['email'] ?? null;
                $tryName = $queueRow['customer_name'] ?? $queueRow['name'] ?? null;
                if ($tryPhone) {
                    $cr2 = $conn->prepare("SELECT student_number, course FROM clients WHERE phone = ? LIMIT 1");
                    if ($cr2) {
                        $cr2->bind_param('s', $tryPhone);
                        $cr2->execute();
                        $cres2 = $cr2->get_result();
                        if ($cres2 && ($crow2 = $cres2->fetch_assoc())) {
                            if (isset($crow2['student_number']) && (!$student_number || $student_number === null || $student_number === '')) $student_number = $crow2['student_number'];
                            if (isset($crow2['course']) && (!$course || $course === null || $course === '')) $course = $crow2['course'];
                        }
                        $cr2->close();
                    }
                }
                if ((!$student_number || $student_number === null || $student_number === '') && $tryEmail) {
                    $cr3 = $conn->prepare("SELECT student_number, course FROM clients WHERE email = ? LIMIT 1");
                    if ($cr3) {
                        $cr3->bind_param('s', $tryEmail);
                        $cr3->execute();
                        $cres3 = $cr3->get_result();
                        if ($cres3 && ($crow3 = $cres3->fetch_assoc())) {
                            if (isset($crow3['student_number'])) $student_number = $crow3['student_number'];
                            if (isset($crow3['course'])) $course = $crow3['course'];
                        }
                        $cr3->close();
                    }
                }
                if ((!$student_number || $student_number === null || $student_number === '') && $tryName) {
                    $cr4 = $conn->prepare("SELECT student_number, course FROM clients WHERE name = ? LIMIT 1");
                    if ($cr4) {
                        $cr4->bind_param('s', $tryName);
                        $cr4->execute();
                        $cres4 = $cr4->get_result();
                        if ($cres4 && ($crow4 = $cres4->fetch_assoc())) {
                            if (isset($crow4['student_number'])) $student_number = $crow4['student_number'];
                            if (isset($crow4['course'])) $course = $crow4['course'];
                        }
                        $cr4->close();
                    }
                }
            }

            // We now store office name (text) in finished_queues.office_id.
            // Resolve office name from session or from queue row's office_id (if numeric), otherwise cast to string.
            $office_name_to_store = null;
            if (isset($_SESSION['office_id']) && intval($_SESSION['office_id']) > 0) {
                $tmp = intval($_SESSION['office_id']);
                $sr = $conn->prepare("SELECT office_description FROM offices WHERE office_id = ? LIMIT 1");
                if ($sr) {
                    $sr->bind_param('i', $tmp);
                    $sr->execute();
                    $res = $sr->get_result();
                    if ($res && ($rw = $res->fetch_assoc())) $office_name_to_store = $rw['office_description'];
                    $sr->close();
                }
            }
            if (!$office_name_to_store) {
                $maybe = $queueRow['office_id'] ?? null;
                if (is_numeric($maybe) && intval($maybe) > 0) {
                    $sr = $conn->prepare("SELECT office_description FROM offices WHERE office_id = ? LIMIT 1");
                    if ($sr) {
                        $mi = intval($maybe);
                        $sr->bind_param('i', $mi);
                        $sr->execute();
                        $res = $sr->get_result();
                        if ($res && ($rw = $res->fetch_assoc())) $office_name_to_store = $rw['office_description'];
                        $sr->close();
                    }
                }
            }
            if (!$office_name_to_store) {
                // fallback: use whatever is in queueRow.office_id as string (could already be name)
                $office_name_to_store = (string)($queueRow['office_id'] ?? '');
            }

            $window_id = $window_no_to_store; // store the resolved window NUMBER here
            $created_at = $queueRow['created_at'] ?? null;
            $status = 'finished';
            $customer_name = $queueRow['customer_name'] ?? null;
            $customer_phone = $queueRow['customer_phone'] ?? null;
            $customer_email = $queueRow['customer_email'] ?? null;

            // Determine target table (voided vs finished)
            $targetTable = $isVoid ? 'voided_queues' : 'finished_queues';

            // detect optional columns on the target table
            $optCols = ['student_number'=>false,'course'=>false,'process_start'=>false,'process_finished'=>false,'processing_duration'=>false,'remarks'=>false];
            foreach (array_keys($optCols) as $col) {
                $chk = $conn->prepare("SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ? LIMIT 1");
                if ($chk) {
                    $chk->bind_param('ss', $targetTable, $col);
                    $chk->execute();
                    $cres = $chk->get_result();
                    if ($cres && $cres->fetch_row()) $optCols[$col] = true;
                    $chk->close();
                }
            }

            $cols = ['original_queue_id','office_id','window_id','processed_by','finished_at','created_at','status','services','customer_name','customer_phone','customer_email'];
            $placeholders = array_fill(0, count($cols), '?');
            $types = 'isissssssss';
            $values = [ $queue_id, $office_name_to_store, $window_id, $processed_by, $finishedAt, $created_at, $status, $servicesText, $customer_name, $customer_phone, $customer_email ];

            if ($optCols['student_number']) { $cols[] = 'student_number'; $placeholders[] = '?'; $types .= 's'; $values[] = $student_number; }
            if ($optCols['course'])         { $cols[] = 'course'; $placeholders[] = '?'; $types .= 's'; $values[] = $course; }
            if ($optCols['process_start'])  { $cols[] = 'process_start'; $placeholders[] = '?'; $types .= 's'; $values[] = $process_start; }
            if ($optCols['process_finished']) { $cols[] = 'process_finished'; $placeholders[] = '?'; $types .= 's'; $values[] = $process_finished; }
            if ($optCols['processing_duration']) { $cols[] = 'processing_duration'; $placeholders[] = '?'; $types .= 's'; $values[] = $processing_duration; }
            if ($optCols['remarks'])        { $cols[] = 'remarks'; $placeholders[] = '?'; $types .= 's'; $values[] = $remarks; }

            $insertCols = implode(', ', $cols);
            $insertHolders = implode(', ', $placeholders);
            $dupUpdate = [];
            foreach ($cols as $c) { if ($c !== 'original_queue_id') $dupUpdate[] = "$c = VALUES($c)"; }
            $dupSql = implode(', ', $dupUpdate);

            $insSql = "INSERT INTO `{$targetTable}` ($insertCols) VALUES ($insertHolders) ON DUPLICATE KEY UPDATE $dupSql";
            $ins = $conn->prepare($insSql);
            if ($ins) {
                $params = array_merge([$types], $values);
                $refs = [];
                foreach ($params as $k => $v) $refs[$k] = &$params[$k];
                call_user_func_array([$ins, 'bind_param'], $refs);
                $ins->execute();
                $ins->close();
            }

            // remove queue services
            $dqs = $conn->prepare("DELETE FROM queue_services WHERE queue_id = ?");
            $dqs->bind_param('i', $queue_id);
            $dqs->execute();
            $dqs->close();

            // remove the original queue row
            $dq = $conn->prepare("DELETE FROM queues WHERE queue_id = ?");
            $dq->bind_param('i', $queue_id);
            $dq->execute();
            $dq->close();

            $conn->commit();
            echo json_encode(['success' => true]);
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            error_log('Archive finish error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Failed to archive finished queue']);
            exit;
        }
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

    // attach services (with soft_copy flag) for each queue item and on-air
    $svcStmt = $conn->prepare(
        "SELECT s.service_description, qs.soft_copy 
         FROM queue_services qs 
         JOIN services s ON qs.service_id = s.service_id 
         WHERE qs.queue_id = ?"
    );
    if ($svcStmt) {
        foreach ($queue_list as &$q) {
            $services = [];
            $id = intval($q['queue_id'] ?? 0);
            if ($id > 0) {
                $svcStmt->bind_param('i', $id);
                $svcStmt->execute();
                $res = $svcStmt->get_result();
                while ($r = $res->fetch_assoc()) {
                    $services[] = [
                        'service_description' => $r['service_description'],
                        'soft_copy' => (isset($r['soft_copy']) && intval($r['soft_copy']) === 1) ? 1 : 0
                    ];
                }
            }
            $q['services'] = $services;
        }
        // on-air
        if ($on_air && !empty($on_air['queue_id'])) {
            $services = [];
            $oid = intval($on_air['queue_id']);
            if ($oid > 0) {
                $svcStmt->bind_param('i', $oid);
                $svcStmt->execute();
                $res = $svcStmt->get_result();
                while ($r = $res->fetch_assoc()) {
                    $services[] = [
                        'service_description' => $r['service_description'],
                        'soft_copy' => (isset($r['soft_copy']) && intval($r['soft_copy']) === 1) ? 1 : 0
                    ];
                }
            }
            $on_air['services'] = $services;
        }
        $svcStmt->close();
        // Enrich queue items and on-air with student info when available (clients table)
        $clientStmt = $conn->prepare("SELECT name, phone, email, student_number, course FROM clients WHERE client_id = ? LIMIT 1");
        if ($clientStmt) {
            foreach ($queue_list as &$q) {
                $cid = intval($q['client_id'] ?? 0);
                if ($cid > 0) {
                    $clientStmt->bind_param('i', $cid);
                    $clientStmt->execute();
                    $cres = $clientStmt->get_result();
                    if ($cres && ($crow = $cres->fetch_assoc())) {
                        if (isset($crow['name'])) {
                            $q['customer_name'] = $crow['name'];
                            $q['name'] = $crow['name'];
                        }
                        if (isset($crow['phone'])) {
                            $q['customer_phone'] = $crow['phone'];
                            $q['phone'] = $crow['phone'];
                        }
                        if (isset($crow['email'])) {
                            $q['customer_email'] = $crow['email'];
                            $q['email'] = $crow['email'];
                        }
                        if (isset($crow['student_number'])) $q['student_number'] = $crow['student_number'];
                        if (isset($crow['course'])) $q['course'] = $crow['course'];
                    }
                }
            }
            if ($on_air && !empty($on_air['queue_id'])) {
                $oid = intval($on_air['queue_id'] ?? 0);
                // try to find client_id on on_air row
                $maybeCid = intval($on_air['client_id'] ?? 0);
                if ($maybeCid > 0) {
                    $clientStmt->bind_param('i', $maybeCid);
                    $clientStmt->execute();
                    $cres = $clientStmt->get_result();
                    if ($cres && ($crow = $cres->fetch_assoc())) {
                        if (isset($crow['name'])) {
                            $on_air['customer_name'] = $crow['name'];
                            $on_air['name'] = $crow['name'];
                        }
                        if (isset($crow['phone'])) {
                            $on_air['customer_phone'] = $crow['phone'];
                            $on_air['phone'] = $crow['phone'];
                        }
                        if (isset($crow['email'])) {
                            $on_air['customer_email'] = $crow['email'];
                            $on_air['email'] = $crow['email'];
                        }
                        if (isset($crow['student_number'])) $on_air['student_number'] = $crow['student_number'];
                        if (isset($crow['course'])) $on_air['course'] = $crow['course'];
                    }
                }
            }
            $clientStmt->close();
        }
    }

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