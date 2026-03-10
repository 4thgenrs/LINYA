<?php
// Simple admin page to list clients and remove them.
include '../linya_db/config.php';
session_start();

// Very small auth guard: require admin session (optional)
// if (!isset($_SESSION['user']) || $_SESSION['user']['user_type'] != 1) { echo 'Access denied'; exit; }

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Clients Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="../css/dashboard.css">
  <style>
    :root {
      --legacy-bg: #f4f7fb;
      --legacy-card: #ffffff;
      --legacy-text: #16324f;
      --legacy-muted: #64748b;
      --legacy-accent: #157347;
      --legacy-accent-deep: #0f5d35;
      --legacy-accent-soft: #e8f5e9;
      --legacy-border: #dbe7df;
      --legacy-shadow: 0 18px 40px rgba(15, 23, 42, 0.07);
      --legacy-shadow-soft: 0 12px 28px rgba(15, 23, 42, 0.06);
    }

    * { box-sizing: border-box; }
    html, body {
      margin: 0;
      background: linear-gradient(180deg, #f4f7fb 0%, #eef6f1 100%);
      color: var(--legacy-text);
      font-family: Inter, 'Segoe UI', system-ui, -apple-system, sans-serif;
    }
    .legacy-shell {
      display: grid;
      gap: 18px;
      padding: 24px 18px;
    }
    .legacy-hero,
    .legacy-table-card {
      border-radius: 22px;
      overflow: hidden;
    }
    .legacy-hero {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 18px;
      padding: 22px 24px;
      background: linear-gradient(135deg, #ffffff, #f3fbf6);
      border: 1px solid var(--legacy-border);
      box-shadow: var(--legacy-shadow);
    }
    .legacy-hero-copy { max-width: 760px; }
    .legacy-badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 7px 12px;
      border-radius: 999px;
      background: var(--legacy-accent-soft);
      border: 1px solid #cfe2d6;
      color: var(--legacy-accent);
      font-size: 12px;
      font-weight: 800;
      letter-spacing: .04em;
      text-transform: uppercase;
    }
    .legacy-hero h1 {
      margin: 10px 0 0;
      font-size: 34px;
      letter-spacing: -.03em;
      color: var(--legacy-text);
    }
    .legacy-subtitle {
      margin: 10px 0 0;
      color: var(--legacy-muted);
      font-size: 15px;
      line-height: 1.6;
    }
    .legacy-hero-side {
      min-width: 250px;
      align-self: stretch;
      display: flex;
      flex-direction: column;
      justify-content: center;
      gap: 8px;
      padding: 16px 18px;
      border-radius: 18px;
      background: rgba(255,255,255,0.8);
      border: 1px solid var(--legacy-border);
    }
    .legacy-status {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      width: fit-content;
      padding: 7px 12px;
      border-radius: 999px;
      background: #f5fbf7;
      border: 1px solid #dce9e0;
      color: var(--legacy-accent);
      font-size: 12px;
      font-weight: 700;
    }
    .legacy-status-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: #22c55e;
      box-shadow: 0 0 0 0 rgba(34,197,94,.42);
      animation: legacyPulse 2s infinite;
    }
    .legacy-status-copy {
      color: var(--legacy-muted);
      font-size: 13px;
      line-height: 1.55;
    }
    .legacy-table-card {
      background: var(--legacy-card);
      border: 1px solid #e4ece7;
      box-shadow: var(--legacy-shadow-soft);
    }
    .legacy-table-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      padding: 18px 20px;
      background: linear-gradient(135deg, #1b5e20, #157347);
      color: #fff;
    }
    .legacy-table-eyebrow {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 8px;
      padding: 7px 12px;
      border-radius: 999px;
      background: rgba(255,255,255,.14);
      border: 1px solid rgba(255,255,255,.2);
      color: #ecfdf3;
      font-size: 12px;
      font-weight: 800;
      letter-spacing: .04em;
      text-transform: uppercase;
    }
    .legacy-table-header h2 {
      margin: 0;
      font-size: 20px;
      font-weight: 700;
    }
    .legacy-table-header p {
      margin: 6px 0 0;
      color: rgba(255,255,255,.86);
      font-size: 13px;
    }
    .legacy-table-icon {
      width: 48px;
      height: 48px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 16px;
      background: rgba(255,255,255,.14);
      border: 1px solid rgba(255,255,255,.18);
      font-size: 18px;
    }
    .legacy-table-body { padding: 20px; }
    #msg {
      display: none;
      margin: 0;
      padding: 14px 16px;
      border-radius: 14px;
      font-size: 14px;
      font-weight: 600;
    }
    .legacy-message.is-success {
      display: block !important;
      background: linear-gradient(135deg, #ecfdf5, #dcfce7);
      color: #166534 !important;
      border: 1px solid #ccebd8;
    }
    .legacy-message.is-error {
      display: block !important;
      background: linear-gradient(135deg, #fef2f2, #fee2e2);
      color: #991b1b !important;
      border: 1px solid #f4cdd2;
    }
    #clientsTable td small {
      color: var(--legacy-muted) !important;
      display: inline-block;
      margin-top: 4px;
    }
    .btn.danger {
      min-width: 40px;
      min-height: 38px;
      padding: 0 12px;
      border-radius: 12px;
      background: linear-gradient(135deg, #dc3545, #c82333);
      color: #fff;
      border: none;
      box-shadow: 0 10px 18px rgba(220,53,69,.16);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      font-weight: 700;
    }
    .btn.danger:hover { background: linear-gradient(135deg, #cc2f3f, #b51f2e); }
    @keyframes legacyPulse {
      0% { box-shadow: 0 0 0 0 rgba(34,197,94,.42); }
      70% { box-shadow: 0 0 0 10px rgba(34,197,94,0); }
      100% { box-shadow: 0 0 0 0 rgba(34,197,94,0); }
    }
    @media (max-width: 992px) {
      .legacy-hero { flex-direction: column; }
      .legacy-hero-copy { max-width: none; }
      .legacy-hero-side { width: 100%; min-width: 0; }
    }
    @media (max-width: 768px) {
      .legacy-shell { padding: 16px 8px; gap: 14px; }
      .legacy-hero,
      .legacy-table-card { border-radius: 18px; }
      .legacy-hero,
      .legacy-table-header,
      .legacy-table-body { padding-left: 16px; padding-right: 16px; }
      .legacy-hero { padding-top: 18px; padding-bottom: 18px; }
      .legacy-hero h1 { font-size: 28px; }
    }
    @media (max-width: 576px) {
      .legacy-hero h1 { font-size: 24px; }
      .legacy-subtitle,
      .legacy-status-copy { font-size: 12px; }
      .legacy-badge,
      .legacy-status,
      .legacy-table-eyebrow { font-size: 11px; padding: 6px 10px; }
      .legacy-table-header h2 { font-size: 18px; }
    }
  </style>
  <link rel="stylesheet" href="../css/table-unified.css">
</head>
<body>
  <main class="legacy-shell">
    <section class="legacy-hero">
      <div class="legacy-hero-copy">
        <span class="legacy-badge">
          <i class="fa-solid fa-users-viewfinder"></i>
          Legacy Client Admin
        </span>
        <h1>Registered Clients</h1>
        <p class="legacy-subtitle">Browse the legacy client directory, review enrolled profile details, and remove outdated client records from one compact admin view.</p>
      </div>
      <div class="legacy-hero-side">
        <div class="legacy-status">
          <span class="legacy-status-dot"></span>
          Client registry
        </div>
        <span class="legacy-status-copy">This page keeps the simple delete workflow while matching the newer management-page look and spacing.</span>
      </div>
    </section>

    <section class="legacy-table-card">
      <div class="legacy-table-header">
        <div>
          <span class="legacy-table-eyebrow">Client Directory</span>
          <h2>Registered Clients</h2>
          <p>Review saved client records and remove entries that are no longer needed.</p>
        </div>
        <div class="legacy-table-icon">
          <i class="fa-solid fa-table-list"></i>
        </div>
      </div>
      <div class="legacy-table-body">
        <div id="msg"></div>
        <div class="table-responsive">
          <table id="clientsTable">
            <thead>
              <tr><th>ID</th><th>Name</th><th>Phone</th><th>Email</th><th>Created</th><th>Actions</th></tr>
            </thead>
            <tbody>
              <?php
              $q = $conn->query('SELECT client_id,name,phone,email,created_at,safe_name FROM clients ORDER BY created_at DESC');
              if ($q) {
                  while ($r = $q->fetch_assoc()) {
                      echo '<tr data-id="'.intval($r['client_id']).'">';
                      echo '<td>'.intval($r['client_id']).'</td>';
                      echo '<td>'.htmlspecialchars($r['name']).' <small>('.htmlspecialchars($r['safe_name']).')</small></td>';
                      echo '<td>'.htmlspecialchars($r['phone']).'</td>';
                      echo '<td>'.htmlspecialchars($r['email']).'</td>';
                      echo '<td>'.htmlspecialchars($r['created_at']).'</td>';
                      echo '<td><button class="btn danger" data-id="'.intval($r['client_id']).'"><i class="fa-solid fa-trash"></i></button></td>';
                      echo '</tr>';
                  }
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </main>

  <script>
    document.addEventListener('click', function(e){
      const btn = e.target.closest('button[data-id]');
      if (!btn) return;
      const id = btn.getAttribute('data-id');
      if (!confirm('Delete client ID '+id+'? This cannot be undone.')) return;
      fetch('../backend/clients_delete.php', { method: 'POST', headers: { 'Content-Type':'application/json' }, body: JSON.stringify({ client_id: parseInt(id,10) }) })
        .then(r => r.json())
        .then(j => {
          const msg = document.getElementById('msg');
          if (j && j.status === 'ok') {
            msg.className = 'legacy-message is-success';
            msg.textContent = 'Deleted client ' + id;
            const row = document.querySelector('tr[data-id="'+id+'"]'); if (row) row.remove();
          } else {
            msg.className = 'legacy-message is-error';
            msg.textContent = 'Error: ' + (j && j.message ? j.message : 'unknown');
          }
        })
        .catch(err => {
          const msg = document.getElementById('msg');
          msg.className = 'legacy-message is-error';
          msg.textContent = 'Network error';
        });
    });
  </script>
</body>
</html>
