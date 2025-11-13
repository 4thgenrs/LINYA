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

  function setView(name) {
    Object.keys(views).forEach(k => {
      views[k].style.display = (k === name) ? '' : 'none';
    });
    navBtns.forEach(b => b.classList.toggle('active', b.dataset.view === name));
    // when switching to window control ensure iframe uses session queueSelect (no reload required)
    if (name === 'window' && windowFrame && windowFrame.src.indexOf('queueSelect.php') === -1) {
      windowFrame.src = '../includes/queueSelect.php';
    }
  }

  navBtns.forEach(b => b.addEventListener('click', () => setView(b.dataset.view)));

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
    // windows
    const windows = data.windows || [];
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