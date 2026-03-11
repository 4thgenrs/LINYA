<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || intval($_SESSION['user_type']) !== 1) {
    header('Location: login.php');
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Face Recognition Clients</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <style>
    * { box-sizing: border-box; }
    body { margin: 0; font-family: Inter, Segoe UI, Roboto, Arial, sans-serif; background: linear-gradient(180deg, #f4f7fb 0%, #eef4ff 100%); color: #111827; }
    .container { padding: 28px; max-width: 1320px; margin: 0 auto; }
    h1 { color: #157347; margin: 0; font-size: 28px; letter-spacing: -0.02em; }
    .page-header { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 20px; padding: 20px 22px; border-radius: 18px; background: linear-gradient(135deg, #ffffff, #f3fbf6); border: 1px solid #cfe2d6; box-shadow: 0 14px 30px rgba(15,23,42,0.06); }
    .page-header-main { display: flex; align-items: center; gap: 14px; }
    .page-header-icon { width: 52px; height: 52px; border-radius: 14px; display: inline-flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #157347, #198754); color: #fff; box-shadow: 0 12px 24px rgba(25, 135, 84, 0.28); font-size: 20px; }
    .page-subtitle { margin: 6px 0 0; color: #5f6b66; font-size: 14px; }
    .table-container { background: #fff; border-radius: 18px; overflow: hidden; box-shadow: 0 18px 40px rgba(15,23,42,0.08); border: 1px solid #e5e7eb; }
    .table-header { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 18px 20px; background: linear-gradient(135deg, #157347, #198754); color: #fff; }
    .table-header-title { display: flex; align-items: center; gap: 12px; }
    .table-header-icon { width: 42px; height: 42px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; background: rgba(255, 255, 255, 0.16); font-size: 18px; }
    .table-header h2 { margin: 0; font-size: 18px; font-weight: 700; }
    .table-header p { margin: 2px 0 0; font-size: 13px; color: rgba(255, 255, 255, 0.86); }
    .table-body { padding: 8px; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #d1e7dd; padding: 14px 12px; text-align: left; font-weight: 700; color: #0f5132; border-bottom: 1px solid #badbcc; font-size: 13px; text-transform: uppercase; letter-spacing: 0.04em; }
    td { padding: 14px 12px; border-bottom: 1px solid #eef2f7; vertical-align: middle; }
    tr:hover { background: #f3fbf6; }
    .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; }
    .badge-info { background: #d1e7dd; color: #0f5132; }
    .btn-delete { border-radius: 10px; font-size: 12px; font-weight: 700; transition: all 0.2s; box-shadow: 0 8px 18px rgba(220, 53, 69, 0.18); display: inline-flex; align-items: center; }
    .btn-delete:hover { transform: translateY(-1px); box-shadow: 0 10px 20px rgba(220, 53, 69, 0.24); }
    .btn-delete:disabled { cursor: not-allowed; box-shadow: none; }
    .loading { text-align: center; padding: 40px; color: #6b7280; }
    .alert-banner { display: none; align-items: center; gap: 10px; border: 0; border-radius: 14px; margin-bottom: 18px; padding: 14px 16px; box-shadow: 0 12px 28px rgba(15, 23, 42, 0.06); }
    .alert-banner i { font-size: 18px; }
    .error { background: linear-gradient(135deg, #fef2f2, #fee2e2); color: #991b1b; }
    .success { background: linear-gradient(135deg, #ecfdf5, #dcfce7); color: #166534; }
    .empty { text-align: center; padding: 40px; color: #9ca3af; }
    .mobile-search-wrap { margin-bottom: 14px; }
    .mobile-client-list { display: grid; gap: 12px; }
    .mobile-client-card { border: 1px solid #e4ece7; border-radius: 16px; background: linear-gradient(135deg, #ffffff, #f8fbf9); box-shadow: 0 10px 22px rgba(15,23,42,0.05); overflow: hidden; }
    .mobile-client-toggle { width: 100%; border: 0; background: transparent; display: flex; align-items: center; gap: 12px; padding: 14px; text-align: left; }
    .mobile-client-toggle:focus { outline: 0; box-shadow: inset 0 0 0 2px rgba(25, 135, 84, 0.18); }
    .mobile-client-avatar { width: 38px; height: 38px; border-radius: 12px; flex-shrink: 0; display: inline-flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #157347, #198754); color: #fff; box-shadow: 0 10px 18px rgba(25,135,84,0.22); }
    .mobile-client-summary { min-width: 0; flex: 1; }
    .mobile-client-title { font-size: 14px; font-weight: 700; color: #16324f; line-height: 1.45; word-break: break-word; }
    .mobile-client-meta { display: block; margin-top: 4px; color: #64748b; font-size: 12px; }
    .mobile-client-chevron { color: #157347; font-size: 14px; transition: transform 0.2s ease; }
    .mobile-client-toggle[aria-expanded="true"] .mobile-client-chevron { transform: rotate(180deg); }
    .mobile-client-body { border-top: 1px solid #edf3ef; padding: 14px; }
    .mobile-client-detail { margin-bottom: 10px; }
    .mobile-client-detail:last-child { margin-bottom: 0; }
    .mobile-client-detail-label { display: block; margin-bottom: 4px; color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; }
    .mobile-client-detail-value { color: #16324f; font-size: 13px; line-height: 1.5; word-break: break-word; }
    .mobile-client-actions { display: flex; gap: 10px; margin-top: 14px; }
    .mobile-client-actions .btn-delete { width: 100%; justify-content: center; }
    .mobile-empty { text-align: center; padding: 22px 16px; color: #94a3b8; border: 1px dashed #dbe7df; border-radius: 16px; background: #fbfdfb; }
    .modal-content { border: 0; border-radius: 18px; box-shadow: 0 20px 45px rgba(15, 23, 42, 0.18); }
    .modal-header,
    .modal-footer { border-color: #e5e7eb; }
    .modal-title { font-size: 18px; font-weight: 700; margin: 0; }
    .modal-message { color: #6b7280; margin: 0; line-height: 1.6; }
    .btn { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; transition: all 0.2s; }
    .btn-primary { background: #0f62fe; color: white; }
    .btn-primary:hover { background: #0353e9; }
    .btn-secondary { background: #e5e7eb; color: #111; }
    .btn-secondary:hover { background: #d1d5db; }
    .stats { display: flex; gap: 20px; margin-bottom: 20px; }
    .stat-card { background: linear-gradient(135deg, #ffffff, #f3fbf6); padding: 18px 20px; border-radius: 16px; border: 1px solid #cfe2d6; box-shadow: 0 14px 30px rgba(15,23,42,0.06); min-width: 260px; display: flex; align-items: center; justify-content: space-between; gap: 14px; }
    .stat-card-icon { width: 52px; height: 52px; border-radius: 14px; display: inline-flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #157347, #198754); color: #fff; box-shadow: 0 12px 24px rgba(25, 135, 84, 0.28); font-size: 20px; }
    .stat-label { color: #6b7280; font-size: 12px; font-weight: 600; }
    .stat-value { font-size: 24px; font-weight: 700; color: #157347; }
    #clientsTable { border-radius: 14px; overflow: hidden; margin-bottom: 0 !important; }
    #clientsTable_wrapper { padding: 14px 12px 8px; }
    .dataTables_wrapper .row { align-items: center; }
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter { margin-bottom: 14px; }
    .dataTables_wrapper .dataTables_filter { text-align: right; }
    .dataTables_wrapper .dataTables_length label,
    .dataTables_wrapper .dataTables_filter label,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate { color: #475569; font-size: 14px; }
    .dataTables_wrapper .dataTables_length label,
    .dataTables_wrapper .dataTables_filter label { display: inline-flex; align-items: center; gap: 10px; font-weight: 600; }
    .dataTables_wrapper .dataTables_filter input,
    .dataTables_wrapper .dataTables_length select {
      border: 1px solid #dbe3ef;
      border-radius: 10px;
      padding: 8px 12px;
      background: #fff;
      color: #111827;
      outline: none;
      transition: border-color 0.2s, box-shadow 0.2s;
    }
    .dataTables_wrapper .dataTables_filter input:focus,
    .dataTables_wrapper .dataTables_length select:focus {
      border-color: #75b798;
      box-shadow: 0 0 0 4px rgba(25, 135, 84, 0.16);
    }
    .dataTables_wrapper .dataTables_filter input { min-width: 260px; }
    .dataTables_wrapper .dataTables_length select { min-width: 82px; }
    .dataTables_wrapper .pagination { gap: 6px; }
    .dataTables_wrapper .page-item .page-link {
      border-radius: 10px !important;
      border: 1px solid #dbe3ef !important;
      color: #157347;
      margin: 0;
      transition: all 0.2s ease;
      padding: 0.5rem 0.85rem;
    }
    .dataTables_wrapper .page-item .page-link:hover {
      background: #eefaf2 !important;
      border-color: #badbcc !important;
      color: #157347 !important;
    }
    .dataTables_wrapper .page-item.active .page-link,
    .dataTables_wrapper .page-item.active .page-link:hover {
      background: linear-gradient(135deg, #157347, #198754) !important;
      color: #fff !important;
      border-color: transparent !important;
      box-shadow: 0 10px 20px rgba(25, 135, 84, 0.2);
    }
    .dataTables_wrapper .page-item.disabled .page-link {
      color: #94a3b8 !important;
      background: #f8fafc !important;
      border-color: #e2e8f0 !important;
    }
    .dataTables_wrapper .dataTables_info { padding-top: 18px; }
    .table > :not(caption) > * > * { box-shadow: none; }
    @media (max-width: 768px) {
      .container { padding: 18px; }
      .page-header { flex-direction: column; align-items: flex-start; }
      .page-header-main { align-items: flex-start; }
      .stats { flex-direction: column; }
      .stat-card { min-width: 0; }
      .table-container { padding: 0; }
      .table-header { padding: 16px; }
      .table-header-title { align-items: flex-start; }
      .table-body { padding: 14px; }
      .dataTables_wrapper .dataTables_length,
      .dataTables_wrapper .dataTables_filter,
      .dataTables_wrapper .dataTables_info,
      .dataTables_wrapper .dataTables_paginate {
        text-align: left;
      }
      .dataTables_wrapper .dataTables_filter label,
      .dataTables_wrapper .dataTables_length label { display: flex; width: 100%; }
      .dataTables_wrapper .dataTables_filter input { width: 100%; min-width: 0; margin-left: 0; }
    }
  </style>
  <link rel="stylesheet" href="../css/table-unified.css">
</head>
<body>

<div class="container-fluid p-3">
  <div class="page-header">
    <div class="page-header-main">
      <div class="page-header-icon">
        <i class="fa-solid fa-users-viewfinder"></i>
      </div>
      <div>
        <h1>Face Recognition Clients</h1>
        <p class="page-subtitle">Manage registered face recognition profiles with searchable, sortable client records.</p>
      </div>
    </div>
  </div>

  <div class="stats">
    <div class="stat-card">
      <div>
        <div class="stat-label">Total Clients</div>
        <div class="stat-value" id="statTotal">—</div>
      </div>
      <div class="stat-card-icon">
        <i class="fa-solid fa-id-card"></i>
      </div>
    </div>
  </div>

  <div id="msgSuccess" class="alert-banner success" role="alert" aria-live="polite">
    <i class="fa-solid fa-circle-check"></i>
    <span></span>
  </div>
  <div id="msgError" class="alert-banner error" role="alert" aria-live="assertive">
    <i class="fa-solid fa-circle-exclamation"></i>
    <span></span>
  </div>

  <div class="table-container">
    <div class="table-header">
      <div class="table-header-title">
        <div class="table-header-icon">
          <i class="fa-solid fa-table-list"></i>
        </div>
        <div>
          <h2>Client Directory</h2>
          <p>Browse, search, and manage enrolled clients.</p>
        </div>
      </div>
    </div>
    <div class="table-body">
      <div id="loadingMsg" class="loading">Loading clients...</div>
      <div id="emptyMsg" class="empty" style="display: none;">No clients registered yet.</div>
      <div class="d-none d-md-block">
        <div class="table-responsive">
        <table id="clientsTable" class="table table-hover align-middle w-100" style="display: none;">
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Safe Name</th>
              <th>Created At</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="clientsBody">
          </tbody>
        </table>
        </div>
      </div>
      <div class="d-block d-md-none">
        <div class="mobile-search-wrap">
          <input type="text" class="form-control" id="mobileClientSearch" placeholder="Search clients..." onkeyup="filterMobileClients()">
        </div>
        <div id="mobileEmptyMsg" class="mobile-empty" style="display: none;">No clients registered yet.</div>
        <div id="mobileClientsList" class="mobile-client-list"></div>
      </div>
    </div>
  </div>
</div>

<script>
let clients = [];
let clientsDataTable = null;

async function loadClients() {
  try {
    const resp = await fetch('../backend/adminClientsData.php');
    const json = await resp.json();

    if (!json.success) {
      showError(json.error || 'Failed to load clients');
      return;
    }

    clients = json.clients || [];
    renderTable();
  } catch (e) {
    console.error('Load error:', e);
    showError('Network error: ' + e.message);
  }
}

function renderTable() {
  const table = document.getElementById('clientsTable');
  const tbody = document.getElementById('clientsBody');
  const loadingMsg = document.getElementById('loadingMsg');
  const emptyMsg = document.getElementById('emptyMsg');
  const mobileEmptyMsg = document.getElementById('mobileEmptyMsg');
  const mobileClientsList = document.getElementById('mobileClientsList');
  const statTotal = document.getElementById('statTotal');

  loadingMsg.style.display = 'none';

  destroyDataTable();

  if (clients.length === 0) {
    table.style.display = 'none';
    emptyMsg.style.display = 'block';
    if (mobileEmptyMsg) mobileEmptyMsg.style.display = 'block';
    if (mobileClientsList) mobileClientsList.innerHTML = '';
    statTotal.textContent = '0';
    return;
  }

  table.style.display = 'table';
  emptyMsg.style.display = 'none';
  if (mobileEmptyMsg) mobileEmptyMsg.style.display = 'none';
  statTotal.textContent = clients.length;

  tbody.innerHTML = clients.map(client => `
    <tr>
      <td>${client.client_id}</td>
      <td>${escapeHtml(client.name)}</td>
      <td>${client.email ? escapeHtml(client.email) : '—'}</td>
      <td>${client.phone ? escapeHtml(client.phone) : '—'}</td>
      <td><span class="badge badge-info">${escapeHtml(client.safe_name)}</span></td>
      <td>${formatDate(client.created_at)}</td>
      <td>
        <button class="btn btn-danger btn-sm btn-delete" onclick="openDeleteConfirm(${client.client_id}, '${escapeAttr(client.name)}')">
          <i class="fa-solid fa-trash me-1"></i>Delete
        </button>
      </td>
    </tr>
  `).join('');

  if (mobileClientsList) {
    mobileClientsList.innerHTML = clients.map(client => {
      const collapseId = `clientMobile${client.client_id}`;
      const searchable = `${client.name || ''} ${client.email || ''} ${client.phone || ''} ${client.safe_name || ''}`;
      return `
        <div class="mobile-client-card" data-search-text="${escapeHtml(searchable).toLowerCase()}">
          <button class="mobile-client-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#${collapseId}" aria-expanded="false" aria-controls="${collapseId}">
            <span class="mobile-client-avatar"><i class="fa-solid fa-user"></i></span>
            <span class="mobile-client-summary">
              <span class="mobile-client-title">${escapeHtml(client.name)}</span>
              <span class="mobile-client-meta">${client.email ? escapeHtml(client.email) : 'No email provided'}</span>
            </span>
            <span class="mobile-client-chevron"><i class="fa-solid fa-chevron-down"></i></span>
          </button>
          <div id="${collapseId}" class="collapse mobile-client-body">
            <div class="mobile-client-detail">
              <span class="mobile-client-detail-label">Client ID</span>
              <span class="mobile-client-detail-value">${client.client_id}</span>
            </div>
            <div class="mobile-client-detail">
              <span class="mobile-client-detail-label">Phone</span>
              <span class="mobile-client-detail-value">${client.phone ? escapeHtml(client.phone) : '—'}</span>
            </div>
            <div class="mobile-client-detail">
              <span class="mobile-client-detail-label">Safe Name</span>
              <span class="mobile-client-detail-value"><span class="badge badge-info">${escapeHtml(client.safe_name)}</span></span>
            </div>
            <div class="mobile-client-detail">
              <span class="mobile-client-detail-label">Created At</span>
              <span class="mobile-client-detail-value">${formatDate(client.created_at)}</span>
            </div>
            <div class="mobile-client-actions">
              <button class="btn btn-danger btn-sm btn-delete" onclick="openDeleteConfirm(${client.client_id}, '${escapeAttr(client.name)}')">
                <i class="fa-solid fa-trash-can me-1"></i>Delete
              </button>
            </div>
          </div>
        </div>
      `;
    }).join('');
  }

  initializeDataTable();
}

function destroyDataTable() {
  if (window.jQuery && $.fn.DataTable && $.fn.DataTable.isDataTable('#clientsTable')) {
    $('#clientsTable').DataTable().destroy();
  }
  clientsDataTable = null;
}

function initializeDataTable() {
  if (!window.jQuery || !$.fn.DataTable) return;

  clientsDataTable = $('#clientsTable').DataTable({
    dom: "<'row g-3 align-items-center mb-3'<'col-md-6'l><'col-md-6 text-md-end'f>>" +
         "t" +
         "<'row g-3 align-items-center mt-2'<'col-md-5'i><'col-md-7 text-md-end'p>>",
    pageLength: 10,
    lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]],
    order: [[0, 'desc']],
    columnDefs: [
      { orderable: false, targets: 6 }
    ],
    language: {
      search: '',
      searchPlaceholder: 'Search clients...',
      lengthMenu: 'Show _MENU_ clients',
      info: 'Showing _START_ to _END_ of _TOTAL_ clients',
      emptyTable: 'No clients registered yet.'
    },
    initComplete: function () {
      const wrapper = $('#clientsTable_wrapper');
      wrapper.find('.dataTables_filter input').addClass('form-control form-control-sm');
      wrapper.find('.dataTables_length select').addClass('form-select form-select-sm');
    }
  });
}

function escapeHtml(text) {
  const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
  return text ? text.replace(/[&<>"']/g, m => map[m]) : '';
}

function escapeAttr(text) {
  return text ? text.replace(/['\\]/g, '\\$&') : '';
}

function formatDate(dateStr) {
  if (!dateStr) return '—';
  try {
    const d = new Date(dateStr);
    return d.toLocaleDateString() + ' ' + d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  } catch {
    return dateStr;
  }
}

async function openDeleteConfirm(clientId, clientName) {
  const result = await Swal.fire({
    icon: 'warning',
    title: 'Delete client?',
    html: `This will delete <strong>${escapeHtml(clientName)}</strong> and remove all face recognition data from the database and filesystem.<br><br>This action cannot be undone.`,
    showCancelButton: true,
    confirmButtonText: '<i class="fa-solid fa-trash-can me-1"></i>Delete',
    cancelButtonText: '<i class="fa-solid fa-xmark me-1"></i>Cancel',
    confirmButtonColor: '#dc2626',
    cancelButtonColor: '#6b7280',
    reverseButtons: true,
    focusCancel: true
  });

  if (result.isConfirmed) {
    await confirmDelete(clientId);
  }
}

async function confirmDelete(clientId) {
  if (!clientId) return;

  try {
    Swal.fire({
      title: 'Deleting client...',
      html: 'Please wait while we remove the client data.',
      allowOutsideClick: false,
      allowEscapeKey: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    const resp = await fetch('../backend/clients_delete.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ client_id: clientId })
    });

    const json = await resp.json();

    if (json.status === 'ok') {
      await showSuccess('Client deleted successfully (DB, filesystem, and Python cache updated)');
      await loadClients();
    } else {
      await showError(`Deletion failed: ${json.message || 'Unknown error'}`);
    }
  } catch (e) {
    console.error('Delete error:', e);
    await showError('Delete error: ' + e.message);
  }
}

function showError(msg) {
  setInlineAlert('msgError', msg);
  hideInlineAlert('msgSuccess');
  return Swal.fire({
    icon: 'error',
    title: 'Something went wrong',
    text: msg,
    confirmButtonColor: '#dc2626'
  });
}

function showSuccess(msg) {
  setInlineAlert('msgSuccess', msg);
  hideInlineAlert('msgError');
  return Swal.fire({
    icon: 'success',
    title: 'Success',
    text: msg,
    confirmButtonColor: '#0f62fe'
  });
}

function setInlineAlert(id, msg) {
  const element = document.getElementById(id);
  const message = element.querySelector('span');
  message.textContent = msg;
  element.style.display = 'flex';
}

function hideInlineAlert(id) {
  const element = document.getElementById(id);
  element.style.display = 'none';
}

function filterMobileClients() {
  const input = document.getElementById('mobileClientSearch');
  const list = document.getElementById('mobileClientsList');
  if (!input || !list) return;

  const query = input.value.trim().toLowerCase();
  const cards = list.querySelectorAll('.mobile-client-card');
  let visibleCount = 0;

  cards.forEach(card => {
    const text = card.getAttribute('data-search-text') || '';
    const show = !query || text.includes(query);
    card.style.display = show ? '' : 'none';
    if (show) visibleCount += 1;
  });

  const mobileEmptyMsg = document.getElementById('mobileEmptyMsg');
  if (mobileEmptyMsg) {
    mobileEmptyMsg.style.display = visibleCount === 0 ? 'block' : 'none';
    mobileEmptyMsg.textContent = query ? 'No clients match your search.' : 'No clients registered yet.';
  }
}

// Load on page ready
document.addEventListener('DOMContentLoaded', () => {
  loadClients();
});
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</body>
</html>
