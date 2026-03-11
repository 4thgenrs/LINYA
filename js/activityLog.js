// Fetch backend/activityLogData.php and render table; shows only current user's processed queues
(function(){
  const api = '../backend/activityLogData.php';
  const officeSelect = document.getElementById('officeFilter');
  const filterType = document.getElementById('filterType');
  const filterDate = document.getElementById('filterDate');
  const filterMonth = document.getElementById('filterMonth');
  const startDate = document.getElementById('startDate');
  const endDate = document.getElementById('endDate');
  const tbody = document.querySelector('#activityTable tbody');
  const mobileList = document.getElementById('activityMobileList');

  function formatCell(v){
    return (v === null || typeof v === 'undefined' || v === '') ? '—' : String(v);
  }

  function renderRows(rows){
    tbody.innerHTML = '';
    if (mobileList) mobileList.innerHTML = '';
    if (!rows || !rows.length) {
      const tr = document.createElement('tr');
      tr.innerHTML = `<td colspan="8" class="small-muted">No records</td>`;
      tbody.appendChild(tr);
      if (window.mobileTableCards) {
        window.mobileTableCards.register('activityMobileSearch', 'activityMobileList', 'activityMobileEmpty', {
          emptyText: 'No activity records found.',
          emptySearchText: 'No activity records match your search.'
        });
      }
      return;
    }
    rows.forEach((r, index) => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${formatCell(r.office_description)}</td>
        <td>${formatCell(r.window_no && r.window_no > 0 ? r.window_no : (r.window_id && r.window_id > 0 ? r.window_id : '—'))}</td>
        <td>${formatCell(r.created_at)}</td>
        <td>${formatCell(r.finished_at)}</td>
        <td style="max-width:320px; white-space:normal;">${formatCell(r.services)}</td>
        <td>${formatCell(r.customer_name)}</td>
        <td>${formatCell(r.customer_phone)}</td>
        <td>${formatCell(r.customer_email)}</td>
      `;
      tbody.appendChild(tr);

      if (mobileList) {
        const collapseId = `activityMobile${index}`;
        const card = document.createElement('div');
        card.className = 'mobile-table-card';
        card.setAttribute('data-search-text', `${formatCell(r.office_description)} ${formatCell(r.services)} ${formatCell(r.customer_name)} ${formatCell(r.customer_phone)} ${formatCell(r.customer_email)}`);
        card.innerHTML = `
          <button class="mobile-table-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#${collapseId}" aria-expanded="false" aria-controls="${collapseId}">
            <span class="mobile-table-icon"><i class="fa-solid fa-clock-rotate-left"></i></span>
            <span class="mobile-table-summary">
              <span class="mobile-table-title">${formatCell(r.office_description)} · Window ${formatCell(r.window_no && r.window_no > 0 ? r.window_no : (r.window_id && r.window_id > 0 ? r.window_id : '—'))}</span>
              <span class="mobile-table-meta">${formatCell(r.customer_name)} · ${formatCell(r.finished_at)}</span>
            </span>
            <span class="mobile-table-chevron"><i class="fa-solid fa-chevron-down"></i></span>
          </button>
          <div id="${collapseId}" class="collapse mobile-table-body">
            <div class="mobile-table-grid">
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
      window.mobileTableCards.register('activityMobileSearch', 'activityMobileList', 'activityMobileEmpty', {
        emptyText: 'No activity records found.',
        emptySearchText: 'No activity records match your search.'
      });
    }
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

  function updateFilterInputs(){
    const t = filterType.value;
    filterDate.style.display = t === 'day' ? '' : 'none';
    filterMonth.style.display = t === 'month' ? '' : 'none';
    startDate.style.display = t === 'range' ? '' : 'none';
    endDate.style.display = t === 'range' ? '' : 'none';
  }

  async function load(){
    const office = officeSelect.value || 0;
    const params = {};
    if (office && parseInt(office)) params.office_id = office;

    const t = filterType.value;
    if (t && t !== 'all') {
      params.filter_type = t;
      if (t === 'day' && filterDate.value) params.date = filterDate.value;
      if (t === 'month' && filterMonth.value) params.month = filterMonth.value;
      if (t === 'range' && startDate.value && endDate.value) {
        params.start = startDate.value;
        params.end = endDate.value;
      }
    }

    const qs = new URLSearchParams(params).toString();
    const url = api + (qs ? '?' + qs : '');
    try {
      const res = await fetch(url, { credentials: 'same-origin' });
      if (!res.ok) {
        if (res.status === 401) {
          tbody.innerHTML = `<tr><td colspan="8" class="small-muted">Unauthorized — please log in</td></tr>`;
          return;
        }
        throw new Error('Server error');
      }
      const json = await res.json();
      if (Array.isArray(json.offices) && officeSelect.options.length <= 1) populateOffices(json.offices);
      renderRows(json.rows || []);
    } catch (err) {
      tbody.innerHTML = `<tr><td colspan="8" class="small-muted">Failed to load data</td></tr>`;
      console.error(err);
    }
  }

  officeSelect.addEventListener('change', load);
  filterType.addEventListener('change', () => { updateFilterInputs(); load(); });
  filterDate.addEventListener('change', load);
  filterMonth.addEventListener('change', load);
  startDate.addEventListener('change', load);
  endDate.addEventListener('change', load);

  document.addEventListener('DOMContentLoaded', () => { updateFilterInputs(); load(); });
})();
