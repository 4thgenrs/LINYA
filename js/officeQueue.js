// Polls backend and updates DOM every 3s. Populates office selector from backend.offices and switches live view on change.
(function(){
  const api = '../backend/officeQueueData.php';
  const root = document.getElementById('root');
  const windowsContainer = document.getElementById('windowsContainer');
  const pendingList = document.getElementById('pendingList');
  const officeSelect = document.getElementById('officeSelect');
<<<<<<< HEAD
  const timeDisplay = document.getElementById('current-time');
  const dateDisplay = document.getElementById('current-date');
  const officeNameDisplay = document.getElementById('office-name');

  // ===== TIME DISPLAY FUNCTION =====
  function updateTime() {
    const now = new Date();
    const timeString = now.toLocaleTimeString('en-US', {
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit',
      hour12: true
    });
    const dateString = now.toLocaleDateString('en-US', {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
    
    if (dateDisplay) {
      dateDisplay.textContent = dateString;
    }
    if (timeDisplay) {
      timeDisplay.textContent = timeString;
    }
  }
=======
  const pollInterval = 3000;
>>>>>>> fccc85f8acfc01e3bf9ec2157cf3f6201ae9a088

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
<<<<<<< HEAD
    if (!officeSelect) return;
    
    // Hardcoded office names (these will be used in the dropdown AND the header)
    // UPDATE: Based on your actual office IDs from the database
    const officeNameMap = {
      1: 'REGISTRAR',
      2: 'ACCOUNTING',
      8: 'MIS',  // MIS is office_id 8
      4: 'OFFICE 4'
    };
    
=======
    if (!officeSelect || !Array.isArray(offices)) return;
>>>>>>> fccc85f8acfc01e3bf9ec2157cf3f6201ae9a088
    const prev = parseInt(officeSelect.value,10) || office;
    officeSelect.innerHTML = '';
    
    offices.forEach(o => {
      const opt = document.createElement('option');
      opt.value = o.office_id;
      // Use hardcoded name instead of API description
      opt.textContent = officeNameMap[o.office_id] || o.office_description;
      officeSelect.appendChild(opt);
    });
<<<<<<< HEAD
    
    if (prev) officeSelect.value = prev;
    office = parseInt(officeSelect.value,10) || office;
    
    console.log('Populating offices, current office:', office); // Debug
    
    // Update the office name in navbar (using hardcoded names, not from API)
    updateOfficeName();
  }

  // Function to update office name in navbar with fade transition
  function updateOfficeName() {
    if (!officeNameDisplay) return;
    
    // Define the exact office names to display
    const officeNameMap = {
      1: 'OFFICE OF THE UNIVERSITY REGISTRAR',
      2: 'OFFICE OF THE UNIVERSITY ACCOUNTING',
      8: 'MANAGEMENT INFORMATION SYSTEMS',  // MIS is office_id 8
      4: 'OFFICE 4' // Change this to the actual name
    };
    
    console.log('Current office ID:', office); // Debug log
    const newOfficeName = officeNameMap[office] || 'OFFICE OF THE UNIVERSITY REGISTRAR';
    console.log('New office name:', newOfficeName); // Debug log
    
    if (newOfficeName !== officeNameDisplay.textContent) {
      // Fade out
      officeNameDisplay.style.opacity = '0';
      // Wait for fade out to complete, then change text and fade in
      setTimeout(() => {
        officeNameDisplay.textContent = newOfficeName;
        // Force reflow to ensure opacity change is registered
        officeNameDisplay.offsetHeight;
        // Fade in
        officeNameDisplay.style.opacity = '1';
      }, 200);
    }
  }

  if (officeSelect) {
    officeSelect.addEventListener('change', () => {
      const v = parseInt(officeSelect.value,10);
      console.log('Office select changed to:', v); // Debug
      if (!isNaN(v) && v > 0) {
        office = v;
        if (root) root.setAttribute('data-office', String(office));
        // Update office name immediately on change
        updateOfficeName();
        load();
      }
    });
=======
    if (prev && officeSelect.querySelector(`option[value="${prev}"]`)) {
      officeSelect.value = prev;
    } else if (root && root.getAttribute('data-office') && officeSelect.querySelector(`option[value="${root.getAttribute('data-office')}"]`)) {
      officeSelect.value = root.getAttribute('data-office');
    }
    office = parseInt(officeSelect.value,10) || office;
    if (root) root.setAttribute('data-office', String(office));
>>>>>>> fccc85f8acfc01e3bf9ec2157cf3f6201ae9a088
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
        sp.appendChild(b);
      }
      parent.appendChild(sp);
    });
  }

