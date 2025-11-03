// Fetch finishedQueueData.php and render table; supports office filter
(function(){
  const api = '../backend/finishedQueueData.php';
  const officeSelect = document.getElementById('officeFilter');
  const tbody = document.querySelector('#finishedTable tbody');
  const refreshBtn = document.getElementById('refreshBtn');

  function formatCell(v){
    return (v === null || typeof v === 'undefined' || v === '') ? '—' : String(v);
  }

  function renderRows(rows){
    tbody.innerHTML = '';
    if (!rows || !rows.length) {
      const tr = document.createElement('tr');
      tr.innerHTML = `<td colspan="9" class="small-muted">No records</td>`;
      tbody.appendChild(tr);
      return;
    }
    rows.forEach(r => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${formatCell(r.office_description)}</td>
        <td>${formatCell(r.window_no && r.window_no > 0 ? r.window_no : (r.window_id && r.window_id > 0 ? r.window_id : '—'))}</td>
        <td>${formatCell(r.processed_by)}</td>
        <td>${formatCell(r.created_at)}</td>
        <td>${formatCell(r.finished_at)}</td>
        <td style="max-width:320px; white-space:normal;">${formatCell(r.services)}</td>
        <td>${formatCell(r.customer_name)}</td>
        <td>${formatCell(r.customer_phone)}</td>
        <td>${formatCell(r.customer_email)}</td>
      `;
      tbody.appendChild(tr);
    });
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
})();