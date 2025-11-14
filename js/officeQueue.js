// Polls backend and updates DOM every 3s
(function(){
  const api = '../backend/officeQueueData.php';
  const root = document.getElementById('root');
  let office = root ? parseInt(root.getAttribute('data-office'),10) || 1 : 1;
  const windowsContainer = document.getElementById('windowsContainer');
  const pendingList = document.getElementById('pendingList');
  const officeSelect = document.getElementById('officeSelect');
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

  function fetchState() {
    return fetch(`${api}?office=${office}`, { credentials: 'same-origin' })
      .then(r => r.json());
  }

  function populateOffices(offices) {
    if (!officeSelect) return;
    
    // Hardcoded office names (these will be used in the dropdown AND the header)
    // UPDATE: Based on your actual office IDs from the database
    const officeNameMap = {
      1: 'REGISTRAR',
      2: 'ACCOUNTING',
      8: 'MIS',  // MIS is office_id 8
      4: 'OFFICE 4'
    };
    
    const prev = parseInt(officeSelect.value,10) || office;
    officeSelect.innerHTML = '';
    
    offices.forEach(o => {
      const opt = document.createElement('option');
      opt.value = o.office_id;
      // Use hardcoded name instead of API description
      opt.textContent = officeNameMap[o.office_id] || o.office_description;
      officeSelect.appendChild(opt);
    });
    
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
  }

  function renderServices(parent, services) {
    if (!services || !services.length) return;
    services.forEach(s => {
      const sp = document.createElement('span');
      sp.className = 'svc-chip';
      sp.textContent = s.service_description;
      if (s.soft_copy && (s.soft_copy === 1 || s.soft_copy === true)) {
        const b = document.createElement('small');
        b.className = 'soft-badge';
        b.textContent = 'PDF';
        sp.appendChild(b);
      }
      parent.appendChild(sp);
    });
  }

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
    }
  }

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

    load();
    setInterval(load, 3000);
  });
})();