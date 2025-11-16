// Dashboard logic: fetch backend/dashboardData.php and render activity + office queue; also nav + logout
(function(){
  const api = '../backend/dashboardData.php';
  const pollInterval = 3000;
  const logoutBtn = document.getElementById('logoutBtn');
  const navBtns = Array.from(document.querySelectorAll('.nav-btn'));
  const views = {
    activity: document.getElementById('view-activity'),
    window: document.getElementById('view-window'),
    office: document.getElementById('view-office')
  };
  const activityContainer = document.getElementById('activityContainer');
  const officeQueueContainer = document.getElementById('officeQueueContainer');
  const officePending = document.getElementById('officePending');
  const windowFrame = document.getElementById('windowFrame');
  const assignedOfficeEl = document.getElementById('assignedOffice');

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
    // when switching to window control ensure iframe uses session queueSelect (no reload required)
    if (name === 'window' && windowFrame && windowFrame.src.indexOf('queueSelect.php') === -1) {
      windowFrame.src = '../includes/queueSelect.php';
    }
  }

  // keep a flag whether user can use window control (set by API)
  let canControl = false;
  // whether the user can view the live office queue (assigned to an office)
  let canViewOffice = false;

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
    if (!rows || !rows.length) {
      activityContainer.innerHTML = '<div class="activity-row small-muted">No processed transactions</div>';
      return;
    }
    activityContainer.innerHTML = '';
    rows.forEach(r => {
      const el = document.createElement('div');
      el.className = 'activity-row';
      el.innerHTML = `<div><strong class="queue-code">${escapeHtml(r.services || '—')}</strong></div>
        <div class="activity-meta">${escapeHtml(r.customer_name || '—')} • ${escapeHtml(r.customer_phone || '')} • ${escapeHtml(r.customer_email || '')}</div>
        <div class="activity-meta">Finished at: ${escapeHtml(r.finished_at || '')} • Window: ${escapeHtml(String(r.window_id || r.window_no || '—'))}</div>`;
      activityContainer.appendChild(el);
    });
  }

  function renderOffice(data) {
    if (!data) {
      officeQueueContainer.innerHTML = 'No office data';
      officePending.innerHTML = '';
      return;
    }
    // filter out disabled windows
    const windows = (data.windows || []).filter(w => parseInt(w.enabled, 10) === 1);
    const wrap = document.createElement('div');
    wrap.className = 'office-windows';
    windows.forEach(w => {
      const card = document.createElement('div');
      card.className = 'window-card';
      const top = document.createElement('div');
      top.innerHTML = `<div style="display:flex;justify-content:space-between;align-items:center"><div><strong>Window ${w.window_no}</strong></div><div class="small muted">${w.enabled? (w.assigned_staff_id?('Staff ID: '+w.assigned_staff_id):'Open') : 'Disabled'}</div></div>`;
      card.appendChild(top);
      if (w.current_queue) {
        const q = w.current_queue;
        const code = document.createElement('div');
        code.className = 'queue-code';
        code.textContent = q.queue_code;
        const name = document.createElement('div');
        name.textContent = q.customer_name || '';
        card.appendChild(code);
        card.appendChild(name);
        if (q.services && q.services.length) {
          const svcWrap = document.createElement('div');
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
        waiting.textContent = 'Waiting';
        waiting.className = 'small muted';
        card.appendChild(waiting);
      }
      wrap.appendChild(card);
    });
    officeQueueContainer.innerHTML = '';
    officeQueueContainer.appendChild(wrap);

    // pending
    const pending = data.pending || [];
    officePending.innerHTML = '';
    if (!pending.length) {
      officePending.innerHTML = '<div class="small muted">No pending queues</div>';
    } else {
      pending.forEach(p => {
        const item = document.createElement('div');
        item.className = 'activity-row';
        item.innerHTML = `<div><strong>${escapeHtml(p.queue_code)}</strong></div><div class="activity-meta">${escapeHtml(p.customer_name||'')}</div>`;
        officePending.appendChild(item);
      });
    }
  }

  function escapeHtml(s){ return String(s||'').replace(/[&<>"']/g, c=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }

  async function refresh() {
    const json = await fetchState();
    if (!json) return;
    if (!json.success) return;
    renderActivity(json.activity || []);
    renderOffice(json.office || {});

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
    canControl = !!(json.user && json.user.assigned_window_id && parseInt(json.user.assigned_window_id, 10) > 0);
    const windowBtn = navBtns.find(n => n.dataset.view === 'window');
    if (windowBtn) {
      if (!canControl) {
        // hide the tab entirely when user is not assigned to a window
        windowBtn.style.display = 'none';
      } else {
        windowBtn.style.display = '';
        windowBtn.removeAttribute('disabled');
        windowBtn.title = '';
        windowBtn.classList.remove('muted');
      }
    }

    // update live office queue availability: require assigned office OR session office
    canViewOffice = !!(json.user && ((json.user.assigned_office_id && parseInt(json.user.assigned_office_id,10)>0) || (json.user.office && parseInt(json.user.office,10)>0)));
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

  // initial
  document.addEventListener('DOMContentLoaded', () => {
    setView('activity');
    refresh();
    setInterval(refresh, pollInterval);
    // ensure iframe uses session assigned office/window if needed
    // queueSelect.php will use session values for office/window so no params required
  });
})();  