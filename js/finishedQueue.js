// Fetch finishedQueueData.php and render table; supports office filter
(function(){
  const api = '../backend/finishedQueueData.php';
  const officeSelect = document.getElementById('officeFilter');
  const tbody = document.querySelector('#finishedTable tbody');
  const mobileList = document.getElementById('finishedMobileList');
  const refreshBtn = document.getElementById('refreshBtn');
  let dataTable = null;
  let currentData = []; // Store current data for CSV export

  function formatCell(v){
    return (v === null || typeof v === 'undefined' || v === '') ? '—' : String(v);
  }

  function formatDateTime(v){
    if (!v || v === '—') return '—';
    // Split datetime into date and time
    const parts = String(v).split(' ');
    if (parts.length === 2) {
      return `<div>${parts[0]}</div><div class="small">${parts[1]}</div>`;
    }
    return v;
  }

  function renderRows(rows){
    // Store data for CSV export
    currentData = rows || [];
    
    // Destroy existing DataTable if it exists
    if (dataTable) {
      dataTable.destroy();
      dataTable = null;
    }

    tbody.innerHTML = '';
    if (mobileList) mobileList.innerHTML = '';
    if (!rows || !rows.length) {
      const tr = document.createElement('tr');
      tr.innerHTML = `<td colspan="9" class="small-muted">No records</td>`;
      tbody.appendChild(tr);
      if (window.mobileTableCards) {
        window.mobileTableCards.register('finishedMobileSearch', 'finishedMobileList', 'finishedMobileEmpty', {
          emptyText: 'No finished queues found.',
          emptySearchText: 'No finished queues match your search.'
        });
      }
      return;
    }
    rows.forEach((r, index) => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${formatCell(r.office_description)}</td>
        <td>${formatCell(r.window_no && r.window_no > 0 ? r.window_no : (r.window_id && r.window_id > 0 ? r.window_id : '—'))}</td>
        <td>${formatCell(r.processed_by)}</td>
        <td>${formatDateTime(r.created_at)}</td>
        <td>${formatDateTime(r.finished_at)}</td>
        <td style="max-width:320px; white-space:normal;">${formatCell(r.services)}</td>
        <td>${formatCell(r.customer_name)}</td>
        <td>${formatCell(r.customer_phone)}</td>
        <td>${formatCell(r.customer_email)}</td>
      `;
      tbody.appendChild(tr);

      if (mobileList) {
        const collapseId = `finishedMobile${index}`;
        const card = document.createElement('div');
        card.className = 'mobile-table-card';
        card.setAttribute('data-search-text', `${formatCell(r.office_description)} ${formatCell(r.processed_by)} ${formatCell(r.services)} ${formatCell(r.customer_name)} ${formatCell(r.customer_phone)} ${formatCell(r.customer_email)}`);
        card.innerHTML = `
          <button class="mobile-table-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#${collapseId}" aria-expanded="false" aria-controls="${collapseId}">
            <span class="mobile-table-icon"><i class="fa-solid fa-check-double"></i></span>
            <span class="mobile-table-summary">
              <span class="mobile-table-title">${formatCell(r.office_description)} · Window ${formatCell(r.window_no && r.window_no > 0 ? r.window_no : (r.window_id && r.window_id > 0 ? r.window_id : '—'))}</span>
              <span class="mobile-table-meta">${formatCell(r.customer_name)} · ${formatCell(r.finished_at)}</span>
            </span>
            <span class="mobile-table-chevron"><i class="fa-solid fa-chevron-down"></i></span>
          </button>
          <div id="${collapseId}" class="collapse mobile-table-body">
            <div class="mobile-table-grid">
              <div class="mobile-table-field"><span class="mobile-table-label">Processed By</span><span class="mobile-table-value">${formatCell(r.processed_by)}</span></div>
              <div class="mobile-table-field"><span class="mobile-table-label">Created At</span><span class="mobile-table-value">${formatCell(r.created_at)}</span></div>
              <div class="mobile-table-field"><span class="mobile-table-label">Finished At</span><span class="mobile-table-value">${formatCell(r.finished_at)}</span></div>
              <div class="mobile-table-field"><span class="mobile-table-label">Services</span><span class="mobile-table-value">${formatCell(r.services)}</span></div>
              <div class="mobile-table-field"><span class="mobile-table-label">Phone</span><span class="mobile-table-value">${formatCell(r.customer_phone)}</span></div>
              <div class="mobile-table-field"><span class="mobile-table-label">Email</span><span class="mobile-table-value">${formatCell(r.customer_email)}</span></div>
            </div>
          </div>
        `;
        mobileList.appendChild(card);
      }
    });

    if (window.mobileTableCards) {
      window.mobileTableCards.register('finishedMobileSearch', 'finishedMobileList', 'finishedMobileEmpty', {
        emptyText: 'No finished queues found.',
        emptySearchText: 'No finished queues match your search.'
      });
    }

    // Initialize DataTable after rows are added
    dataTable = $('#finishedTable').DataTable({
      responsive: true,
      ordering: true,
      pageLength: 10,
      order: [[4, 'desc']], // Sort by Finished At column (descending)
      language: {
        lengthMenu: 'Show _MENU_ entries',
        info: 'Showing _START_ to _END_ of _TOTAL_ entries',
        search: "Search:",
      }
    });

    $('.dataTables_filter input').attr('placeholder', 'Search finished queues...');
  }

  function populateOffices(list){
    if (!Array.isArray(list)) return;
    // keep existing "All" option at value 0
    list.forEach(o => {
      const opt = document.createElement('option');
      opt.value = o.office_id;
      opt.textContent = o.office_description;
      officeSelect.appendChild(opt);
    });
  }

  async function load(){
    const office = officeSelect.value || 0;
    const url = api + (office && parseInt(office) ? '?office_id=' + encodeURIComponent(office) : '');
    try {
      const res = await fetch(url, { credentials: 'same-origin' });
      if (!res.ok) throw new Error('Server error');
      const json = await res.json();
      if (Array.isArray(json.offices) && officeSelect.options.length <= 1) populateOffices(json.offices);
      renderRows(json.rows || []);
    } catch (err) {
      tbody.innerHTML = `<tr><td colspan="9" class="small-muted">Failed to load data</td></tr>`;
      console.error(err);
    }
  }

  officeSelect.addEventListener('change', load);
  refreshBtn.addEventListener('click', load);

  document.addEventListener('DOMContentLoaded', load);

  // CSV Export functionality
  const exportCSVBtn = document.getElementById('exportCSVBtn');
  exportCSVBtn && exportCSVBtn.addEventListener('click', function() {
    if (!currentData || !currentData.length) {
      alert('No data to export');
      return;
    }

    // Create CSV content
    const headers = ['Office', 'Window', 'Processed By', 'Created At', 'Finished At', 'Services', 'Name', 'Phone', 'Email'];
    let csvContent = headers.join(',') + '\n';

    currentData.forEach(r => {
      const row = [
        `"${(r.office_description || '').replace(/"/g, '""')}"`,
        r.window_no || r.window_id || '',
        `"${(r.processed_by || '').replace(/"/g, '""')}"`,
        `"${(r.created_at || '').replace(/"/g, '""')}"`,
        `"${(r.finished_at || '').replace(/"/g, '""')}"`,
        `"${(r.services || '').replace(/"/g, '""')}"`,
        `"${(r.customer_name || '').replace(/"/g, '""')}"`,
        `"${(r.customer_phone || '').replace(/"/g, '""')}"`,
        `"${(r.customer_email || '').replace(/"/g, '""')}"`
      ];
      csvContent += row.join(',') + '\n';
    });

    // Create download link
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const downloadUrl = URL.createObjectURL(blob);
    link.setAttribute('href', downloadUrl);
    link.setAttribute('download', `finished_queues_${new Date().toISOString().slice(0,10)}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(downloadUrl);
  });
})();

// Modal & Report generation helpers
(function(){
  const openBtn = document.getElementById('openReportBtn');
  const modalElement = document.getElementById('reportModal');
  const officeSelect = document.getElementById('officeFilter');
  const modalOffice = document.getElementById('r_office');
  const modalStaff = document.getElementById('r_staff');
  const reportForm = document.getElementById('reportForm');

  let modal = null;

  // Initialize Bootstrap modal
  if (modalElement) {
    modal = new bootstrap.Modal(modalElement);
  }

  function showModal(){
    // clone office options into modal office select
    modalOffice.innerHTML = '';
    Array.from(officeSelect.options).forEach(opt => {
      const o = document.createElement('option');
      o.value = opt.value;
      o.textContent = opt.textContent;
      modalOffice.appendChild(o);
    });
    // load staff list for current office
    loadStaffList(modalOffice.value);
    if (modal) modal.show();
  }

  async function loadStaffList(officeId){
    modalStaff.innerHTML = '<option value="">Any</option>';
    try {
      const url = '../backend/staffList.php' + (officeId && parseInt(officeId)>0 ? '?office_id=' + encodeURIComponent(officeId) : '');
      const res = await fetch(url, { credentials: 'same-origin' });
      if (!res.ok) throw new Error('Failed');
      const json = await res.json();
      if (Array.isArray(json) && json.length) {
        json.forEach(s => {
          const opt = document.createElement('option');
          opt.value = s.name || s.user_name || '';
          opt.textContent = s.name || s.user_name || '';
          modalStaff.appendChild(opt);
        });
      }
    } catch (e) {
      console.error('staff load', e);
    }
  }

  openBtn && openBtn.addEventListener('click', showModal);
  modalOffice && modalOffice.addEventListener('change', e => loadStaffList(e.target.value));

  // Optional: validate date range before submit
  reportForm && reportForm.addEventListener('submit', function(e){
    const s = document.getElementById('r_start').value;
    const en = document.getElementById('r_end').value;
    if (s && en && s > en) {
      e.preventDefault();
      alert('Start date must be earlier than end date');
      return false;
    }
    // allow form to submit (target _blank opens in new tab)
  });

})();