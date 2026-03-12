<?php
// Simple admin page to list clients and remove them.
include '../linya_db/config.php';
session_start();

// Very small auth guard: require admin session (optional)
// if (!isset($_SESSION['user']) || $_SESSION['user']['user_type'] != 1) { echo 'Access denied'; exit; }

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Clients Admin</title>
  <link rel="stylesheet" href="../css/dashboard.css">
  <style>
    table { border-collapse: collapse; width: 100%; }
    th, td { padding: 8px; border: 1px solid #ddd; }
    th { background: #f7f7f7; }
    .btn { padding:6px 10px; border-radius:4px; cursor:pointer; }
    .btn.danger { background:#d9534f; color:#fff; border:none; }
  </style>
</head>
<body>
  <main style="padding:18px;">
    <h1>Registered Clients</h1>
    <div id="msg" style="margin-bottom:12px;color:#333;"></div>
    <table id="clientsTable">
      <thead>
        <tr><th>ID</th><th>Name</th><th>Phone</th><th>Email</th><th>Student #</th><th>Course</th><th>Created</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php
        $q = $conn->query('SELECT client_id,name,phone,email,student_number,course,created_at,safe_name FROM clients ORDER BY created_at DESC');
        if ($q) {
            while ($r = $q->fetch_assoc()) {
                echo '<tr data-id="'.intval($r['client_id']).'">';
                echo '<td>'.intval($r['client_id']).'</td>';
                echo '<td>'.htmlspecialchars($r['name']).' <small style="color:#666">('.htmlspecialchars($r['safe_name']).')</small></td>';
                echo '<td>'.htmlspecialchars($r['phone']).'</td>';
                echo '<td>'.htmlspecialchars($r['email']).'</td>';
                echo '<td>'.htmlspecialchars($r['student_number']).'</td>';
                echo '<td>'.htmlspecialchars($r['course']).'</td>';
                echo '<td>'.htmlspecialchars($r['created_at']).'</td>';
                echo '<td><button class="btn danger" data-id="'.intval($r['client_id']).'">Delete</button></td>';
                echo '</tr>';
            }
        }
        ?>
      </tbody>
    </table>
  </main>

  <script>
    document.addEventListener('click', function(e){
      const btn = e.target.closest('button[data-id]');
      if (!btn) return;
      const id = btn.getAttribute('data-id');
      if (!confirm('Delete client ID '+id+'? This cannot be undone.')) return;
      fetch('../backend/clients_delete.php', { method: 'POST', headers: { 'Content-Type':'application/json' }, body: JSON.stringify({ client_id: parseInt(id,10) }) })
        .then(r => r.json())
        .then(j => {
          const msg = document.getElementById('msg');
          if (j && j.status === 'ok') {
            msg.style.color = 'green'; msg.textContent = 'Deleted client '+id;
            const row = document.querySelector('tr[data-id="'+id+'"]'); if (row) row.remove();
          } else {
            msg.style.color = 'red'; msg.textContent = 'Error: '+(j && j.message? j.message : 'unknown');
          }
        })
        .catch(err => { document.getElementById('msg').style.color='red'; document.getElementById('msg').textContent = 'Network error'; });
    });
  </script>
</body>
</html>
