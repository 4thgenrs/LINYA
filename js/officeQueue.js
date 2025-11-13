// Polls backend and updates DOM every 3s
(function(){
  const api = '../backend/officeQueueData.php';
  const root = document.getElementById('root');
  let office = root ? parseInt(root.getAttribute('data-office'),10) || 1 : 1;
  const windowsContainer = document.getElementById('windowsContainer');
  const pendingList = document.getElementById('pendingList');
  const officeSelect = document.getElementById('officeSelect');

  function fetchState() {
    return fetch(`${api}?office=${office}`, { credentials: 'same-origin' })
      .then(r => r.json());
  }

  function populateOffices(offices) {
    if (!officeSelect) return;
    const prev = parseInt(officeSelect.value,10) || office;
    officeSelect.innerHTML = '';
    offices.forEach(o => {
      const opt = document.createElement('option');
      opt.value = o.office_id;
      opt.textContent = o.office_description;
      officeSelect.appendChild(opt);
    });
    // restore selection if possible
    if (prev) officeSelect.value = prev;
    office = parseInt(officeSelect.value,10) || office;
  }

  if (officeSelect) {
    officeSelect.addEventListener('change', () => {
      const v = parseInt(officeSelect.value,10);
      if (!isNaN(v) && v > 0) {
        office = v;
        // update root dataset for consistency
        if (root) root.setAttribute('data-office', String(office));
        load(); // immediate reload on change
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
        b.title = 'Soft copy available';
        sp.appendChild(b);
      }
      parent.appendChild(sp);
    });
  }

  function render(state) {
    if (!state || !state.success) return;
    // windows — filter out disabled windows
    windowsContainer.innerHTML = '';
    const visible = (state.windows || []).filter(w => parseInt(w.enabled, 10) === 1);
    visible.forEach(w => {
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

    // pending
    pendingList.innerHTML = '';
    if (!state.pending || !state.pending.length) {
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

  // initial load + polling
  function load() {
    fetchState()
      .then(json => render(json))
      .catch(err => {
        console.error('officeQueue fetch error', err);
      });
  }

  document.addEventListener('DOMContentLoaded', function(){
    load();
    setInterval(load, 3000);
  });
})();