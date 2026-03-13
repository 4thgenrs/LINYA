(function(){
  const api = '../backend/voidQueueData.php';
  const officeSelect = document.getElementById('officeFilter');
  const tbody = document.querySelector('#voidTable tbody');
  const refreshBtn = document.getElementById('refreshBtn');
  const detailsContent = document.getElementById('voidDetailsContent');
  const detailsModalElement = document.getElementById('voidDetailsModal');
  const detailsModal = detailsModalElement ? new bootstrap.Modal(detailsModalElement) : null;
  let dataTable = null;
  let currentRows = [];

  function escapeHtml(value){
    if (value === null || typeof value === 'undefined') return '';
    return String(value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function formatCell(v){ return (v === null || typeof v === 'undefined' || v === '') ? '—' : escapeHtml(v); }
  function formatDateTime(v){ if (!v || v === '—') return '—'; const parts = String(v).split(' '); if (parts.length===2) return `<div>${parts[0]}</div><div class="small">${parts[1]}</div>`; return v; }

  function buildDetailItem(label, value, options = {}) {
    const classes = options.full ? 'detail-item detail-item-full' : 'detail-item';
    return `
      <div class="${classes}">
        <span class="detail-label">${escapeHtml(label)}:</span>
        <span class="detail-value">${value}</span>
      </div>
    `;
  }

  function showDetails(index) {
    const row = currentRows[index];
    if (!row || !detailsContent || !detailsModal) return;

    const leftColumn = [
      buildDetailItem('Control #', formatCell(row.original_queue_id)),
      buildDetailItem('Student Name', formatCell(row.customer_name)),
      buildDetailItem('Phone', formatCell(row.customer_phone)),
      buildDetailItem('Processed By', formatCell(row.processed_by)),
      buildDetailItem('Window', formatCell(row.window_no || row.window_id)),
      buildDetailItem('Remarks', formatCell(row.remarks), { full: true })
    ].join('');

    const rightColumn = [
      buildDetailItem('Date Submitted', formatDateTime(row.created_at)),
      buildDetailItem('Student ID', formatCell(row.student_number || row.studentNumber)),
      buildDetailItem('Email', formatCell(row.customer_email)),
      buildDetailItem('Program', formatCell(row.course)),
      buildDetailItem('Office', formatCell(row.office_id || row.office_description)),
      buildDetailItem('Voided At', formatDateTime(row.process_finished || row.finished_at)),
      buildDetailItem('Process Start', formatDateTime(row.process_start)),
      buildDetailItem('Process Finished', formatDateTime(row.process_finished)),
      buildDetailItem('Processing Duration', formatCell(row.processing_duration)),
      buildDetailItem('Services', formatCell(row.services), { full: true })
    ].join('');

    const html = `
      <div class="detail-layout">
        <div class="detail-grid">
          <div class="detail-column">${leftColumn}</div>
          <div class="detail-column">
            ${rightColumn}
          </div>
        </div>
      </div>
    `;

    detailsContent.innerHTML = html;
    detailsModal.show();
  }

  function renderRows(rows){
    if (dataTable) { dataTable.destroy(); dataTable = null; }
    currentRows = Array.isArray(rows) ? rows : [];
    tbody.innerHTML = '';
    if (!rows || !rows.length) { tbody.innerHTML = `<tr><td colspan="7" class="small-muted">No records</td></tr>`; return; }

    rows.forEach((r, index) => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${formatCell(r.original_queue_id)}</td>
        <td>${formatCell(r.office_id || r.office_description)}</td>
        <td>${formatCell(r.customer_name)}</td>
        <td>${formatCell(r.student_number || r.studentNumber)}</td>
        <td>${formatCell(r.course)}</td>
        <td>${formatCell(r.processed_by)}</td>
        <td><button type="button" class="btn btn-sm btn-outline-success view-details-btn" data-index="${index}"><i class="fa-solid fa-eye me-1"></i>View</button></td>
      `;
      tbody.appendChild(tr);
    });

    dataTable = $('#voidTable').DataTable({
      responsive: true,
      ordering: true,
      pageLength: 10,
      order: [[0,'desc']],
      language: {
        lengthMenu: 'Show _MENU_ entries',
        info: 'Showing _START_ to _END_ of _TOTAL_ entries',
        search: 'Search:'
      },
      columns: [
        null,
        null,
        null,
        null,
        null,
        null,
        { orderable: false, searchable: false }
      ]
    });

    $('.dataTables_filter input').attr('placeholder', 'Search voided queues...');
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
      tbody.innerHTML = `<tr><td colspan="7" class="small-muted">Failed to load data</td></tr>`;
      console.error(e);
    }
  }

  tbody.addEventListener('click', (event) => {
    const button = event.target.closest('.view-details-btn');
    if (!button) return;
    showDetails(parseInt(button.dataset.index, 10));
  });

  officeSelect.addEventListener('change', load);
  refreshBtn.addEventListener('click', load);
  document.addEventListener('DOMContentLoaded', load);
})();