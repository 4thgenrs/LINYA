(function(){
  const frame = document.getElementById('moduleFrame');
  const buttons = Array.from(document.querySelectorAll('.adm-btn'));
  const logout = document.getElementById('adminLogout');

  const mapping = {
    landing: '../includes/landing.php',
    accounts: '../includes/accounts.php',
    adminClients: '../includes/adminClients.php',
    assignStaff: '../includes/assignStaff.php',
    finishedQueue: '../includes/finishedQueue.php',
    office: '../includes/office.php',
    services: '../includes/services.php',
    queueDisplay: '../includes/queueDisplay.php',
    officeQueue: '../includes/officeQueue.php'
  };

  buttons.forEach(b => b.addEventListener('click', () => {
    buttons.forEach(x => x.classList.remove('active'));
    b.classList.add('active');
    const m = b.dataset.module;
    frame.src = mapping[m] || mapping.landing;
  }));

  if (logout) {
    logout.addEventListener('click', async () => {
      if (!confirm('Logout admin?')) return;
      try {
        const r = await fetch('../backend/logout.php', { method: 'POST', credentials: 'same-origin' });
        const j = await r.json();
        if (j && j.success) window.location.href = '../includes/login.php';
        else alert('Logout failed');
      } catch (e) { alert('Logout error'); }
    });
  }
})();