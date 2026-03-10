// Dashboard logic: fetch backend/dashboardData.php and render activity + office queue; also nav + logout
(function(){
  const api = '../backend/dashboardData.php';
  const pollInterval = 3000;
  const logoutBtn = document.getElementById('logoutBtn');
  const navBtns = Array.from(document.querySelectorAll('.nav-btn'));
  const mobileToggle = document.getElementById('mobileToggle');
  const sidebar = document.getElementById('sidebar');
  const body = document.body;
  const views = {
    activity: document.getElementById('view-activity'),
    window: document.getElementById('view-window'),
    office: document.getElementById('view-office')
  };
  const activityTable = document.getElementById('activityTable');
  const activityTotalServed = document.getElementById('activityTotalServed');
  const activityServicesHandled = document.getElementById('activityServicesHandled');
  const activityLatestCompletion = document.getElementById('activityLatestCompletion');
  const activityLatestCustomer = document.getElementById('activityLatestCustomer');
  const activityTotalServedCard = activityTotalServed ? activityTotalServed.closest('.activity-summary-card') : null;
  const activityServicesHandledCard = activityServicesHandled ? activityServicesHandled.closest('.activity-summary-card') : null;
  const activityLatestCompletionCard = activityLatestCompletion ? activityLatestCompletion.closest('.activity-summary-card') : null;
  let dataTable = null;
  const officeQueueContainer = document.getElementById('officeQueueContainer');
  const officePending = document.getElementById('officePending');
  const officeLastUpdated = document.getElementById('officeLastUpdated');
  const windowFrame = document.getElementById('windowFrame');
  const assignedOfficeEl = document.getElementById('assignedOffice');
  let lastOfficeWindowsSignature = '';
  let lastOfficePendingSignature = '';
  let officeLastUpdatedAt = null;
  let activitySummaryState = {
    totalServed: 0,
    servicesHandled: 0,
    latestCompletion: '—',
    latestCustomer: 'Waiting for activity data'
  };

  function setView(name) {
    // prevent opening window control if user is not assigned
    if (name === 'window' && !canControl) {
      alert('You are not assigned to a window. Window control is disabled.');
      return;
    }

    Object.keys(views).forEach(k => {
      views[k].style.display = (k === name) ? '' : 'none';
    });
    navBtns.forEach(b => b.classList.toggle('active', b.dataset.view === name));
    const url = new URL(window.location.href);
    url.searchParams.set('view', name);
    window.history.replaceState({}, '', url.toString());
    // when switching to window control ensure iframe uses session queueSelect (no reload required)
    if (name === 'window' && windowFrame && windowFrame.src.indexOf('queueSelect.php') === -1) {
      windowFrame.src = '../includes/queueSelect.php';
    }
  }

  // keep a flag whether user can use window control (seeded by PHP, refreshed by API)
  let canControl = !!(window.__LINYA_SESSION && window.__LINYA_SESSION.can_control);
  // whether the user can view the live office queue (assigned to an office)
  let canViewOffice = !!(window.__LINYA_SESSION && window.__LINYA_SESSION.can_view_office);

  navBtns.forEach(b => b.addEventListener('click', () => {
    const view = b.dataset.view;
    if (view === 'window' && !canControl) {
      alert('You are not assigned to a window. Window control is disabled.');
      return;
    }
    if (view === 'office' && !canViewOffice) {
      alert('You are not assigned to an office. Live Office Queue is disabled.');
      return;
    }
    setView(view);
    if (window.innerWidth <= 991.98 && sidebar) {
      sidebar.classList.remove('show');
      body.classList.remove('sidebar-open');
    }
  }));

  async function fetchState() {
    try {
      const res = await fetch(api, { credentials: 'same-origin' });
      if (!res.ok) {
        if (res.status === 401) { window.location.href = 'login.php'; return null; }
        throw new Error('Server error');
      }
      return await res.json();
    } catch (e) {
      console.error('dashboard fetch error', e);
      return null;
    }
  }

  function renderActivity(rows) {
    if (!activityTable) return;

    const safeRows = Array.isArray(rows) ? rows : [];
    const totalServed = safeRows.length;
    const servicesHandled = safeRows.reduce((total, row) => {
      const rawServices = String(row && row.services ? row.services : '');
      const items = rawServices.split(',').map(service => service.trim()).filter(Boolean);
      return total + items.length;
    }, 0);
    const latestRow = safeRows[0] || null;
    const latestCompletionText = latestRow && latestRow.finished_at ? latestRow.finished_at : '—';
    const latestCustomerText = latestRow
      ? ((latestRow.customer_name || 'Unknown customer') + (latestRow.queue_code ? ` • ${latestRow.queue_code}` : ''))
      : 'Waiting for activity data';

    animateMetricValue(activityTotalServed, activitySummaryState.totalServed, totalServed);
    animateMetricValue(activityServicesHandled, activitySummaryState.servicesHandled, servicesHandled);

    if (activityLatestCompletion && activitySummaryState.latestCompletion !== latestCompletionText) {
      activityLatestCompletion.textContent = latestCompletionText;
      flashSummaryCard(activityLatestCompletionCard);
    }

    if (activityLatestCustomer) {
      activityLatestCustomer.textContent = latestCustomerText;
    }

    activitySummaryState = {
      totalServed,
      servicesHandled,
      latestCompletion: latestCompletionText,
      latestCustomer: latestCustomerText
    };
    
    if (!safeRows.length) {
      if (dataTable) {
        dataTable.clear().draw();
      }
      return;
    }

    const tableData = safeRows.map(r => [
      `<span class="queue-code-badge">${escapeHtml(r.queue_code || '—')}</span>`,
      `<div class="activity-service-cell">${(r.services || '—').split(',').map(service => `<span class="activity-service-chip">${escapeHtml(service.trim())}</span>`).join('')}</div>`,
      `<div class="activity-customer-cell"><div class="activity-customer-name">${escapeHtml(r.customer_name || '—')}</div><div class="activity-customer-sub">Served customer</div></div>`,
      `<div class="activity-contact-cell">
          ${r.customer_phone ? `<div><i class="fa-solid fa-phone me-2"></i>${escapeHtml(r.customer_phone)}</div>` : ''}
          ${r.customer_email ? `<div><i class="fa-solid fa-envelope me-2"></i>${escapeHtml(r.customer_email)}</div>` : ''}
          ${!r.customer_phone && !r.customer_email ? '<span class="table-meta">—</span>' : ''}
       </div>`,
      `<span class="activity-window-pill"><i class="fa-solid fa-desktop me-2"></i>Window ${escapeHtml(String(r.window_id || r.window_no || '—'))}</span>`,
      `<div class="activity-finished-cell"><div class="activity-finished-time">${escapeHtml(r.finished_at || '—')}</div><div class="activity-finished-sub">Completed</div></div>`
    ]);

    if (dataTable) {
      dataTable.clear().rows.add(tableData).draw();
    } else {
      // Initialize DataTable
      dataTable = $(activityTable).DataTable({
        data: tableData,
        responsive: true,
        pageLength: 10,
        order: [[5, 'desc']], // Sort by finished_at descending
        language: {
          search: "Search:",
          lengthMenu: "Show _MENU_ entries",
          info: "Showing _START_ to _END_ of _TOTAL_ entries",
          paginate: {
            first: "First",
            last: "Last",
            next: "Next",
            previous: "Previous"
          }
        }
      });
    }
  }

  function animateMetricValue(element, previousValue, nextValue) {
    if (!element) return;
    const startValue = Number.isFinite(previousValue) ? previousValue : 0;
    const endValue = Number.isFinite(nextValue) ? nextValue : 0;
    const summaryCard = element.closest('.activity-summary-card');

    if (startValue === endValue) {
      element.textContent = String(endValue);
      return;
    }

    if (element._linyaAnimationFrame) {
      cancelAnimationFrame(element._linyaAnimationFrame);
    }

    const duration = 550;
    const startedAt = performance.now();
    flashSummaryCard(summaryCard);

    const step = (now) => {
      const progress = Math.min((now - startedAt) / duration, 1);
      const eased = 1 - Math.pow(1 - progress, 3);
      const currentValue = Math.round(startValue + ((endValue - startValue) * eased));
      element.textContent = String(currentValue);

      if (progress < 1) {
        element._linyaAnimationFrame = requestAnimationFrame(step);
      } else {
        element.textContent = String(endValue);
        element._linyaAnimationFrame = null;
      }
    };

    element._linyaAnimationFrame = requestAnimationFrame(step);
  }

  function flashSummaryCard(card) {
    if (!card) return;
    card.classList.remove('is-flash');
    void card.offsetWidth;
    card.classList.add('is-flash');

    if (card._linyaFlashTimeout) {
      clearTimeout(card._linyaFlashTimeout);
    }

    card._linyaFlashTimeout = setTimeout(() => {
      card.classList.remove('is-flash');
      card._linyaFlashTimeout = null;
    }, 700);
  }

  function renderOffice(data) {
    if (!data) {
      officeQueueContainer.innerHTML = 'No office data';
      officePending.innerHTML = '';
      lastOfficeWindowsSignature = '';
      lastOfficePendingSignature = '';
      return;
    }
    // filter out disabled windows
    const windows = (data.windows || []).filter(w => parseInt(w.enabled, 10) === 1);
    const pending = data.pending || [];
    const windowsSignature = JSON.stringify(windows);
    const pendingSignature = JSON.stringify(pending);
    if (windowsSignature !== lastOfficeWindowsSignature) {
      const wrap = document.createElement('div');
      wrap.className = 'office-windows';

      windows.forEach(w => {
        const card = document.createElement('div');
        card.className = 'window-card';
        const top = document.createElement('div');
        const assignedLabel = w.assigned_staff_id ? `Staff ID: ${escapeHtml(String(w.assigned_staff_id))}` : 'Open';
        const stateLabel = w.current_queue ? 'Serving' : 'Waiting';
        top.className = 'window-card-top';
        top.innerHTML = `
          <div>
            <strong>Window ${escapeHtml(String(w.window_no))}</strong>
            <div class="window-card-meta">${assignedLabel}</div>
          </div>
          <span class="window-state-pill ${w.current_queue ? 'is-busy' : 'is-idle'}">${stateLabel}</span>
        `;
        card.appendChild(top);
        if (w.current_queue) {
          const q = w.current_queue;
          const code = document.createElement('div');
          code.className = 'queue-code';
          code.textContent = q.queue_code;
          const name = document.createElement('div');
          name.className = 'window-customer';
          name.textContent = q.customer_name || '';
          const detail = document.createElement('div');
          detail.className = 'window-card-detail';
          detail.innerHTML = `<i class="fa-solid fa-user me-2"></i>${escapeHtml(q.customer_name || 'Unnamed customer')}`;
          card.appendChild(code);
          card.appendChild(name);
          card.appendChild(detail);
          if (q.services && q.services.length) {
            const svcWrap = document.createElement('div');
            svcWrap.className = 'window-service-wrap';
            q.services.forEach(s => {
              const sp = document.createElement('span');
              sp.className = 'svc-chip';
              sp.textContent = s.service_description + (s.soft_copy? ' (PDF)': '');
              svcWrap.appendChild(sp);
            });
            card.appendChild(svcWrap);
          }
        } else {
          const waiting = document.createElement('div');
          waiting.innerHTML = '<i class="fa-regular fa-clock me-2"></i>Waiting for next queue';
          waiting.className = 'small muted window-empty';
          card.appendChild(waiting);
        }
        wrap.appendChild(card);
      });
      officeQueueContainer.innerHTML = '';
      officeQueueContainer.appendChild(wrap);
      lastOfficeWindowsSignature = windowsSignature;
    }

    if (pendingSignature !== lastOfficePendingSignature) {
      officePending.innerHTML = '';
      if (!pending.length) {
        officePending.innerHTML = '<div class="small muted">No pending queues</div>';
      } else {
        pending.forEach((p, index) => {
          const item = document.createElement('div');
          item.className = 'activity-row';
          item.innerHTML = `
            <div class="pending-item-top">
              <div class="pending-item-title-wrap">
                <span class="pending-rank">#${index + 1}</span>
                <div>
                  <strong class="pending-queue-code">${escapeHtml(p.queue_code)}</strong>
                  <div class="pending-item-sub">Waiting in queue</div>
                </div>
              </div>
              <span class="pending-status-pill">Next Up</span>
            </div>
            <div class="activity-meta pending-customer-name"><i class="fa-solid fa-user me-2"></i>${escapeHtml(p.customer_name||'Unnamed customer')}</div>
          `;
          officePending.appendChild(item);
        });
      }
      lastOfficePendingSignature = pendingSignature;
    }
  }

  function escapeHtml(s){ return String(s||'').replace(/[&<>"']/g, c=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }

  function getRelativeTimeLabel(date) {
    if (!date) return '—';

    const diffSeconds = Math.max(0, Math.floor((Date.now() - date.getTime()) / 1000));
    if (diffSeconds < 60) return `${diffSeconds}s ago`;

    const diffMinutes = Math.floor(diffSeconds / 60);
    if (diffMinutes < 60) return `${diffMinutes}m ago`;

    const diffHours = Math.floor(diffMinutes / 60);
    if (diffHours < 24) return `${diffHours}h ago`;

    const diffDays = Math.floor(diffHours / 24);
    return `${diffDays}d ago`;
  }

  function updateOfficeTimestamp() {
    if (!officeLastUpdated) return;
    const relative = getRelativeTimeLabel(officeLastUpdatedAt);
    const exact = officeLastUpdatedAt
      ? officeLastUpdatedAt.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' })
      : '—';
    officeLastUpdated.innerHTML = `<i class="fa-regular fa-clock me-2"></i>Last updated: ${exact}${officeLastUpdatedAt ? ` (${relative})` : ''}`;
    officeLastUpdated.title = officeLastUpdatedAt ? `Last successful refresh at ${exact}` : 'Awaiting first successful refresh';
  }

  async function refresh() {
    const json = await fetchState();
    if (!json) return;
    if (!json.success) return;
    renderActivity(json.activity || []);
    renderOffice(json.office || {});
    officeLastUpdatedAt = new Date();
    updateOfficeTimestamp();

    // update assigned office in header (use API data first, fallback to session)
    try {
      let officeName = (json.office && json.office.office_description) ? json.office.office_description : (json.user && json.user.office_description ? json.user.office_description : window.__LINYA_SESSION.office_description || '');
      let officeCode = (json.office && json.office.office_code) ? json.office.office_code : (json.user && json.user.office_code ? json.user.office_code : window.__LINYA_SESSION.office_code || '');
      if (assignedOfficeEl) {
        assignedOfficeEl.textContent = officeName ? (officeName + (officeCode ? ' ('+officeCode+')' : '')) : '—';
      }
      // also update browser title to include office
      if (officeName) {
        document.title = 'Dashboard — LINYA' + ' — ' + officeName;
      }
    } catch (e) { /* ignore */ }

    // update window-control availability
    // require both an assigned window and that the account has an `office` set (server-side guard uses accounts.office)
    const accountOffice = json.user ? parseInt(json.user.account_office || json.user.office || 0, 10) : 0;
    canControl = !!(json.user && json.user.assigned_window_id && parseInt(json.user.assigned_window_id, 10) > 0 && accountOffice > 0);
    const windowBtn = navBtns.find(n => n.dataset.view === 'window');
    if (windowBtn) {
      if (!canControl) {
        // hide the tab entirely when user is not allowed to control windows
        windowBtn.style.display = 'none';
      } else {
        windowBtn.style.display = '';
        windowBtn.removeAttribute('disabled');
        windowBtn.title = '';
        windowBtn.classList.remove('muted');
      }
    }

    // update live office queue availability: require assigned office OR account/session office
    const sessOffice = json.user ? parseInt(json.user.office || 0, 10) : 0;
    const assignedOffice = json.user ? parseInt(json.user.assigned_office_id || 0, 10) : 0;
    canViewOffice = !!(json.user && (assignedOffice > 0 || accountOffice > 0 || sessOffice > 0));
    const officeBtn = navBtns.find(n => n.dataset.view === 'office');
    if (officeBtn) {
      if (!canViewOffice) {
        // hide the Live Office Queue tab when user is not assigned to an office
        officeBtn.style.display = 'none';
      } else {
        officeBtn.style.display = '';
        officeBtn.removeAttribute('disabled');
        officeBtn.title = '';
        officeBtn.classList.remove('muted');
      }
    }
  }

  // logout centralized
  if (logoutBtn) {
    logoutBtn.addEventListener('click', async () => {
      if (!confirm('Log out?')) return;
      try {
        const r = await fetch('../backend/logout.php', { method: 'POST', credentials: 'same-origin' });
        const j = await r.json();
        if (j && j.success) {
          window.location.href = 'login.php';
        } else {
          alert('Logout failed');
        }
      } catch (e) { console.error(e); alert('Logout error'); }
    });
  }

  if (mobileToggle && sidebar) {
    mobileToggle.addEventListener('click', () => {
      sidebar.classList.toggle('show');
      body.classList.toggle('sidebar-open');
    });

    body.addEventListener('click', (e) => {
      if (body.classList.contains('sidebar-open') && !sidebar.contains(e.target) && !mobileToggle.contains(e.target)) {
        sidebar.classList.remove('show');
        body.classList.remove('sidebar-open');
      }
    });
  }

  // initial
  document.addEventListener('DOMContentLoaded', () => {
    setView(window.__LINYA_SESSION.current_view || 'activity');
    refresh();
    setInterval(refresh, pollInterval);
    setInterval(updateOfficeTimestamp, 1000);
    // ensure iframe uses session assigned office/window if needed
    // queueSelect.php will use session values for office/window so no params required
  });
})();  