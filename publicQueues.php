<?php
$defaultOffice = isset($_GET['office']) ? max(1, intval($_GET['office'])) : 1;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Public Office Queues — LINYA</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <style>
    :root {
      --pq-bg: #eef4f0;
      --pq-card: #ffffff;
      --pq-text: #16324f;
      --pq-muted: #64748b;
      --pq-accent: #157347;
      --pq-accent-deep: #0f5d35;
      --pq-accent-soft: #e8f5e9;
      --pq-border: #dbe7df;
      --pq-shadow: 0 22px 50px rgba(15, 23, 42, 0.08);
      --pq-shadow-soft: 0 14px 28px rgba(15, 23, 42, 0.06);
      --pq-nav-height: clamp(84px, 11vh, 112px);
      --pq-footer-height: clamp(132px, 22vh, 180px);
    }

    * { box-sizing: border-box; }

    body {
      margin: 0;
      min-height: 100vh;
      min-height: 100dvh;
      background: linear-gradient(180deg, #eef4f0 0%, #e6eeea 100%);
      color: var(--pq-text);
      font-family: Inter, "Segoe UI", system-ui, sans-serif;
      overflow-x: hidden;
      overflow-y: auto;
    }

    .public-shell {
      width: 100%;
      margin: 0 auto;
      min-height: 100vh;
      min-height: 100dvh;
      height: 100dvh;
      padding: 0;
      display: grid;
      gap: 0;
      grid-template-rows: minmax(88px, var(--pq-nav-height)) minmax(0, 1fr) minmax(132px, var(--pq-footer-height));
    }

    .public-hero {
      display: flex;
      justify-content: space-between;
      align-items: center;
      width: 100%;
      min-width: 0;
      padding-inline: clamp(10px, 2vw, 18px);
      height: 100%;
      border: 0;
      border-bottom: 1px solid var(--pq-border);
      box-shadow: none;
    }

    .public-hero-brand {
      flex: 1 1 auto;
      min-width: 0;
      height: 100%;
      display: flex;
      align-items: stretch;
      align-self: stretch;
    }

    .public-badge,
    .public-status,
    .panel-eyebrow {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      width: fit-content;
      padding: 7px 12px;
      border-radius: 999px;
      background: var(--pq-accent-soft);
      border: 1px solid #cfe2d6;
      color: var(--pq-accent);
      font-size: 12px;
      font-weight: 800;
      letter-spacing: .04em;
      text-transform: uppercase;
    }

    .public-brand {
      display: flex;
      flex: 1 1 auto;
      align-items: center;
      gap: clamp(10px, 1.5vw, 16px);
      padding: clamp(8px, 1.4vw, 14px) clamp(12px, 2vw, 20px);
      border-radius: 0;
      background: linear-gradient(135deg, #6c7f22 0%, #8a9f2e 100%);
      box-shadow: none;
      width: 100%;
      height: 100%;
      min-height: 0;
      min-width: 0;
      align-self: stretch;
      justify-content: center;
      flex-wrap: wrap;
    }

    .public-brand-logo {
      width: clamp(38px, 4vw, 52px);
      height: clamp(38px, 4vw, 52px);
      object-fit: contain;
      flex-shrink: 0;
    }

    .public-brand-text {
      min-width: 0;
      display: grid;
      line-height: 1.05;
      text-align: center;
      gap: 4px;
    }

    .public-brand-title {
      color: #fff9db;
      font-size: clamp(.95rem, 2vw, 1.45rem);
      font-weight: 800;
      letter-spacing: .03em;
      text-transform: uppercase;
      text-wrap: balance;
    }

    .public-brand-subtitle {
      color: rgba(255,255,255,.88);
      font-size: .72rem;
      font-weight: 700;
      letter-spacing: .14em;
      text-transform: uppercase;
      margin-top: 3px;
    }

    .public-title {
      margin: 8px 0 0;
      font-size: clamp(1.45rem, 2.1vw, 2rem);
      font-weight: 800;
      letter-spacing: -.03em;
    }

    .public-subtitle {
      margin: 8px 0 0;
      max-width: 760px;
      color: var(--pq-muted);
      line-height: 1.45;
      font-size: 12px;
    }

    .public-hero-side {
      min-width: clamp(220px, 24vw, 320px);
      display: flex;
      align-items: center;
      justify-content: flex-end;
      gap: 14px;
      flex-wrap: nowrap;
      padding: clamp(10px, 1.5vw, 14px);
      border-radius: 0;
      background: rgba(255,255,255,.8);
      border: 0;
      border-left: 1px solid var(--pq-border);
      box-shadow: none;
      height: 100%;
    }

    .public-office-inline {
      display: flex;
      align-items: center;
      justify-content: flex-end;
      gap: 8px;
      width: 100%;
      min-width: 0;
      flex: 0 1 auto;
    }

    .public-status-dot {
      width: 8px;
      height: 8px;
      border-radius: 999px;
      background: #22c55e;
      box-shadow: 0 0 0 0 rgba(34,197,94,.4);
      animation: pulse 2s infinite;
    }

    .hero-side-logo-wrap {
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      width: 100%;
      gap: 10px;
      padding-bottom: 6px;
      min-width: 0;
    }

    .hero-side-logo {
      width: min(100%, clamp(120px, 18vw, 240px));
      height: auto;
      aspect-ratio: 1 / 1;
      object-fit: contain;
      animation: horizontalFlip 3.2s ease-in-out infinite;
      transform-style: preserve-3d;
      backface-visibility: hidden;
      filter: drop-shadow(0 10px 16px rgba(21, 115, 71, 0.18));
    }

    .hero-side-meta {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-height: 36px;
      padding: 8px 14px;
      border-radius: 999px;
      background: rgba(255,255,255,.92);
      border: 1px solid var(--pq-border);
      color: var(--pq-muted);
      font-size: 11px;
      font-weight: 700;
      text-align: center;
      max-width: 100%;
      white-space: normal;
    }

    .office-display-label {
      font-size: 12px;
      font-weight: 700;
      color: var(--pq-text);
      margin-bottom: 0;
      display: block;
      white-space: nowrap;
    }

    .office-display-box {
      width: auto;
      min-width: min(180px, 100%);
      max-width: 100%;
      min-height: 38px;
      border-radius: 14px;
      border: 1px solid #d8e6dd;
      padding: 8px 10px;
      color: var(--pq-text);
      background: #fff;
      font-weight: 700;
      line-height: 1.4;
      font-size: 13px;
      white-space: normal;
      text-align: center;
    }

    .muted-copy {
      white-space: nowrap;
      display: inline-flex;
      align-items: center;
      height: 100%;
    }

    .public-grid {
      display: grid;
      grid-template-columns: minmax(0, 1fr) minmax(240px, 26vw);
      align-items: stretch;
      gap: 0;
      min-height: 0;
      background: rgba(255,255,255,.78);
    }

    .public-grid .public-hero-side {
      min-width: 0;
      width: 100%;
      justify-content: center;
      gap: 12px;
      border-left: 1px solid var(--pq-border);
      border-top: 0;
      background: rgba(248, 252, 249, .92);
      padding: 16px 18px;
    }

    .pending-row {
      width: 100%;
      min-height: 0;
      background: linear-gradient(180deg, #1e6a36 0%, #15582d 100%);
    }

    .panel-card {
      background: transparent;
      border: 0;
      border-radius: 0;
      box-shadow: none;
      overflow: hidden;
      height: 100%;
      display: flex;
      flex-direction: column;
    }

    .panel-head {
      display: flex;
      justify-content: space-between;
      gap: 12px;
      align-items: center;
      padding: clamp(10px, 1.6vw, 14px) clamp(12px, 2vw, 20px) clamp(8px, 1.2vw, 10px);
      border-bottom: 1px solid #edf3ef;
      flex-shrink: 0;
      background: transparent;
    }

    .panel-head h2,
    .panel-head h3 {
      margin: 4px 0 0;
      font-size: 1rem;
      font-weight: 800;
    }

    .panel-head-inline {
      display: flex;
      align-items: center;
      gap: 12px;
      width: 100%;
      min-width: 0;
      flex-wrap: nowrap;
    }

    .panel-head-inline h2 {
      margin: 0;
      white-space: nowrap;
    }

    .panel-head-inline .public-office-inline {
      margin-left: auto;
      width: auto;
      flex: 0 0 auto;
    }

    .panel-icon {
      width: 38px;
      height: 38px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 12px;
      background: var(--pq-accent-soft);
      border: 1px solid #d7eadc;
      color: var(--pq-accent);
      font-size: 15px;
      flex-shrink: 0;
    }

    .panel-body {
      padding: clamp(10px, 1.6vw, 14px) clamp(12px, 2vw, 20px) clamp(12px, 1.8vw, 16px);
      min-height: 0;
      flex: 1;
    }

    .window-list {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(min(220px, 100%), 1fr));
      gap: clamp(8px, 1.2vw, 12px);
      height: 100%;
      align-content: start;
      overflow: auto;
      min-width: 0;
    }

    .window-card {
      border: 0;
      border-left: 4px solid var(--pq-accent);
      border-radius: 12px;
      padding: clamp(10px, 1.4vw, 14px);
      background: rgba(255,255,255,.55);
      box-shadow: none;
      display: grid;
      gap: 6px;
      min-height: 0;
      min-width: 0;
    }

    .window-top {
      display: flex;
      justify-content: space-between;
      gap: 12px;
      align-items: flex-start;
    }

    .window-no {
      font-size: .88rem;
      font-weight: 800;
    }

    .window-state {
      padding: 3px 6px;
      border-radius: 999px;
      background: #f6faf7;
      border: 1px solid #dce9e0;
      color: var(--pq-muted);
      font-size: 9px;
      font-weight: 700;
    }

    .queue-code {
      font-size: clamp(1rem, 1.6vw, 1.12rem);
      font-weight: 800;
      letter-spacing: -.03em;
      color: var(--pq-accent);
      word-break: break-word;
    }

    .queue-name {
      font-size: 12px;
      font-weight: 700;
      color: var(--pq-text);
    }

    .queue-meta,
    .muted-copy {
      color: var(--pq-muted);
      font-size: 11px;
      line-height: 1.45;
    }

    .waiting-state {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-height: 44px;
      border-radius: 0;
      border: 1px dashed #d7e5db;
      background: #f8fbf9;
      color: var(--pq-muted);
      font-size: 10px;
      font-weight: 700;
      text-align: center;
      padding: 6px;
    }

    .service-wrap,
    .pending-services {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
    }

    .service-chip {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 4px 8px;
      border-radius: 999px;
      background: #eef7f1;
      border: 1px solid #d9ebdf;
      color: #185135;
      font-size: 10px;
      font-weight: 700;
    }

    .soft-badge {
      padding: 2px 6px;
      border-radius: 999px;
      background: rgba(37,99,235,.12);
      color: #2563eb;
      font-size: 10px;
      font-weight: 800;
    }

    .pending-panel {
      background: linear-gradient(180deg, #1e6a36 0%, #15582d 100%);
      color: #fff;
    }

    .pending-panel .panel-head {
      border-bottom-color: rgba(255,255,255,.1);
    }

    .pending-panel .panel-eyebrow {
      background: rgba(255,255,255,.14);
      border-color: rgba(255,255,255,.18);
      color: #ecfdf3;
    }

    .pending-panel .panel-icon {
      background: rgba(255,255,255,.14);
      border-color: rgba(255,255,255,.18);
      color: #fff;
    }

    .pending-list {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(min(120px, 100%), 1fr));
      gap: clamp(8px, 1.2vw, 12px);
      height: 100%;
      align-content: start;
      overflow: auto;
      min-width: 0;
    }

    .pending-item {
      padding: clamp(8px, 1.2vw, 12px);
      border-radius: 10px;
      background: rgba(255,255,255,.03);
      border: 1px solid rgba(255,255,255,.10);
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 44px;
      text-align: center;
    }

    .pending-code {
      font-size: clamp(.82rem, 1.3vw, .9rem);
      font-weight: 800;
      letter-spacing: -.02em;
      word-break: break-word;
    }

    .empty-state {
      padding: 12px;
      text-align: center;
      border-radius: 0;
      border: 1px dashed #d7e5db;
      background: #f9fcfa;
      color: var(--pq-muted);
      font-weight: 600;
      font-size: 11px;
    }

    .pending-panel .empty-state {
      background: rgba(255,255,255,.08);
      border-color: rgba(255,255,255,.18);
      color: rgba(255,255,255,.82);
    }

    .footer-note {
      text-align: center;
      color: var(--pq-muted);
      font-size: 13px;
      padding-bottom: 10px;
    }

    @keyframes pulse {
      0% { box-shadow: 0 0 0 0 rgba(34,197,94,.4); }
      70% { box-shadow: 0 0 0 10px rgba(34,197,94,0); }
      100% { box-shadow: 0 0 0 0 rgba(34,197,94,0); }
    }

    @keyframes horizontalFlip {
      0%, 100% { transform: rotateY(0deg); }
      50% { transform: rotateY(180deg); }
    }

    @media (max-width: 992px) {
      :root {
        --pq-nav-height: auto;
        --pq-footer-height: auto;
      }

      body {
        overflow-y: auto;
      }

      .public-shell {
        height: auto;
        min-height: 100dvh;
        grid-template-rows: auto minmax(0, 1fr) auto;
      }

      .public-grid {
        grid-template-columns: 1fr;
      }

      .public-hero {
        align-items: stretch;
        padding: 12px 14px;
      }

      .public-hero-brand {
        height: auto;
      }

      .public-brand,
      .public-hero-side {
        height: auto;
      }

      .public-hero-side {
        min-width: 0;
        width: 100%;
        border-left: 0;
        border-top: 1px solid var(--pq-border);
        justify-content: center;
        flex-wrap: wrap;
        min-height: 180px;
      }

      .public-grid .public-hero-side {
        border-top: 1px solid var(--pq-border);
        border-left: 0;
        padding: 18px 16px;
      }

      .panel-head,
      .panel-head-inline {
        flex-wrap: wrap;
      }

      .panel-head-inline .public-office-inline {
        margin-left: 0;
        width: 100%;
        justify-content: flex-start;
        flex: 1 1 100%;
      }
    }

    @media (max-width: 768px) {
      .public-shell {
        grid-template-rows: auto auto auto;
      }

      .public-hero,
      .panel-head,
      .panel-body {
        padding-left: 12px;
        padding-right: 12px;
      }

      .public-hero {
        padding-top: 10px;
        padding-bottom: 10px;
      }

      .public-brand {
        width: 100%;
        padding: 10px 12px;
        flex-direction: column;
        text-align: center;
      }

      .public-brand-title {
        font-size: 1rem;
      }

      .hero-side-logo {
        width: min(160px, 52vw);
      }

      .window-list,
      .pending-list {
        grid-template-columns: 1fr;
      }

      .panel-head-inline {
        align-items: flex-start;
        flex-wrap: wrap;
      }

      .panel-head-inline .public-office-inline {
        margin-left: 0;
        width: 100%;
        flex: 1 1 100%;
        justify-content: flex-start;
        gap: 10px;
      }

      .public-office-inline {
        flex-direction: column;
        align-items: flex-start;
      }

      .office-display-box {
        width: 100%;
      }

      .pending-row {
        min-height: 180px;
      }
    }

    @media (max-width: 576px) {
      .public-hero {
        padding-inline: 10px;
      }

      .public-brand {
        padding: 10px;
        gap: 8px;
      }

      .public-brand-logo {
        width: 34px;
        height: 34px;
      }

      .public-brand-title {
        font-size: .88rem;
        letter-spacing: .02em;
      }

      .panel-icon {
        width: 34px;
        height: 34px;
        border-radius: 10px;
      }

      .window-card,
      .pending-item {
        border-radius: 10px;
      }
    }

    @media (max-height: 900px) {
      :root {
        --pq-nav-height: clamp(80px, 10vh, 96px);
        --pq-footer-height: clamp(120px, 20vh, 156px);
      }

      .public-hero {
        padding: 8px 14px;
      }

      .panel-head {
        padding: 10px 14px 8px;
      }

      .panel-body {
        padding: 10px 14px 12px;
      }
    }

    @media (max-height: 760px) {
      :root {
        --pq-nav-height: clamp(74px, 9vh, 88px);
        --pq-footer-height: clamp(108px, 18vh, 136px);
      }

      .public-brand-logo {
        width: 34px;
        height: 34px;
      }

      .public-brand-title {
        font-size: .95rem;
      }

      .public-brand-subtitle {
        font-size: .6rem;
      }

      .hero-side-logo {
        width: min(120px, 20vh);
      }

      .window-list {
        grid-template-columns: repeat(auto-fit, minmax(min(160px, 100%), 1fr));
      }

      .pending-list {
        grid-template-columns: repeat(auto-fit, minmax(min(108px, 100%), 1fr));
      }
    }
  </style>
</head>
<body>
  <div class="public-shell" id="publicQueuesRoot" data-office="<?php echo $defaultOffice; ?>">
    <section class="public-hero">
      <div class="public-hero-brand">
        <div class="public-brand">
          <img src="resources/ucc.png" alt="UCC Logo" class="public-brand-logo">
          <div class="public-brand-text">
            <span class="public-brand-title">University of Caloocan City</span>
          </div>
        </div>
      </div>
    </section>

    <section class="public-grid">
      <div class="panel-card">
        <div class="panel-head">
          <div class="panel-head-inline">
            <div class="panel-eyebrow">Active Windows</div>
            <!-- <h2>Currently Serving</h2> -->
            <div class="public-office-inline">
              <label class="office-display-label">Current Office</label>
              <div id="officeName" class="office-display-box">Loading office…</div>
            </div>
          </div>
          <div class="panel-icon"><i class="fa-solid fa-display"></i></div>
        </div>
        <div class="panel-body">
          <div id="windowList" class="window-list"></div>
        </div>
      </div>
      <div class="public-hero-side">
        <div class="hero-side-logo-wrap">
          <img src="resources/ucc.png" alt="UCC Logo" class="hero-side-logo">
          <div id="lastUpdated" class="hero-side-meta">Time: --:--:--</div>
        </div>
      </div>
    </section>

    <section class="pending-row">
      <div class="panel-card pending-panel">
        <div class="panel-head">
          <div>
            <div class="panel-eyebrow">Waiting Queues</div>
          </div>
          <div class="panel-icon"><i class="fa-solid fa-hourglass-half"></i></div>
        </div>
        <div class="panel-body">
          <div id="pendingList" class="pending-list"></div>
        </div>
      </div>
    </section>

    <!-- <div class="footer-note">Access this page directly at <strong>/linya/publicQueues.php</strong>.</div> -->
  </div>

  <script>
    (function () {
      const api = 'backend/officeQueueData.php';
      const root = document.getElementById('publicQueuesRoot');
      const officeName = document.getElementById('officeName');
      const windowList = document.getElementById('windowList');
      const pendingList = document.getElementById('pendingList');
      const lastUpdated = document.getElementById('lastUpdated');

      if (!root || !officeName || !windowList || !pendingList) {
        return;
      }

      let office = root ? parseInt(root.getAttribute('data-office'), 10) || 1 : 1;
      let officesCache = [];
      let currentOfficeIndex = 0;
      let countdownInterval = null;
      let rotateTimeout = null;
      let clockInterval = null;

      function escapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, function (character) {
          return {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;'
          }[character];
        });
      }

      function renderServices(services, isPending) {
        if (!services || !services.length) return '';
        return `<div class="${isPending ? 'pending-services' : 'service-wrap'}">` + services.map(service => {
          return `<span class="service-chip">${escapeHtml(service.service_description || '')}${service.soft_copy ? '<span class="soft-badge">PDF</span>' : ''}</span>`;
        }).join('') + '</div>';
      }

      function renderWindows(windows) {
        const enabledWindows = (windows || []).filter(windowItem => parseInt(windowItem.enabled, 10) === 1);

        if (!enabledWindows.length) {
          windowList.innerHTML = '<div class="empty-state">No active windows available for this office.</div>';
          return;
        }

        windowList.innerHTML = enabledWindows.map(windowItem => {
          const currentQueue = windowItem.current_queue;
          return `
            <article class="window-card">
              <div class="window-top">
                <div class="window-no">Window ${escapeHtml(windowItem.window_no)}</div>
                <div class="window-state">${currentQueue ? 'Serving now' : 'Waiting'}</div>
              </div>
              ${currentQueue ? `
                <div class="queue-code">${escapeHtml(currentQueue.queue_code || '—')}</div>
                <div class="queue-name">${escapeHtml(currentQueue.customer_name || 'Walk-in client')}</div>
                <div class="queue-meta">${escapeHtml(currentQueue.customer_email || 'No email provided')}</div>
                ${renderServices(currentQueue.services || [], false)}
              ` : '<div class="waiting-state">No ongoing queue on this window</div>'}
            </article>
          `;
        }).join('');
      }

      function renderPending(pending) {
        if (!pending || !pending.length) {
          pendingList.innerHTML = '<div class="empty-state">No pending queues right now.</div>';
          return;
        }

        pendingList.innerHTML = pending.map(queueItem => {
          return `
            <article class="pending-item">
              <div class="pending-code">${escapeHtml(queueItem.queue_code || '—')}</div>
            </article>
          `;
        }).join('');
      }

      function updateOfficeName() {
        if (!officeName) return;
        const currentOffice = officesCache.find(item => parseInt(item.office_id, 10) === office);
        officeName.textContent = currentOffice ? currentOffice.office_description : 'Unknown office';
      }

      function updateLastUpdated(message) {
        if (lastUpdated) {
          lastUpdated.textContent = message;
        }
      }

      function updateRealtimeClock() {
        updateLastUpdated(`Real time: ${new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' })}`);
      }

      function startRealtimeClock() {
        if (!lastUpdated) return;
        updateRealtimeClock();
        clearInterval(clockInterval);
        clockInterval = setInterval(updateRealtimeClock, 1000);
      }

      function syncOffices(offices) {
        if (!Array.isArray(offices) || !offices.length) return;

        officesCache = offices;
        const foundIndex = officesCache.findIndex(item => parseInt(item.office_id, 10) === office);

        if (foundIndex >= 0) {
          currentOfficeIndex = foundIndex;
        } else {
          currentOfficeIndex = 0;
          office = parseInt(officesCache[0].office_id, 10) || office;
          root.setAttribute('data-office', String(office));
        }

        updateOfficeName();
      }

      function startRotation(durationSeconds) {
        clearInterval(countdownInterval);
        clearTimeout(rotateTimeout);

        countdownInterval = setInterval(() => {
        }, 1000);

        rotateTimeout = setTimeout(() => {
          clearInterval(countdownInterval);
          moveToNextOffice();
        }, durationSeconds * 1000);
      }

      function moveToNextOffice() {
        if (!officesCache.length) return;
        currentOfficeIndex = (currentOfficeIndex + 1) % officesCache.length;
        office = parseInt(officesCache[currentOfficeIndex].office_id, 10) || office;
        root.setAttribute('data-office', String(office));
        loadState();
      }

      async function loadState() {
        try {
          const response = await fetch(`${api}?office=${encodeURIComponent(office)}`, {
            credentials: 'same-origin'
          });
          if (!response.ok) throw new Error('Failed to load queue state');
          const json = await response.json();
          if (!json || json.success === false) throw new Error((json && json.message) || 'Unable to fetch queue state');

          syncOffices(json.offices || []);
          renderWindows(json.windows || []);
          renderPending(json.pending || []);

          const hasActiveWindowQueue = (json.windows || []).some(windowItem => {
            return parseInt(windowItem.enabled, 10) === 1 && !!windowItem.current_queue;
          });
          const hasPendingQueues = Array.isArray(json.pending) && json.pending.length > 0;
          const rotationDuration = (hasActiveWindowQueue || hasPendingQueues) ? 10 : 5;

          updateOfficeName();
          startRotation(rotationDuration);
        } catch (error) {
          console.error(error);
          clearInterval(countdownInterval);
          clearTimeout(rotateTimeout);
          windowList.innerHTML = '<div class="empty-state">Unable to load active windows.</div>';
          pendingList.innerHTML = '<div class="empty-state">Unable to load pending queues.</div>';
        }
      }

      startRealtimeClock();
      loadState();
    })();
  </script>
</body>
</html>
