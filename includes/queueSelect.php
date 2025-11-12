<?php

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || intval($_SESSION['user_type']) !== 2) {
    header('Location: LINYA/index.php');
    exit;
}

// session-provided office/window (fallback to query params)
$office = isset($_SESSION['office_id']) ? intval($_SESSION['office_id']) : (isset($_GET['office']) ? intval($_GET['office']) : 1);
$window_no = isset($_SESSION['window_no']) ? intval($_SESSION['window_no']) : (isset($_GET['window']) ? intval($_GET['window']) : 1);
$user_name = htmlspecialchars($_SESSION['user_name']);
?>

  <title>Queue Window</title>

  <!-- Bootstrap & FontAwesome -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    body {
      /* background-color: #f8f9fa; */
      min-height: 100vh;
    }
    .window-card {
      /* background: #fff; */
      border-radius: 1rem;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      padding: 1.5rem;
    }
    .number {
      font-size: 3rem;
      font-weight: 700;
      color: #198754;
    }
    #ongoingText { font-style: italic; color: #6c757d; }
    .service-chips .chip {
      background-color: #d1e7dd;
      color: #0f5132;
      border-radius: 50rem;
      padding: 0.4rem 0.9rem;
      font-weight: 500;
    }
    .btn-ghost {
      background: none;
      border: none;
      color: #6c757d;
    }
    .btn-ghost:hover { color: #198754; }
    .queue-card {
      border: 2px solid #198754;
      border-radius: .75rem;
      padding: 1rem;
      margin-bottom: .75rem;
      background-color: #fff;
      transition: 0.2s;
    }
    .queue-card:hover { background-color: #f9fffa; box-shadow: 0 0 10px rgba(25,135,84,0.2); }

    .queue-box {
    background: var(--box);
    border-radius: 12px;
    padding: 18px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    align-items: stretch;
}

    .queue-item{
    width:100%;
    text-align:left;
    padding:10px 12px;
    margin:8px 0;
    border-radius:8px;
    background:#fff;
    color:#111;
    font-weight:600;
    border:1px solid #ccc;;
    display:flex;flex-direction:column;
}
  </style>
</head>
<body>
  <div class="container-fluid p-3">

        <div class="row g-4">
        <!-- LEFT COLUMN -->
        <div class="col-md-8 justify-content-center text-center">
          <div class="window-card" id="boxRoot" data-office="<?= $office ?>" data-window="<?= $window_no ?>">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
              <h4 class="text-success mb-0">Window <?= $window_no ?></h4>
              <div class="text-end">
                <div class="text-muted small">Signed in as</div>
                <strong><?= $_SESSION['user_name'] ?></strong>
              </div>
            </div>
          <div id="number" class="number mb-2">Loading…</div>
          <div><i id="ongoingText"></i></div>

          <div id="currentServices" class="service-chips justify-content-center d-flex flex-wrap gap-2 mt-3"></div>

          <div class="d-flex justify-content-center align-items-center flex-wrap mt-4">
            <div class="d-flex gap-2">
              <button id="attachBtn" class="btn-ghost" type="button">
                <i class="fa-solid fa-paperclip"></i> Attach
              </button>
              <button id="notifyBtn" class="btn-ghost" type="button">
                <i class="fa-solid fa-bell"></i> Notify
              </button>
              <button id="logoutBtn" class="btn-ghost" type="button">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
              </button>
            </div>
          </div>
          </div>

          <!-- Hidden file input -->
          <form id="attachForm" class="d-none" enctype="multipart/form-data" method="post" action="../backend/queueAttach.php">
            <input type="file" id="attachmentInput" name="attachments[]" accept=".pdf,image/*" multiple>
            <input type="hidden" name="queue_id" id="attachQueueId" value="">
          </form>

          <!-- Attachment modal -->
          <div id="attachModal" aria-hidden="true" class="d-none position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-dark bg-opacity-50" style="z-index:9999;">
            <div class="bg-white rounded-3 shadow p-3" style="max-width:720px;width:90%;">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <strong>Attachments Preview</strong>
                <button id="attachCloseBtn" class="btn-ghost" type="button">Back</button>
              </div>
              <div class="text-muted small mb-2">
                Allowed file types: PDF and images (jpg, jpeg, png, gif, webp, bmp). Max size per file: 10 MB.
              </div>
              <div id="attachPreviewList" class="border rounded-2 p-2 mb-3 overflow-auto" style="max-height:360px;"></div>
              <div class="d-flex justify-content-end gap-2">
                <button id="attachAddBtn" class="btn-ghost" type="button">Add file</button>
                <button id="attachSendBtn" type="button" class="btn btn-success">Send</button>
                <button id="attachCancelBtn" class="btn-ghost" type="button">Cancel</button>
              </div>
              <div id="attachModalStatus" class="text-muted small mt-2"></div>
            </div>
          </div>

          <div id="attachStatus" class="small text-muted mt-2 d-none"></div>
        </div>

        <!-- RIGHT COLUMN -->
        <div class="col-md-4">
          <div class="queue-box">
            <h5 class="text-success mb-3">Queue List</h5>
            <div id="queueListContainer"></div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <footer class="text-center mt-4">
    <div class="d-flex justify-content-center gap-2" role="group">
      <button id="finished" type="button" class="btn btn-success btn-lg" disabled>
        <i class="fa-solid fa-check"></i> Finished
      </button>
      <button id="onBreak" type="button" class="btn btn-warning btn-lg" data-break="false">
        <i class="fa-solid fa-mug-hot"></i> Break
      </button>
      <button id="nextQueue" type="button" class="btn btn-success btn-lg">
        <i class="fa-solid fa-phone"></i> Call Next
      </button>
    </div>
  </footer>

  </div>

  <script src="../js/queueSelect.js"></script>
</body>
</html>
