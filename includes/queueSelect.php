<?php
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || intval($_SESSION['user_type']) !== 2) {
    header('Location: login.php');
    exit;
}

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

  <!-- Bootstrap + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <!-- <link rel="stylesheet" href="../css/queueSelect.css" /> -->

  <style>
    body {
      background: #f8f9fa;
      font-family: 'Poppins', sans-serif;
      min-height: 100vh;
      padding: 30px;
    }

    .box, .queue-box {
      background: #fff;
      border-radius: 15px;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
      padding: 25px;
      height: 100%;
    }

    /* .box:hover, .queue-box:hover {
      transform: translateY(-2px);
    } */

    .queue-item{
    width:100%;
    text-align:left;
    padding:10px 12px;
    margin:8px 0;
    border-radius:8px;
    background:#fff;
    color:#111;
    font-weight:600;
    border:1px solid #000;;
    display:flex;flex-direction:column;
}

    .box-top {
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 2px solid #e9ecef;
      padding-bottom: 10px;
      margin-bottom: 15px;
    }

    .window { font-size: 1.3rem; font-weight: 600; color: #1b5e20; }

    .signed .muted { color: #6c757d; font-size: 0.85rem; }
    .signed .strong { font-weight: 600; color: #212529; }

    .number { font-size: 4rem; font-weight: bold; color: #28a745; text-align: center; margin-bottom: 5px; }

    #ongoingText { display: block; text-align: center; color: #6c757d; margin-bottom: 15px; }

    .service-chips { display: flex; flex-wrap: wrap; justify-content: center; gap: 8px; margin-bottom: 20px; }

    .controls {
      display: flex; align-items: center; justify-content: space-between;
      flex-wrap: wrap; gap: 10px; margin-top: 10px;
    }
    .controls button { min-width: 110px; }
    .right-controls { display: flex; gap: 8px; }

    button { border-radius: 8px !important; transition: all 0.3s ease; }
    button:hover { transform: translateY(-1px); }

    .btn-ghost {
      background: transparent; border: 1px solid #ced4da; color: #495057;
    }
    .btn-ghost:hover { background: #f1f3f5; }

    #attachModal {
      display: none; position: fixed; inset: 0; z-index: 1050;
      align-items: center; justify-content: center;
      background: rgba(0, 0, 0, 0.45);
    }
    #attachModal > div{
      width: 92%; max-width: 720px; background: #fff;
      border-radius: 10px; padding: 20px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .queue-title {
      font-weight: 600; font-size: 1.2rem; color: #1b5e20;
      margin-bottom: 10px; border-bottom: 2px solid #e9ecef;
      padding-bottom: 6px;
    }

    #queueListContainer { max-height: 450px; overflow-y: auto; }
    #attachModal button { min-width: 90px; }
  </style>
</head>
<body>
  <div class="container-fluid">
    <div class="row g-4">
      <!-- Main Window Box: 8 columns -->
      <div class="col-12 col-lg-8">
        <div class="box" role="group" aria-label="Window Control" id="boxRoot" data-office="<?php echo $office; ?>" data-window="<?php echo $window_no; ?>">
          <div class="box-top">
            <div id="window" class="window"><i class="fa-solid fa-desktop me-2 text-success"></i>Window <?php echo $window_no; ?></div>
            <div class="signed text-end">
              <div class="muted small">Signed in as</div>
              <div class="strong"><i class="fa-solid fa-user-circle me-1 text-secondary"></i><?php echo $user_name; ?></div>
            </div>
          </div>

          <div id="number" class="number">Loading…</div>
          <div><i id="ongoingText"></i></div>
          <div id="currentServices" class="service-chips"></div>

          <div class="controls">
            <div class="btn-group gap-2"">
              <button id="finished" type="button" class="btn btn-success" disabled><i class="fa-solid fa-check-circle me-1"></i>Finished</button>
              <button id="onBreak" type="button" class="btn btn-warning text-dark" data-break="false"><i class="fa-solid fa-mug-saucer me-1"></i>Break</button>
              <button id="nextQueue" type="button" class="btn btn-primary"><i class="fa-solid fa-forward me-1"></i>Call Next</button>
            </div>

        <div class="right-controls">
          <button id="attachBtn" class="btn-ghost" type="button">Attach file</button>
          <button id="notifyBtn" class="btn-ghost" type="button">Notify</button>
        </div>
      </div>

          <form id="attachForm" style="display:none" enctype="multipart/form-data" method="post" action="../backend/queueAttach.php">
            <input type="file" id="attachmentInput" name="attachments[]" accept=".pdf,image/*" multiple />
            <input type="hidden" name="queue_id" id="attachQueueId" value="" />
          </form>

          <div id="attachModal" aria-hidden="true">
            <div>
              <div class="d-flex justify-content-between align-items-center mb-2">
                <strong><i class="fa-solid fa-paperclip me-2 text-success"></i>Attachments Preview</strong>
                <button id="attachCloseBtn" class="btn-ghost" type="button"><i class="fa-solid fa-arrow-left me-1"></i>Back</button>
              </div>
              <div class="text-muted small mb-2">
                Allowed: PDF & images (jpg, jpeg, png, gif, webp, bmp). Max: 10 MB per file.
              </div>
              <div id="attachPreviewList" class="border rounded p-2 mb-3" style="max-height:360px;overflow:auto;"></div>
              <div class="d-flex justify-content-end gap-2">
                <button id="attachAddBtn" class="btn-ghost" type="button"><i class="fa-solid fa-plus me-1"></i>Add</button>
                <button id="attachSendBtn" class="btn btn-success" type="button"><i class="fa-solid fa-paper-plane me-1"></i>Send</button>
                <button id="attachCancelBtn" class="btn-ghost" type="button">Cancel</button>
              </div>
              <div id="attachModalStatus" class="text-muted small mt-2"></div>
            </div>
          </div>

          <div id="attachStatus" class="small text-muted mt-2" style="display:none"></div>
        </div>
      </div>

      <!-- Queue List: 4 columns -->
      <div class="col-12 col-lg-4">
        <div class="queue-box" aria-label="Queue List">
          <div class="queue-title"><i class="fa-solid fa-list me-2"></i>Queue List</div>
          <div id="queueListContainer"></div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../js/queueSelect.js"></script>
</body>
</html>
