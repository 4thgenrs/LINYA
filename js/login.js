document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('loginForm');
  const err = document.getElementById('error');
  const submit = document.getElementById('submitBtn');

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    err.style.display = 'none';
    submit.disabled = true;

    const data = new FormData(form);
    if (!data.get('user_name') || !data.get('password')) {
      showError('Please enter username and password');
      submit.disabled = false;
      return;
    }

    fetch('../backend/loginData.php', {
      method: 'POST',
      body: data,
      credentials: 'same-origin'
    }).then(r => r.json())
      .then(json => {
        if (json && json.success) {
          const type = (json.user && json.user.user_type) ? parseInt(json.user.user_type,10) : 0;
          if (type === 1) {
            // admin -> admin dashboard
            window.location.href = '/LINYA/includes/adminDashboard.php';
          } else if (type === 2) {
            // staff -> staff dashboard
            window.location.href = '/LINYA/includes/dashboard.php';
          } else {
            window.location.href = '/LINYA/';
          }
        } else {
          showError(json && json.error ? json.error : 'Login failed');
          submit.disabled = false;
        }
      })
      .catch(err => {
        showError('Server error');
        submit.disabled = false;
      });
  });

  function showError(m) {
    err.style.display = 'block';
    err.textContent = m;
  }
});
