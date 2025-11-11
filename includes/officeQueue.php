<?php
$office = isset($_GET['office']) ? intval($_GET['office']) : 1;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Live Queue - Office <?= $office ?></title>

  <!-- Bootstrap & FontAwesome -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    :root {
      --bg: #f6f8fb;
      --card: #fff;
      --accent: #2563eb;
      --muted: #6b7280;
      --chip: #eef6ff;
    }

    * { box-sizing: border-box; }
    html, body { height: 100%; margin: 0; font-family: Inter, system-ui, Segoe UI, Roboto, Arial, sans-serif; background: var(--bg); color: #111; }
    #root { max-width: 1100px; margin: 18px auto; padding: 16px; }

    /* Header */
    .oq-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
    .oq-title { font-size: 20px; font-weight: 700; color: var(--accent); }
    .oq-sub { color: var(--muted); font-size: 13px; }
    .oq-select { padding:6px 8px; border-radius:6px; border:1px solid #e6e9ef; background:#fff; font-size:14px; }
    .oq-select-label { display:block; font-size:12px; color:var(--muted); margin-bottom:4px; text-align:right; margin-right:6px; }

    /* Main layout */
    .oq-main { display: grid; grid-template-columns:1fr 320px; gap:16px; }
    .oq-windows { display: grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:12px; }
    #windowsWrapper { background: var(--card); padding:16px; border-radius:10px; box-shadow:0 6px 18px rgba(15,23,42,0.06); min-height:80vh; }

    /* Window card */
    .window-card { background: var(--card); border-radius:10px; padding:12px; box-shadow:0 6px 18px rgba(15,23,42,0.06); display:flex; flex-direction:column; gap:8px; min-height:100px; }
    .window-top { display:flex; justify-content:space-between; align-items:center; }
    .window-no { font-weight:800; color:var(--accent); font-size:20px; }
    .window-staff { color:var(--muted); font-size:13px; }
    .svc-chip { display:inline-block; background:var(--chip); padding:6px 8px; border-radius:999px; font-size:12px; margin-right:6px; margin-top:6px; color:#0b1230; font-weight:700; }
    .svc-chip small.soft-badge { margin-left:8px; background:#111; color:#fff; padding:2px 6px; border-radius:999px; font-size:10px; font-weight:800; }

    /* Pending queues */
    .pending-list { display:flex; flex-direction:column; gap:10px; }
    .pending-item { background:var(--card); padding:10px; border-radius:8px; display:flex; flex-direction:column; gap:6px; box-shadow:0 6px 18px rgba(15,23,42,0.04); }
    .pending-code { font-weight:800; color:var(--accent); }
    .pending-empty { color:var(--muted); padding:12px; text-align:center; }

    @media (max-width:860px) {
      .oq-main { grid-template-columns:1fr; }
      .oq-pending { order:2; }
    }
  </style>
</head>
<body>
  <div id="root" data-office="<?= $office ?>">

    <!-- Header -->
    <header class="oq-header">
      <div>
        <div class="oq-title">Live Queue</div>
        <div class="oq-sub">Auto-updates every 3s</div>
      </div>
      <div class="d-flex align-items-center">
        <label for="officeSelect" class="oq-select-label me-2">Office:</label>
        <select id="officeSelect" class="oq-select form-select form-select-sm"></select>
      </div>
    </header>

    <!-- Main content -->
    <div class="oq-main">
      <!-- Windows column -->
      <div id="windowsWrapper">
        <div class="oq-windows" id="windowsContainer"></div>
      </div>

      <!-- Pending queues column -->
      <aside class="oq-pending">
        <h5 class="text-success mb-3">Pending Queues</h5>
        <div id="pendingList" class="pending-list"></div>
      </aside>
    </div>
  </div>

  <script src="../js/officeQueue.js" defer></script>
</body>
</html>
