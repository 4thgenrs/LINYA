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
    const m = b.dataset.module;
    if (!m) return;

    const url = new URL(window.location.href);
    url.searchParams.set('module', m);
    window.history.replaceState({}, '', url.toString());

    buttons.forEach(x => x.classList.remove('active'));
    b.classList.add('active');
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
  const mobileToggle = document.getElementById('mobileToggle');
    const sidebar = document.getElementById('sidebar');
    const body = document.body;

    if (mobileToggle) {
      mobileToggle.addEventListener('click', () => {
        sidebar.classList.toggle('show');
        body.classList.toggle('sidebar-open');
      });

      // Close sidebar when clicking overlay
      body.addEventListener('click', (e) => {
        if (body.classList.contains('sidebar-open') && 
            !sidebar.contains(e.target) && 
            !mobileToggle.contains(e.target)) {
          sidebar.classList.remove('show');
          body.classList.remove('sidebar-open');
        }
      });

      // Close sidebar when navigation item is clicked on mobile
      const navButtons = sidebar.querySelectorAll('.adm-btn');
      navButtons.forEach(btn => {
        btn.addEventListener('click', () => {
          if (window.innerWidth <= 768) {
            sidebar.classList.remove('show');
            body.classList.remove('sidebar-open');
          }
        });
      });
    }
})();