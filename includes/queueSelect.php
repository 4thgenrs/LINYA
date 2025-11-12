<?php
session_start();
// require login
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || intval($_SESSION['user_type']) !== 2) {
    header('Location: login.php');
    exit;
}

// session-provided office/window (fallback to query params)
$office = isset($_SESSION['office_id']) ? intval($_SESSION['office_id']) : (isset($_GET['office']) ? intval($_GET['office']) : 1);
$window_no = isset($_SESSION['window_no']) ? intval($_SESSION['window_no']) : (isset($_GET['window']) ? intval($_GET['window']) : 1);
$user_name = htmlspecialchars($_SESSION['user_name']);
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Queue Window</title>
  <link rel="stylesheet" href="../css/queueSelect.css" />
</head>
<body>
  <div class="wrap">
    <div class="box" role="group" aria-label="Window Control" id="boxRoot" data-office="<?php echo $office; ?>" data-window="<?php echo $window_no; ?>">
      <div class="box-top">
        <div id="window" class="window">Window <?php echo $window_no; ?></div>
        <div class="signed">
          <div class="muted small">Signed in as</div>
          <div class="strong"><?php echo $user_name; ?></div>
        </div>
      </div>

      <div id="number" class="number">Loading…</div>
      <div><i id="ongoingText"></i></div>

      <div id="currentServices" class="service-chips"> <!-- services for current on-air queue --> </div>

      <div class="controls">
        <button id="finished" type="button" disabled>Finished</button>
        <button id="onBreak" type="button" data-break="false">Break</button>
        <button id="nextQueue" type="button">Call Next</button>

        <div class="right-controls">
          <button id="attachBtn" class="btn-ghost" type="button">Attach file</button>
          <button id="notifyBtn" class="btn-ghost" type="button">Notify</button>
          <button id="logoutBtn" class="btn-ghost" type="button">Logout</button>
        </div>
      </div>

      <!-- hidden file input -->
      <form id="attachForm" style="display:none" enctype="multipart/form-data" method="post" action="../backend/queueAttach.php">
        <!-- allow multiple files and broader image types; name as array so backend can handle multiple -->
        <input type="file" id="attachmentInput" name="attachments[]" accept=".pdf,image/*" multiple />
        <input type="hidden" name="queue_id" id="attachQueueId" value="" />
      </form>

      <!-- Attachment preview modal -->
      <div id="attachModal" aria-hidden="true" style="display:none;position:fixed;inset:0;z-index:9999;align-items:center;justify-content:center;background:rgba(0,0,0,0.45)">
        <div style="width:92%;max-width:720px;background:#fff;border-radius:10px;padding:16px;box-shadow:0 10px 30px rgba(0,0,0,0.2);">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
            <strong>Attachments Preview</strong>
            <button id="attachCloseBtn" class="btn-ghost" type="button">Back</button>
          </div>
          <div style="font-size:13px;color:#666;margin-bottom:8px">
            Allowed file types: PDF and images (jpg, jpeg, png, gif, webp, bmp). Max size per file: 10 MB.
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
    </div>

    <div class="queue-box" aria-label="Queue List">
      <div class="queue-title">Queue List</div>
      <div id="queueListContainer"></div>
    </div>
  </div>

  <script src="../js/queueSelect.js"></script>
</body>
</html>