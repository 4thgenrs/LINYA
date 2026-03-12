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
  <div class="wrap">

    <div class="box" role="group" aria-label="Window Control" id="boxRoot"
         data-office="<?php echo $office; ?>"
         data-window="<?php echo $window_attr; ?>"
         data-window-no="<?php echo $window_no; ?>">

      <div class="box-top">
        <div id="window" class="window">Window <?php echo $window_no; ?></div>
        <div class="signed">
          <div class="muted small">Signed in as</div>
          <div class="strong"><?php echo $user_name; ?></div>
        </div>
      </div>

      <div id="number" class="number">Loading…</div>
      <div><i id="ongoingText"></i></div>

      <div id="currentServices" class="service-chips"></div>

      <div style="text-align:center;margin-top:6px">
        <button id="viewDetailsBtn" class="btn-ghost" type="button" disabled>View Details</button>
      </div>

      <div class="controls">
        <button id="startProcessing" type="button" disabled>Start Processing</button>
        <button id="nextQueue" type="button">Call Next</button>
      </div>

        <div class="right-controls justify-content-center">
          <button id="attachBtn" class="btn-ghost" type="button"><i class="fa-solid fa-paperclip"></i> Attach</button>
          <button id="notifyBtn" class="btn-ghost" type="button"><i class="fa-solid fa-bell"></i> Notify</button>
        </div>

      <form id="attachForm" style="display:none" enctype="multipart/form-data" method="post" action="../backend/queueAttach.php">
        <input type="file" id="attachmentInput" name="attachments[]" accept=".pdf,image/*" multiple />
        <input type="hidden" name="queue_id" id="attachQueueId" value="" />
      </form>

      <div id="attachModal" aria-hidden="true" style="display:none;position:fixed;inset:0;z-index:9999;align-items:center;justify-content:center;background:rgba(0,0,0,0.45)">
        <div style="width:92%;max-width:720px;background:#fff;border-radius:10px;padding:16px;box-shadow:0 10px 30px rgba(0,0,0,0.2);">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
            <strong>Attachments Preview</strong>
            <button id="attachCloseBtn" class="btn-ghost" type="button">Back</button>
          </div>
          <div style="font-size:13px;color:#666;margin-bottom:8px">
            Allowed file types: PDF and images. Max size per file: 10 MB.
          </div>
          <div id="attachPreviewList" style="display:flex;flex-direction:column;gap:8px;max-height:360px;overflow:auto;padding:6px;border:1px solid #f0f0f0;border-radius:6px;margin-bottom:12px"></div>
          <div style="display:flex;justify-content:flex-end;gap:8px">
            <button id="attachAddBtn" class="btn-ghost" type="button">Add file</button>
            <button id="attachSendBtn" type="button">Send</button>
            <button id="attachCancelBtn" class="btn-ghost" type="button">Cancel</button>
          </div>
          <div id="attachModalStatus" style="margin-top:8px;color:#666;font-size:13px"></div>
        </div>
      </div>

      <div id="attachStatus" class="small muted" style="margin-top:8px;display:none"></div>

      <div id="queueDetailsModal" aria-hidden="true" style="display:none;position:fixed;inset:0;z-index:9999;align-items:center;justify-content:center;background:rgba(0,0,0,0.45)">
        <div style="width:92%;max-width:820px;background:#fff;border-radius:10px;padding:16px;box-shadow:0 10px 30px rgba(0,0,0,0.2);">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
            <strong>Queue Details</strong>
            <button id="detailsCloseBtn" class="btn-ghost" type="button">Close</button>
          </div>
          <div id="queueDetailsContent" style="font-size:14px;color:#111;max-height:60vh;overflow:auto;padding:6px;border:1px solid #f0f0f0;border-radius:6px;margin-bottom:12px">
            <!-- populated dynamically -->
          </div>
        </div>
      </div>

    </div>

    <div class="queue-box" aria-label="Queue List">
      <div class="queue-title">Queue List</div>
      <div id="queueListContainer"></div>
    </div>

  </div>

  <script src="../js/queueSelect.js"></script>
</body>
</html>