<<<<<<< HEAD
  function render(state) {
    if (!state || !state.success) return;

    if (state.offices?.length) {
      populateOffices(state.offices);
    }

    windowsContainer.innerHTML = '';
    state.windows.forEach(w => {
      const card = document.createElement('div');
      card.className = 'window-card';

      const top = document.createElement('div');
      top.className = 'window-top';

      const no = document.createElement('div');
      no.className = 'window-no';
      no.textContent = 'Window ' + w.window_no;

      const staff = document.createElement('div');
      staff.className = 'window-staff';
      staff.textContent = w.enabled ? (w.assigned_staff_id ? 'Staff ID: ' + w.assigned_staff_id : 'Open') : 'Disabled';

      top.appendChild(no);
      top.appendChild(staff);
      card.appendChild(top);

      if (w.current_queue) {
        const code = document.createElement('div');
        code.className = 'queue-code';
        code.textContent = w.current_queue.queue_code;

        const name = document.createElement('div');
        name.className = 'queue-name';
        name.textContent = w.current_queue.customer_name || '';

        card.appendChild(code);
        card.appendChild(name);

        const svcWrap = document.createElement('div');
        renderServices(svcWrap, w.current_queue.services);
        card.appendChild(svcWrap);
      } else {
        const waiting = document.createElement('div');
        waiting.className = 'queue-name';
        waiting.textContent = 'Waiting';
        card.appendChild(waiting);
      }

      windowsContainer.appendChild(card);
    });

    pendingList.innerHTML = '';
    if (!state.pending?.length) {
      const e = document.createElement('div');
      e.className = 'pending-empty';
      e.textContent = 'No pending queues';
      pendingList.appendChild(e);
    } else {
      state.pending.forEach(p => {
        const item = document.createElement('div');
        item.className = 'pending-item';

        const code = document.createElement('div');
        code.className = 'pending-code';
        code.textContent = p.queue_code;

        const name = document.createElement('div');
        name.textContent = p.customer_name || '';

        item.appendChild(code);
        item.appendChild(name);

        const svcWrap = document.createElement('div');
        svcWrap.style.marginTop = '6px';
        renderServices(svcWrap, p.services);
        item.appendChild(svcWrap);

        pendingList.appendChild(item);
      });
=======
  function renderWindows(windows) {
    // show skeleton if empty and first load
    if (!windows) {
      windowsContainer.innerHTML = '<div class="skel" style="height:220px;border-radius:12px"></div>';
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
      top.innerHTML = `<div class="window-no">Window ${escapeHtml(String(w.window_no||''))}</div><div class="window-staff">${w.assigned_staff_id?('Staff ID: '+escapeHtml(String(w.assigned_staff_id))):'Open'}</div>`;
      card.appendChild(top);

      const spot = document.createElement('div');
      spot.className = 'queue-spot';

      if (w.current_queue) {
        const q = w.current_queue;
        const code = document.createElement('div');
        code.className = 'queue-code';
        code.textContent = q.queue_code || '';

        const name = document.createElement('div');
        name.className = 'queue-name';
        name.textContent = q.customer_name || '';

        spot.appendChild(code);
        spot.appendChild(name);

        const svcWrap = document.createElement('div');
        renderServices(svcWrap, q.services || []);
        spot.appendChild(svcWrap);
      } else {
        const waiting = document.createElement('div');
        waiting.className = 'waiting';
        waiting.textContent = 'Waiting';
        spot.appendChild(waiting);
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
>>>>>>> fccc85f8acfc01e3bf9ec2157cf3f6201ae9a088
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

<<<<<<< HEAD
  function load() {
    fetchState()
      .then(json => render(json))
      .catch(err => console.error('officeQueue fetch error', err));
  }

  document.addEventListener('DOMContentLoaded', function(){
    updateTime();
    setInterval(updateTime, 1000);

    // Set the initial office name on page load
    updateOfficeName();

=======
  // initial load + polling
  (function init(){
>>>>>>> fccc85f8acfc01e3bf9ec2157cf3f6201ae9a088
    load();
    setInterval(load, pollInterval);
  })();
})();