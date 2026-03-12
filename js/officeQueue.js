(function(){
  const api = '../backend/officeQueueData.php';
  const root = document.getElementById('root');
  const windowsContainer = document.getElementById('windowsContainer');
  const pendingList = document.getElementById('pendingList');
  const officeSelect = document.getElementById('officeSelect');
  const pollInterval = 3000;

  // initial office: root dataset or 1
  let office = root ? parseInt(root.getAttribute('data-office'),10) || 1 : 1;

  // remember previous state to detect updates
  let prevWindowsMap = new Map();
  let prevPendingMap = new Map();

  function escapeHtml(s){ return String(s||'').replace(/[&<>"']/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }

  async function fetchState() {
    try {
      const res = await fetch(`${api}?office=${encodeURIComponent(office)}`, { credentials: 'same-origin' });
      if (!res.ok) throw new Error('Network response not ok');
      return await res.json();
    } catch (e) {
      console.error('officeQueue fetch error', e);
      return null;
    }
  }

  function populateOffices(offices) {
    if (!officeSelect || !Array.isArray(offices)) return;
    const prev = parseInt(officeSelect.value,10) || office;
    officeSelect.innerHTML = '';
    offices.forEach(o => {
      const opt = document.createElement('option');
      opt.value = o.office_id;
      opt.textContent = o.office_description;
      officeSelect.appendChild(opt);
    });
    if (prev && officeSelect.querySelector(`option[value="${prev}"]`)) {
      officeSelect.value = prev;
    } else if (root && root.getAttribute('data-office') && officeSelect.querySelector(`option[value="${root.getAttribute('data-office')}"]`)) {
      officeSelect.value = root.getAttribute('data-office');
    }
    office = parseInt(officeSelect.value,10) || office;
    if (root) root.setAttribute('data-office', String(office));
  }

  function renderServices(parent, services) {
    parent.innerHTML = '';
    if (!services || !services.length) return;
    services.forEach(s => {
      const sp = document.createElement('span');
      sp.className = 'svc-chip';
      sp.textContent = s.service_description || '';
      if (s.soft_copy && (s.soft_copy === 1 || s.soft_copy === true)) {
        const b = document.createElement('small');
        b.className = 'soft-badge';
        b.textContent = 'PDF';
        b.title = 'Soft copy available';
        sp.appendChild(b);
      }
      parent.appendChild(sp);
    });
  }

  function renderWindows(windows) {
    // show skeleton if empty and first load
    if (!windows) {
      windowsContainer.innerHTML = '<div class="skel" style="height:20px;border-radius:12px"></div>';
      return;
    }

    // build new map to compare updates
    const newMap = new Map();
    windows.forEach(w => {
      if (!w || parseInt(w.enabled,10) !== 1) return;
      const key = w.window_id ? `id:${w.window_id}` : `no:${w.window_no}`;
      newMap.set(key, w);
    });

    // render
    windowsContainer.innerHTML = '';
    const wrap = document.createElement('div');
    wrap.className = 'office-windows';
    newMap.forEach((w, key) => {
      const card = document.createElement('div');
      card.className = 'window-card';
      card.dataset.key = key;

      const top = document.createElement('div');
      top.className = 'window-top';
      const staffText = w.assigned_staff_name ? ('Staff: ' + escapeHtml(String(w.assigned_staff_name))) : 'Open';
      top.innerHTML = `<div class="window-no">Window ${escapeHtml(String(w.window_no||''))}</div><div class="window-staff">${staffText}</div>`;
      card.appendChild(top);

      // status badge (Standby / Processing)
      const statusBadge = document.createElement('div');
      statusBadge.className = 'window-status';
      card.appendChild(statusBadge);

      const spot = document.createElement('div');
      spot.className = 'queue-spot';

      if (w.current_queue) {
        const q = w.current_queue;
        const code = document.createElement('div');
        code.className = 'queue-code';
        code.textContent = q.queue_code || '';

        const txn = document.createElement('div');
        txn.className = 'queue-transaction';
        let txnText = '';
        if (q.services && q.services.length) {
          txnText = q.services.map(s => s.service_description || '').filter(Boolean).join(', ');
        }
        if (!txnText) txnText = q.customer_name || '';
        txn.textContent = txnText;

        spot.appendChild(code);
        spot.appendChild(txn);

        // status: remain Standby until server marks 'processing'
        statusBadge.textContent = (q.status && q.status === 'processing') ? 'Processing' : 'Standby';
      } else {
        // no current queue: always show Standby
        statusBadge.textContent = 'Standby';
      }

      card.appendChild(spot);
      wrap.appendChild(card);

      // detect changes vs previous
      const prev = prevWindowsMap.get(key);
      const prevCode = prev && prev.current_queue ? (prev.current_queue.queue_code || '') : '';
      const newCode = w.current_queue ? (w.current_queue.queue_code || '') : '';

      // mark updated when queue code changed (including from waiting -> code)
      if (prev && prevCode !== newCode) {
        // add visual update
        requestAnimationFrame(() => {
          card.classList.add('updated','pulse');
          setTimeout(()=>{ card.classList.remove('pulse'); }, 900);
          // keep updated accent briefly then fade
          setTimeout(()=>{ card.classList.remove('updated'); }, 2600);
        });
      }
    });

    windowsContainer.appendChild(wrap);
    // replace prev map
    prevWindowsMap = newMap;
  }

  function renderPending(list) {
    const newPending = new Map();
    (list || []).forEach(p => newPending.set(String(p.queue_id), p));

    // simple render (could diff for animations)
    pendingList.innerHTML = '';
    if (!list || !list.length) {
      pendingList.innerHTML = '<div class="pending-empty">No pending queues</div>';
      prevPendingMap = newPending;
      return;
    }

    list.forEach(p => {
      const item = document.createElement('div');
      item.className = 'pending-item';
      item.innerHTML = `<div class="pending-code">${escapeHtml(p.queue_code)}</div><div class="pending-meta">${escapeHtml(p.customer_name||'')}</div>`;
      const svcWrap = document.createElement('div');
      svcWrap.style.marginTop = '6px';
      renderServices(svcWrap, p.services || []);
      item.appendChild(svcWrap);
      pendingList.appendChild(item);
    });

    // small highlight for newly added pending queues
    newPending.forEach((p, id) => {
      if (!prevPendingMap.has(id)) {
        const el = pendingList.querySelector(`.pending-item:nth-child(${[...newPending.keys()].indexOf(id)+1})`);
        if (el) {
          el.style.transform = 'translateY(-6px)';
          el.style.transition = 'transform .28s ease';
          setTimeout(()=>{ el.style.transform = ''; }, 300);
        }
      }
    });

    prevPendingMap = newPending;
  }

  async function load() {
    // show lightweight skeleton while fetching
    if (!windowsContainer.innerHTML.trim()) {
      windowsContainer.innerHTML = '<div class="skel" style="height:180px;border-radius:12px;margin-bottom:12px"></div>';
      pendingList.innerHTML = '<div class="skel" style="height:80px;border-radius:8px"></div>';
    }

    const json = await fetchState();
    if (!json) {
      windowsContainer.innerHTML = '<div class="muted">Error loading data</div>';
      pendingList.innerHTML = '';
      return;
    }

    if (json.offices && Array.isArray(json.offices)) {
      populateOffices(json.offices);
    }

    // update count badge on select (if present)
    if (officeSelect) {
      const pendingCount = (json.pending && json.pending.length) ? json.pending.length : 0;
      // show badge by setting data attribute (CSS could pick this up)
      officeSelect.dataset.pending = String(pendingCount);
      // small UX: put count visible as last option label if needed
    }

    renderWindows(json.windows || []);
    renderPending(json.pending || []);
  }

  if (officeSelect) {
    officeSelect.addEventListener('change', () => {
      const v = parseInt(officeSelect.value,10);
      if (!isNaN(v) && v > 0) {
        office = v;
        if (root) root.setAttribute('data-office', String(office));
        load();
      }
    });
  }

  // initial load + polling
  (function init(){
    load();
    setInterval(load, pollInterval);
  })();
})();