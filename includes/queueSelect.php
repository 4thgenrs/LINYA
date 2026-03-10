<?php
session_start();
// require login
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || intval($_SESSION['user_type']) !== 2) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../linya_db/config.php';

// session-provided office/window (fallback to query params)
$office = isset($_SESSION['office_id']) ? intval($_SESSION['office_id']) : (isset($_GET['office']) ? intval($_GET['office']) : 1);
$window_no = isset($_SESSION['window_no']) ? intval($_SESSION['window_no']) : (isset($_GET['window']) ? intval($_GET['window']) : 1);
$user_name = htmlspecialchars($_SESSION['user_name']);

// Resolve the real window_id (PK)
$window_id = 0;
if (isset($conn)) {
    $rs = $conn->prepare("SELECT window_id FROM office_windows WHERE office_id = ? AND window_no = ? LIMIT 1");
    if ($rs) {
        $rs->bind_param('ii', $office, $window_no);
        $rs->execute();
        $rres = $rs->get_result();
        if ($rres && ($rw = $rres->fetch_assoc())) {
            $window_id = intval($rw['window_id']);
            $_SESSION['window_id'] = $window_id;
        }
        $rs->close();
    }
}
$window_attr = $window_id > 0 ? $window_id : $window_no;

// Page-level permission check
if (isset($_SESSION['user_id']) && isset($_SESSION['user_type']) && intval($_SESSION['user_type']) === 2) {
    $staffId = intval($_SESSION['user_id']);
    $allowed = false;

    if ($window_id > 0) {
        $chk = $conn->prepare("SELECT 1 FROM office_windows WHERE window_id = ? AND assigned_staff_id = ? LIMIT 1");
        if ($chk) {
            $chk->bind_param('ii', $window_id, $staffId);
            $chk->execute();
            $cres = $chk->get_result();
            if ($cres && $cres->fetch_row()) $allowed = true;
            $chk->close();
        }
    } else {
        $chk2 = $conn->prepare("SELECT 1 FROM office_windows WHERE office_id = ? AND window_no = ? AND assigned_staff_id = ? LIMIT 1");
        if ($chk2) {
            $chk2->bind_param('iii', $office, $window_no, $staffId);
            $chk2->execute();
            $cres2 = $chk2->get_result();
            if ($cres2 && $cres2->fetch_row()) $allowed = true;
            $chk2->close();
        }
    }

    if (!$allowed) {
        header('Location: ../includes/dashboard.php');
        exit;
    }
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Queue Window</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- FontAwesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <link rel="stylesheet" href="../css/queueSelect.css">
</head>

<body>
  <div class="container-fluid queue-layout-shell px-0">
    <div class="row g-4 align-items-start queue-layout-row">
      <div class="col-12 col-xl-8">
        <div class="box" role="group" aria-label="Window Control" id="boxRoot"
             data-office="<?php echo $office; ?>"
             data-window="<?php echo $window_attr; ?>"
             data-window-no="<?php echo $window_no; ?>">

          <div class="panel-badge">
            <i class="fa-solid fa-wave-square"></i>
            Active Window Station
          </div>

          <div class="box-top d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3">
            <div class="top-primary d-flex flex-column gap-1">
              <div id="window" class="window">Window <?php echo $window_no; ?></div>
              <div class="window-subtitle">
                <i class="fa-solid fa-building me-1"></i>
                Office queue control panel
              </div>
            </div>
            <div class="signed text-sm-end">
              <div class="muted small">Signed in as</div>
              <div class="strong"><?php echo $user_name; ?></div>
            </div>
          </div>

          <div class="hero-panel text-center">
            <div class="hero-ring"></div>
            <div id="number" class="number">Loading…</div>
            <div class="status-line"><i id="ongoingText"></i></div>
            <div id="currentServices" class="service-chips"></div>
          </div>

          <div class="controls row g-2 justify-content-center mx-0">
            <div class="col-12 col-sm-auto px-0">
              <button id="finished" class="w-100" type="button" disabled><i class="fa-solid fa-check me-2"></i>Finished</button>
            </div>
            <div class="col-12 col-sm-auto px-0">
              <button id="onBreak" class="w-100" type="button" data-break="false"><i class="fa-solid fa-mug-hot me-2"></i>Break</button>
            </div>
            <div class="col-12 col-sm-auto px-0">
              <button id="nextQueue" class="w-100" type="button"><i class="fa-solid fa-forward-step me-2"></i>Call Next</button>
            </div>
          </div>

          <div class="action-caption text-center mx-auto">Use the controls below to manage the active queue and customer attachments.</div>

          <div class="right-controls row g-2 justify-content-center mx-0">
            <div class="col-12 col-sm-auto px-0">
              <button id="attachBtn" class="btn-ghost w-100" type="button"><i class="fa-solid fa-paperclip"></i> Attach</button>
            </div>
            <div class="col-12 col-sm-auto px-0">
              <button id="notifyBtn" class="btn-ghost w-100" type="button"><i class="fa-solid fa-bell"></i> Notify</button>
            </div>
          </div>

          <form id="attachForm" style="display:none" enctype="multipart/form-data" method="post" action="../backend/queueAttach.php">
            <input type="file" id="attachmentInput" name="attachments[]" accept=".pdf,image/*" multiple />
            <input type="hidden" name="queue_id" id="attachQueueId" value="" />
          </form>

          <div id="attachModal" class="attach-modal" aria-hidden="true" style="display:none;position:fixed;inset:0;z-index:9999;align-items:center;justify-content:center;background:rgba(0,0,0,0.45);backdrop-filter:blur(6px)">
            <div class="attach-modal-card" style="width:92%;max-width:720px;background:#fff;border-radius:18px;padding:20px;box-shadow:0 24px 60px rgba(15,23,42,0.22);border:1px solid rgba(226,232,240,0.9);">
              <div class="attach-modal-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                <div>
                  <strong class="attach-modal-title">Attachments Preview</strong>
                  <div class="attach-modal-subtitle">Review files before sending them to the active queue.</div>
                </div>
                <button id="attachCloseBtn" class="btn-ghost" type="button">Back</button>
              </div>
              <div class="attach-modal-note" style="font-size:13px;color:#666;margin-bottom:8px">
                Allowed file types: PDF and images. Max size per file: 10 MB.
              </div>
              <div id="attachPreviewList" class="attach-preview-list" style="display:flex;flex-direction:column;gap:8px;max-height:360px;overflow:auto;padding:8px;border:1px solid #e5e7eb;border-radius:12px;margin-bottom:12px;background:#f8fafc"></div>
              <div class="attach-modal-actions row g-2 justify-content-end mx-0" style="display:flex;justify-content:flex-end;gap:8px">
                <div class="col-12 col-sm-auto px-0">
                  <button id="attachAddBtn" class="btn-ghost w-100" type="button">Add file</button>
                </div>
                <div class="col-12 col-sm-auto px-0">
                  <button id="attachSendBtn" class="w-100" type="button">Send</button>
                </div>
                <div class="col-12 col-sm-auto px-0">
                  <button id="attachCancelBtn" class="btn-ghost w-100" type="button">Cancel</button>
                </div>
              </div>
              <div id="attachModalStatus" class="attach-modal-status" style="margin-top:8px;color:#666;font-size:13px"></div>
            </div>
          </div>

          <div id="attachStatus" class="small muted" style="margin-top:8px;display:none"></div>
        </div>
      </div>

      <div class="col-12 col-xl-4">
        <div class="queue-box" aria-label="Queue List">
          <div class="queue-title"><i class="fa-solid fa-list-ul me-2"></i>Queue List</div>
          <div class="queue-subtitle">Pending queues assigned to this office window.</div>
          <div id="queueListContainer"></div>
        </div>
      </div>
    </div>
  </div>

  <script src="../js/queueSelect.js"></script>
</body>
</html>
