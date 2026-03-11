document.addEventListener('DOMContentLoaded', () => {
  const officeSelect = document.getElementById('officeSelect');
  const staffSelect = document.getElementById('staffSelect');
  const windowNo = document.getElementById('windowNo');
  const createForm = document.getElementById('createForm');
  const windowsTableBody = document.querySelector('#windowsTable tbody');
  const windowsMobileList = document.getElementById('windowsMobileList');
  const refreshBtn = document.getElementById('refreshBtn');

  let dataTable = null; // DataTable instance

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
    if (windowsMobileList) windowsMobileList.innerHTML = '';
    
    // Destroy existing DataTable if it exists
    if (dataTable) {
      dataTable.destroy();
      dataTable = null;
    }
    
    api('../backend/assignStaffData.php?action=list_windows').then(j => {
        if (!j.success) {
            windowsTableBody.innerHTML = '<tr><td colspan="5">Error loading</td></tr>';
            if (windowsMobileList) windowsMobileList.innerHTML = '';
            if (window.mobileTableCards) {
              window.mobileTableCards.register('windowsMobileSearch', 'windowsMobileList', 'windowsMobileEmpty', {
                emptyText: 'Unable to load windows.',
                emptySearchText: 'No windows match your search.'
              });
            }
            return;
        }
        if (!j.windows.length) {
            windowsTableBody.innerHTML = '<tr><td colspan="5">No windows defined</td></tr>';
            if (windowsMobileList) windowsMobileList.innerHTML = '';
            if (window.mobileTableCards) {
              window.mobileTableCards.register('windowsMobileSearch', 'windowsMobileList', 'windowsMobileEmpty', {
                emptyText: 'No windows defined.',
                emptySearchText: 'No windows match your search.'
              });
            }
            return;
        }
        windowsTableBody.innerHTML = '';
        if (windowsMobileList) windowsMobileList.innerHTML = '';
        
        j.windows.forEach(w => {
            const tr = document.createElement('tr');
            tr.setAttribute('data-office-name', w.office_description);
            tr.setAttribute('data-window-number', w.window_no);
            if (parseInt(w.enabled, 10) === 0) tr.classList.add('row-disabled');

            tr.innerHTML = `
                <td>${w.office_description}</td>
                <td>
                    <input class="window-no-input" type="number" value="${w.window_no}" min="1" style="width:80px" data-window="${w.window_id}" ${parseInt(w.enabled,10) === 0 ? 'disabled' : ''} />
                    <button class="btn btn-sm btn-success save-window-btn action-btn" data-window="${w.window_id}" ${parseInt(w.enabled,10) === 0 ? 'disabled' : ''}>
                        <i class="fa fa-floppy-disk me-1"></i> Save
                    </button>
                </td>
                <td>
                    <select class="assign-select small-select" data-window="${w.window_id}" ${parseInt(w.enabled,10) === 0 ? 'disabled' : ''}></select>
                </td>
                <td>${parseInt(w.enabled,10) === 1 ? 'Enabled' : 'Disabled'}</td>
                <td class="text-center">
                    <button class="btn btn-sm ${parseInt(w.enabled,10) === 1 ? 'btn-secondary' : 'btn-success'} toggle-btn action-btn" data-window="${w.window_id}" data-enabled="${w.enabled}">
                        <i class="fa ${parseInt(w.enabled,10) === 1 ? 'fa-toggle-on' : 'fa-toggle-off'} me-1"></i>
                    </button>
                    <button class="btn btn-sm btn-danger delete-btn action-btn" data-window="${w.window_id}">
                        <i class="fa fa-trash me-1"></i>
                    </button>
                </td>
            `;
            windowsTableBody.appendChild(tr);

            if (windowsMobileList) {
              const card = document.createElement('div');
              const collapseId = `windowMobile${w.window_id}`;
              card.className = 'mobile-table-card';
              card.setAttribute('data-window-card', 'true');
              card.setAttribute('data-office-name', w.office_description);
              card.setAttribute('data-window-number', w.window_no);
              card.setAttribute('data-search-text', `${w.office_description} ${w.window_no} ${w.assigned_staff_name || ''} ${parseInt(w.enabled,10) === 1 ? 'Enabled' : 'Disabled'}`);
              card.innerHTML = `
                <button class="mobile-table-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#${collapseId}" aria-expanded="false" aria-controls="${collapseId}">
                  <span class="mobile-table-icon"><i class="fa-solid fa-window-maximize"></i></span>
                  <span class="mobile-table-summary">
                    <span class="mobile-table-title">${w.office_description}</span>
                    <span class="mobile-table-meta">Window ${w.window_no} · ${parseInt(w.enabled,10) === 1 ? 'Enabled' : 'Disabled'}</span>
                  </span>
                  <span class="mobile-table-chevron"><i class="fa-solid fa-chevron-down"></i></span>
                </button>
                <div id="${collapseId}" class="collapse mobile-table-body">
                  <div class="mobile-table-grid">
                    <div class="mobile-table-field">
                      <span class="mobile-table-label">Window Number</span>
                      <div class="mobile-table-value d-flex gap-2 align-items-center">
                        <input class="window-no-input form-control form-control-sm" type="number" value="${w.window_no}" min="1" data-window="${w.window_id}" ${parseInt(w.enabled,10) === 0 ? 'disabled' : ''} />
                        <button class="btn btn-sm btn-success save-window-btn action-btn" data-window="${w.window_id}" ${parseInt(w.enabled,10) === 0 ? 'disabled' : ''}>
                          <i class="fa fa-floppy-disk me-1"></i>Save
                        </button>
                      </div>
                    </div>
                    <div class="mobile-table-field">
                      <span class="mobile-table-label">Assigned Staff</span>
                      <select class="assign-select form-select form-select-sm" data-window="${w.window_id}" ${parseInt(w.enabled,10) === 0 ? 'disabled' : ''}></select>
                    </div>
                    <div class="mobile-table-field"><span class="mobile-table-label">Status</span><span class="mobile-table-value">${parseInt(w.enabled,10) === 1 ? 'Enabled' : 'Disabled'}</span></div>
                  </div>
                  <div class="mobile-table-actions">
                    <button class="btn btn-sm ${parseInt(w.enabled,10) === 1 ? 'btn-secondary' : 'btn-success'} toggle-btn action-btn" data-window="${w.window_id}" data-enabled="${w.enabled}">
                      <i class="fa ${parseInt(w.enabled,10) === 1 ? 'fa-toggle-on' : 'fa-toggle-off'} me-1"></i>${parseInt(w.enabled,10) === 1 ? 'Disable' : 'Enable'}
                    </button>
                    <button class="btn btn-sm btn-danger delete-btn action-btn" data-window="${w.window_id}">
                      <i class="fa fa-trash me-1"></i>Delete
                    </button>
                  </div>
                </div>
              `;
              windowsMobileList.appendChild(card);
            }

            // populate staff select filtered by office
            api(`../backend/assignStaffData.php?action=list_staffs&office_id=${encodeURIComponent(w.office_id)}`)
              .then(res => {
                  const selects = document.querySelectorAll(`.assign-select[data-window="${w.window_id}"]`);
                  selects.forEach(sel => {
                    sel.innerHTML = '<option value="">-- unassigned --</option>';
                    if (res.success) {
                        res.staffs.forEach(s => {
                            const o = document.createElement('option');
                            o.value = s.user_id;
                            o.textContent = `${s.name} (${s.user_name})`;
                            if (String(s.user_id) === String(w.assigned_staff_id)) o.selected = true;
                            sel.appendChild(o);
                        });
                    }
                  });
              });
        });

        if (window.mobileTableCards) {
          window.mobileTableCards.register('windowsMobileSearch', 'windowsMobileList', 'windowsMobileEmpty', {
            emptyText: 'No windows defined.',
            emptySearchText: 'No windows match your search.'
          });
        }
        
        // Initialize DataTable after table is populated
        setTimeout(() => {
          if (dataTable) dataTable.destroy();
          dataTable = $('#windowsTable').DataTable({
            pageLength: 10,
            lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
            order: [[0, 'asc'], [1, 'asc']], // Sort by Office, then Window No
            language: {
              search: "Search:",
              lengthMenu: "Show _MENU_ entries",
              info: "Showing _START_ to _END_ of _TOTAL_ windows",
              infoEmpty: "No windows available",
              infoFiltered: "(filtered from _MAX_ total windows)",
              zeroRecords: "No matching windows found"
            }
          });
        }, 100);
    });
  }

  officeSelect.addEventListener('change', e => loadStaffsForOffice(e.target.value));

  createForm.addEventListener('submit', e => {
    e.preventDefault();
    const officeId = officeSelect.value;
    const wn = windowNo.value; // if empty -> backend will auto assign
    const staffId = staffSelect.value;
    if (!officeId) {
      Swal.fire({
        title: 'Validation Error!',
        text: 'Please select an office',
        icon: 'warning',
        confirmButtonColor: '#ffc107'
      });
      return;
    }
    
    Swal.fire({
      title: 'Creating Window...',
      text: 'Please wait',
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });
    
    const form = new FormData();
    form.append('action','create_window');
    form.append('office_id', officeId);
    form.append('window_no', wn); // can be empty or 0
    form.append('staff_id', staffId);
    fetch('../backend/assignStaffData.php', { method:'POST', body: form })
      .then(r => r.json())
      .then(j => {
        if (j.success) {
          windowNo.value='';
          staffSelect.selectedIndex=0;
          Swal.fire({
            title: 'Success!',
            text: 'Window created successfully',
            icon: 'success',
            confirmButtonColor: '#28a745'
          }).then(() => {
            loadWindows();
          });
        } else {
          Swal.fire({
            title: 'Error!',
            text: 'Error: ' + (j.error || 'failed'),
            icon: 'error',
            confirmButtonColor: '#d33'
          });
        }
      }).catch(()=>{
        Swal.fire({
          title: 'Error!',
          text: 'Network error',
          icon: 'error',
          confirmButtonColor: '#d33'
        });
      });
  });

  refreshBtn.addEventListener('click', loadWindows);

  document.addEventListener('change', e => {
    if (e.target && e.target.matches('.assign-select')) {
      const windowId = e.target.dataset.window;
      const staffId = e.target.value;
      const staffName = e.target.options[e.target.selectedIndex].text;
      
      Swal.fire({
        title: 'Assigning Staff...',
        text: 'Please wait',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });
      
      const fd = new FormData();
      fd.append('action','assign_staff');
      fd.append('window_id', windowId);
      fd.append('staff_id', staffId);
      
      fetch('../backend/assignStaffData.php', { method:'POST', body: fd })
        .then(r=>r.json())
        .then(j=>{
          if (!j.success) {
            Swal.fire({
              title: 'Error!',
              text: 'Assign failed: ' + (j.error || 'unknown'),
              icon: 'error',
              confirmButtonColor: '#d33'
            }).then(() => {
              loadWindows();
            });
          } else {
            Swal.fire({
              title: 'Success!',
              text: 'Staff assigned successfully',
              icon: 'success',
              confirmButtonColor: '#28a745',
              timer: 1500,
              showConfirmButton: false
            }).then(() => {
              loadWindows();
            });
          }
        })
        .catch(()=>{
          Swal.fire({
            title: 'Error!',
            text: 'Network error',
            icon: 'error',
            confirmButtonColor: '#d33'
          }).then(() => {
            loadWindows();
          });
        });
    }
  });

  document.addEventListener('click', e => {
    // Handle delete button clicks
    if (e.target.closest('.delete-btn')) {
      const btn = e.target.closest('.delete-btn');
      const windowId = btn.dataset.window;
      const row = btn.closest('[data-office-name]');
      const officeName = row?.getAttribute('data-office-name') || '';
      const windowInput = row?.querySelector(`.window-no-input[data-window="${windowId}"]`);
      const windowNum = windowInput ? windowInput.value : (row?.getAttribute('data-window-number') || '');
      
      Swal.fire({
        title: 'Are you sure?',
        text: `Do you want to delete window ${windowNum} in ${officeName}? This action cannot be undone!`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
      }).then((result) => {
        if (result.isConfirmed) {
          Swal.fire({
            title: 'Deleting...',
            text: 'Please wait',
            allowOutsideClick: false,
            didOpen: () => {
              Swal.showLoading();
            }
          });
          
          const fd = new FormData();
          fd.append('action','delete_window');
          fd.append('window_id', windowId);
          
          fetch('../backend/assignStaffData.php', { method:'POST', body: fd })
            .then(r=>r.json())
            .then(j=>{
              if (j.success) {
                Swal.fire({
                  title: 'Deleted!',
                  text: 'Window has been deleted successfully.',
                  icon: 'success',
                  confirmButtonColor: '#28a745'
                }).then(() => {
                  loadWindows();
                });
              } else {
                Swal.fire({
                  title: 'Error!',
                  text: 'Delete failed',
                  icon: 'error',
                  confirmButtonColor: '#d33'
                });
              }
            })
            .catch(()=>{
              Swal.fire({
                title: 'Error!',
                text: 'Network error',
                icon: 'error',
                confirmButtonColor: '#d33'
              });
            });
        }
      });
      return;
    }

    // Handle toggle button clicks
    if (e.target.closest('.toggle-btn')) {
      const btn = e.target.closest('.toggle-btn');
      const windowId = btn.dataset.window;
      const current = parseInt(btn.dataset.enabled,10);
      const newState = current === 1 ? 0 : 1;
      const action = newState === 1 ? 'enable' : 'disable';
      const row = btn.closest('[data-office-name]');
      const officeName = row?.getAttribute('data-office-name') || '';
      const windowInput = row?.querySelector(`.window-no-input[data-window="${windowId}"]`);
      const windowNum = windowInput ? windowInput.value : (row?.getAttribute('data-window-number') || '');

      Swal.fire({
        title: 'Are you sure?',
        text: `Do you want to ${action} window ${windowNum} in ${officeName}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: newState === 1 ? '#28a745' : '#6c757d',
        cancelButtonColor: '#6c757d',
        confirmButtonText: `Yes, ${action} it!`,
        cancelButtonText: 'Cancel'
      }).then((result) => {
        if (result.isConfirmed) {
          Swal.fire({
            title: 'Processing...',
            text: 'Please wait',
            allowOutsideClick: false,
            didOpen: () => {
              Swal.showLoading();
            }
          });

          const fd = new FormData();
          fd.append('action','toggle_window');
          fd.append('window_id', windowId);
          fd.append('enabled', newState);
          
          fetch('../backend/assignStaffData.php', { method:'POST', body: fd })
            .then(r=>r.json())
            .then(j=>{
              if (j.success) {
                Swal.fire({
                  title: 'Success!',
                  text: `Window has been ${action}d successfully.`,
                  icon: 'success',
                  confirmButtonColor: '#28a745'
                }).then(() => {
                  loadWindows();
                });
              } else {
                Swal.fire({
                  title: 'Error!',
                  text: 'Toggle failed: ' + (j.error || 'unknown'),
                  icon: 'error',
                  confirmButtonColor: '#d33'
                });
              }
            })
            .catch(()=>{
              Swal.fire({
                title: 'Error!',
                text: 'Network error',
                icon: 'error',
                confirmButtonColor: '#d33'
              });
            });
        }
      });
      return;
    }

    // Handle save window button clicks
    if (e.target.closest('.save-window-btn')) {
      const btn = e.target.closest('.save-window-btn');
      const windowId = btn.dataset.window;
      const scope = btn.closest('[data-window-card]') || btn.closest('tr') || document;
      const input = scope.querySelector(`.window-no-input[data-window="${windowId}"]`) || document.querySelector(`.window-no-input[data-window="${windowId}"]`);
      if (!input) return;
      const newNo = parseInt(input.value,10);
      if (!newNo || newNo <= 0) {
        Swal.fire({
          title: 'Validation Error!',
          text: 'Invalid window number',
          icon: 'warning',
          confirmButtonColor: '#ffc107'
        });
        return;
      }
      
      Swal.fire({
        title: 'Saving...',
        text: 'Please wait',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });
      
      const fd = new FormData();
      fd.append('action','update_window_no');
      fd.append('window_id', windowId);
      fd.append('window_no', newNo);
      
      fetch('../backend/assignStaffData.php', { method:'POST', body: fd })
        .then(r=>r.json())
        .then(j=>{
          if (!j.success) {
            Swal.fire({
              title: 'Error!',
              text: 'Save failed: ' + (j.error || 'unknown'),
              icon: 'error',
              confirmButtonColor: '#d33'
            });
          } else {
            Swal.fire({
              title: 'Success!',
              text: 'Window number updated successfully',
              icon: 'success',
              confirmButtonColor: '#28a745',
              timer: 1500,
              showConfirmButton: false
            }).then(() => {
              loadWindows();
            });
          }
        })
        .catch(()=>{
          Swal.fire({
            title: 'Error!',
            text: 'Network error',
            icon: 'error',
            confirmButtonColor: '#d33'
          });
        });
      return;
    }
  });

  // initial
  if (window.mobileTableCards) {
    window.mobileTableCards.register('windowsMobileSearch', 'windowsMobileList', 'windowsMobileEmpty', {
      emptyText: 'No windows defined.',
      emptySearchText: 'No windows match your search.'
    });
  }
  loadOffices();
  loadWindows();
});