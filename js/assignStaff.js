document.addEventListener('DOMContentLoaded', () => {
  const officeSelect = document.getElementById('officeSelect');
  const staffSelect = document.getElementById('staffSelect');
  const windowNo = document.getElementById('windowNo');
  const createForm = document.getElementById('createForm');
  const windowsTableBody = document.querySelector('#windowsTable tbody');
  const refreshBtn = document.getElementById('refreshBtn');

  function api(path, opts) { return fetch(path, opts).then(r => r.json()); }

  function loadOffices() {
    api('../backend/assignStaffData.php?action=list_offices').then(j => {
      officeSelect.innerHTML = '<option value="">Select office</option>';
      if (j.success) j.offices.forEach(o => {
        const opt = document.createElement('option');
        opt.value = o.office_id; opt.textContent = o.office_description;
        officeSelect.appendChild(opt);
      });
    });
  }

  // get next window number when office is chosen
  function getNextWindowNo(officeId) {
    if (!officeId) { windowNo.value = ''; return; }
    api(`../backend/assignStaffData.php?action=get_next_window_no&office_id=${encodeURIComponent(officeId)}`)
      .then(j => { if (j.success) windowNo.value = j.next_no; })
      .catch(()=>{ windowNo.value = ''; });
  }

  function loadStaffsForOffice(officeId) {
    staffSelect.innerHTML = '<option value="">Loading...</option>'; staffSelect.disabled = true;
    if (!officeId) { staffSelect.innerHTML = '<option value="">Select office first</option>'; staffSelect.disabled = true; return; }
    api(`../backend/assignStaffData.php?action=list_staffs&office_id=${encodeURIComponent(officeId)}`)
      .then(j => {
        staffSelect.innerHTML = '<option value="">-- none / unassigned --</option>';
        if (j.success) j.staffs.forEach(s => {
          const opt = document.createElement('option'); opt.value = s.user_id; opt.textContent = s.name + ' (' + s.user_name + ')';
          staffSelect.appendChild(opt);
        });
        staffSelect.disabled = false;
      });
    getNextWindowNo(officeId);
  }

  function loadWindows() {
    windowsTableBody.innerHTML = '<tr><td colspan="5">Loading...</td></tr>';
    api('../backend/assignStaffData.php?action=list_windows').then(j => {
      if (!j.success) { windowsTableBody.innerHTML = '<tr><td colspan="5">Error loading</td></tr>'; return; }
      if (!j.windows.length) { windowsTableBody.innerHTML = '<tr><td colspan="5">No windows defined</td></tr>'; return; }
      windowsTableBody.innerHTML = '';
      j.windows.forEach(w => {
        const tr = document.createElement('tr');
        if (parseInt(w.enabled,10) === 0) tr.classList.add('row-disabled');
        tr.innerHTML = `
          <td>${w.office_description}</td>
            <td>
              <input class="window-no-input form-control d-inline-block text-center" type="number" value="${w.window_no}" min="1"
                    style="width:80px" data-window="${w.window_id}" 
                    ${parseInt(w.enabled,10) === 0 ? 'disabled' : ''} />
              <button class="btn btn-sm btn-success save-window-btn action-btn ms-1" data-window="${w.window_id}">
                <i class="fa-solid fa-check me-1"></i>
              </button>
            </td>

            <td>
              <select class="assign-select form-select form-select-sm small-select" 
                      data-window="${w.window_id}" 
                      ${parseInt(w.enabled,10) === 0 ? 'disabled' : ''}></select>
            </td>

            <td>${parseInt(w.enabled,10) === 1 ? 'Enabled' : 'Disabled'}</td>

            <td>
              <button class="btn btn-sm ${parseInt(w.enabled,10) === 1 ? 'btn-warning' : 'btn-success'} toggle-btn action-btn me-1"
                      data-window="${w.window_id}" data-enabled="${w.enabled}">
                <i class="fa-solid ${parseInt(w.enabled,10) === 1 ? 'fa-ban' : 'fa-check'} me-1"></i>
                ${parseInt(w.enabled,10) === 1 ? 'Disable' : 'Enable'}
              </button>

              <button class="btn btn-sm btn-danger delete-btn action-btn" data-window="${w.window_id}">
                <i class="fa-solid fa-trash me-1"></i>
              </button>
            </td>
        `;
        windowsTableBody.appendChild(tr);

        // populate staff select filtered by office
        api(`../backend/assignStaffData.php?action=list_staffs&office_id=${encodeURIComponent(w.office_id)}`)
          .then(res => {
            const sel = tr.querySelector('.assign-select');
            sel.innerHTML = '<option value="">-- unassigned --</option>';
            if (res.success) {
              res.staffs.forEach(s => {
                const o = document.createElement('option');
                o.value = s.user_id;
                o.textContent = s.name + ' (' + s.user_name + ')';
                if (String(s.user_id) === String(w.assigned_staff_id)) o.selected = true;
                sel.appendChild(o);
              });
            }
          });
      });
    });
  }

  officeSelect.addEventListener('change', e => loadStaffsForOffice(e.target.value));

  createForm.addEventListener('submit', e => {
    e.preventDefault();
    const officeId = officeSelect.value;
    const wn = windowNo.value; // if empty -> backend will auto assign
    const staffId = staffSelect.value;
    if (!officeId) { alert('Select office'); return; }
    const form = new FormData();
    form.append('action','create_window');
    form.append('office_id', officeId);
    form.append('window_no', wn); // can be empty or 0
    form.append('staff_id', staffId);
    fetch('../backend/assignStaffData.php', { method:'POST', body: form })
      .then(r => r.json())
      .then(j => {
        if (j.success) { windowNo.value=''; staffSelect.selectedIndex=0; loadWindows(); } else alert('Error: ' + (j.error || 'failed'));
      }).catch(()=>alert('Network error'));
  });

  refreshBtn.addEventListener('click', loadWindows);

  document.addEventListener('change', e => {
    if (e.target && e.target.matches('.assign-select')) {
      const windowId = e.target.dataset.window;
      const staffId = e.target.value;
      const fd = new FormData(); fd.append('action','assign_staff'); fd.append('window_id', windowId); fd.append('staff_id', staffId);
      fetch('../backend/assignStaffData.php', { method:'POST', body: fd }).then(r=>r.json()).then(j=>{
        if (!j.success) {
          alert('Assign failed: ' + (j.error || 'unknown'));
          loadWindows();
        } else loadWindows();
      }).catch(()=>alert('Network error'));
    }
  });

  document.addEventListener('click', e => {
    if (e.target && e.target.matches('.delete-btn')) {
      if (!confirm('Delete window?')) return;
      const windowId = e.target.dataset.window;
      const fd = new FormData(); fd.append('action','delete_window'); fd.append('window_id', windowId);
      fetch('../backend/assignStaffData.php', { method:'POST', body: fd }).then(r=>r.json()).then(j=>{ if (j.success) loadWindows(); else alert('Delete failed'); }).catch(()=>alert('Network error'));
    }

    if (e.target && e.target.matches('.toggle-btn')) {
      const windowId = e.target.dataset.window;
      const current = parseInt(e.target.dataset.enabled,10);
      const newState = current === 1 ? 0 : 1;
      const fd = new FormData(); fd.append('action','toggle_window'); fd.append('window_id', windowId); fd.append('enabled', newState);
      fetch('../backend/assignStaffData.php', { method:'POST', body: fd }).then(r=>r.json()).then(j=>{ if (j.success) loadWindows(); else alert('Toggle failed'); }).catch(()=>alert('Network error'));
    }

    if (e.target && e.target.matches('.save-window-btn')) {
      const windowId = e.target.dataset.window;
      const input = document.querySelector(`.window-no-input[data-window="${windowId}"]`);
      if (!input) return;
      const newNo = parseInt(input.value,10);
      if (!newNo || newNo <= 0) { alert('Invalid window number'); return; }
      const fd = new FormData(); fd.append('action','update_window_no'); fd.append('window_id', windowId); fd.append('window_no', newNo);
      fetch('../backend/assignStaffData.php', { method:'POST', body: fd }).then(r=>r.json()).then(j=>{
        if (!j.success) alert('Save failed: ' + (j.error || 'unknown')); else loadWindows();
      }).catch(()=>alert('Network error'));
    }
  });

  // initial
  loadOffices();
  loadWindows();
});