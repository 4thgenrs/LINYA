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
  <style>
    * { box-sizing: border-box; }
    body { margin: 0; font-family: Inter, Segoe UI, Roboto, Arial, sans-serif; background: #f6f8fb; color: #111; }
    .container { padding: 20px; }
    h1 { color: #0f62fe; margin: 0 0 20px 0; font-size: 28px; }
    .table-container { background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(15,23,42,0.08); }
    table { width: 100%; border-collapse: collapse; }
    th { background: #f3f4f6; padding: 12px; text-align: left; font-weight: 700; color: #6b7280; border-bottom: 1px solid #e5e7eb; }
    td { padding: 12px; border-bottom: 1px solid #e5e7eb; }
    tr:hover { background: #fafbfc; }
    .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; }
    .badge-info { background: #dbeafe; color: #1e40af; }
    .btn-delete { background: #dc2626; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 600; transition: all 0.2s; }
    .btn-delete:hover { background: #b91c1c; }
    .btn-delete:disabled { background: #9ca3af; cursor: not-allowed; }
    .loading { text-align: center; padding: 40px; color: #6b7280; }
    .error { background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 4px; margin-bottom: 20px; }
    .success { background: #dcfce7; color: #166534; padding: 12px; border-radius: 4px; margin-bottom: 20px; }
    .empty { text-align: center; padding: 40px; color: #9ca3af; }
    .modal { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; }
    .modal.show { display: flex; }
    .modal-content { background: white; padding: 24px; border-radius: 8px; max-width: 400px; box-shadow: 0 20px 25px rgba(0,0,0,0.15); }
    .modal-title { font-size: 18px; font-weight: 700; margin: 0 0 12px 0; }
    .modal-message { color: #6b7280; margin: 0 0 20px 0; }
    .modal-actions { display: flex; gap: 10px; justify-content: flex-end; }
    .btn { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; transition: all 0.2s; }
    .btn-primary { background: #0f62fe; color: white; }
    .btn-primary:hover { background: #0353e9; }
    .btn-secondary { background: #e5e7eb; color: #111; }
    .btn-secondary:hover { background: #d1d5db; }
    .stats { display: flex; gap: 20px; margin-bottom: 20px; }
    .stat-card { background: #fff; padding: 16px; border-radius: 8px; border-left: 4px solid #0f62fe; }
    .stat-label { color: #6b7280; font-size: 12px; font-weight: 600; }
    .stat-value { font-size: 24px; font-weight: 700; color: #0f62fe; }
  </style>
</head>
<body>

<div class="container">
  <h1>👤 Face Recognition Clients</h1>

  <div class="stats">
    <div class="stat-card">
      <div class="stat-label">Total Clients</div>
      <div class="stat-value" id="statTotal">—</div>
    </div>
  </div>

  <div id="msgSuccess" class="success" style="display: none;"></div>
  <div id="msgError" class="error" style="display: none;"></div>

  <div class="table-container">
    <div id="loadingMsg" class="loading">Loading clients...</div>
    <div id="emptyMsg" class="empty" style="display: none;">No clients registered yet.</div>
    <table id="clientsTable" style="display: none;">
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

<!-- Confirmation Modal -->
<div id="confirmModal" class="modal">
  <div class="modal-content">
    <h2 class="modal-title">Delete Client?</h2>
    <p class="modal-message">
      This will delete <strong id="modalClientName"></strong> and remove all face recognition data from the database and filesystem.
      <br><br>
      This action cannot be undone.
    </p>
    <div class="modal-actions">
      <button class="btn btn-secondary" onclick="closeModal()">Cancel</button>
      <button class="btn btn-primary" id="confirmDeleteBtn" onclick="confirmDelete()">Delete</button>
    </div>
  </div>
</div>

<script>
let clients = [];
let pendingDeleteId = null;

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
  const statTotal = document.getElementById('statTotal');

  loadingMsg.style.display = 'none';

  if (clients.length === 0) {
    table.style.display = 'none';
    emptyMsg.style.display = 'block';
    statTotal.textContent = '0';
    return;
  }

  table.style.display = 'table';
  emptyMsg.style.display = 'none';
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
        <button class="btn-delete" onclick="openDeleteConfirm(${client.client_id}, '${escapeAttr(client.name)}')">
          🗑️ Delete
        </button>
      </td>
    </tr>
  `).join('');
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

function openDeleteConfirm(clientId, clientName) {
  pendingDeleteId = clientId;
  document.getElementById('modalClientName').textContent = clientName;
  document.getElementById('confirmModal').classList.add('show');
}

function closeModal() {
  document.getElementById('confirmModal').classList.remove('show');
  pendingDeleteId = null;
}

async function confirmDelete() {
  if (!pendingDeleteId) return;

  const btn = document.getElementById('confirmDeleteBtn');
  btn.disabled = true;
  btn.textContent = 'Deleting...';

  try {
    const resp = await fetch('../backend/clients_delete.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ client_id: pendingDeleteId })
    });

    const json = await resp.json();

    if (json.status === 'ok') {
      showSuccess(`Client deleted successfully (DB, filesystem, and Python cache updated)`);
      closeModal();
      await loadClients();
    } else {
      showError(`Deletion failed: ${json.message || 'Unknown error'}`);
    }
  } catch (e) {
    console.error('Delete error:', e);
    showError('Delete error: ' + e.message);
  } finally {
    btn.disabled = false;
    btn.textContent = '🗑️ Delete';
  }
}

function showError(msg) {
  const el = document.getElementById('msgError');
  el.textContent = msg;
  el.style.display = 'block';
  document.getElementById('msgSuccess').style.display = 'none';
  setTimeout(() => { el.style.display = 'none'; }, 5000);
}

function showSuccess(msg) {
  const el = document.getElementById('msgSuccess');
  el.textContent = msg;
  el.style.display = 'block';
  document.getElementById('msgError').style.display = 'none';
  setTimeout(() => { el.style.display = 'none'; }, 4000);
}

// Load on page ready
document.addEventListener('DOMContentLoaded', loadClients);
</script>

</body>
</html>
