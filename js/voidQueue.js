(function(){
  const api = '../backend/voidQueueData.php';
  const officeSelect = document.getElementById('officeFilter');
  const tbody = document.querySelector('#voidTable tbody');
  const refreshBtn = document.getElementById('refreshBtn');
  let dataTable = null;

  function formatCell(v){ return (v === null || typeof v === 'undefined' || v === '') ? '—' : String(v); }
  function formatDateTime(v){ if (!v || v === '—') return '—'; const parts = String(v).split(' '); if (parts.length===2) return `<div>${parts[0]}</div><div class="small">${parts[1]}</div>`; return v; }

  function renderRows(rows){
    if (dataTable) { dataTable.destroy(); dataTable = null; }
    tbody.innerHTML = '';
    if (!rows || !rows.length) { tbody.innerHTML = `<tr><td colspan="17" class="small-muted">No records</td></tr>`; return; }

    rows.forEach(r => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${formatCell(r.office_id || r.office_description)}</td>
        <td>${formatCell(r.window_no || r.window_id)}</td>
        <td>${formatCell(r.processed_by)}</td>
        <td>${formatDateTime(r.created_at)}</td>
        <td>${formatDateTime(r.process_finished || r.finished_at)}</td>
        <td>${formatDateTime(r.process_start)}</td>
        <td>${formatDateTime(r.process_finished)}</td>
        <td>${formatCell(r.processing_duration)}</td>
        <td style="max-width:240px;white-space:normal;">${formatCell(r.remarks)}</td>
        <td style="max-width:320px;white-space:normal;">${formatCell(r.services)}</td>
        <td>${formatCell(r.customer_name)}</td>
        <td>${formatCell(r.customer_phone)}</td>
        <td>${formatCell(r.customer_email)}</td>
        <td>${formatCell(r.student_number || r.studentNumber)}</td>
        <td>${formatCell(r.course)}</td>
        <td>${formatCell(r.original_queue_id)}</td>
        <td>${formatCell(r.finished_id)}</td>
      `;
      tbody.appendChild(tr);
    });

    dataTable = $('#voidTable').DataTable({
      responsive: true,
      ordering: true,
      pageLength: 10,
      order: [[4,'desc']],
      columns: Array.from({length:17}, () => ({ }))
    });
  }

  async function load(){
    const office = officeSelect.value || 0;
    const url = api + (office && parseInt(office) ? '?office_id=' + encodeURIComponent(office) : '');
    try {
      const res = await fetch(url, { credentials: 'same-origin' });
      if (!res.ok) throw new Error('Server error');
      const json = await res.json();
      if (Array.isArray(json.offices) && officeSelect.options.length <= 1) {
        json.offices.forEach(o => { const opt = document.createElement('option'); opt.value = o.office_id; opt.textContent = o.office_description; officeSelect.appendChild(opt); });
      }
      renderRows(json.rows || []);
    } catch (e) {
      tbody.innerHTML = `<tr><td colspan="17" class="small-muted">Failed to load data</td></tr>`;
      console.error(e);
    }
  }

  officeSelect.addEventListener('change', load);
  refreshBtn.addEventListener('click', load);
  document.addEventListener('DOMContentLoaded', load);
})();
