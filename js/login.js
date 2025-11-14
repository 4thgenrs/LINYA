document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('loginForm');
  const err = document.getElementById('error');
  const submit = document.getElementById('submitBtn');

  if (!form) return;

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

    // Simple absolute path to the backend endpoint. Keep this minimal.
    fetch('/LINYA/backend/loginData.php', {
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
      .then(json => {
        submit.disabled = false;

        if (json && json.success) {
          // Close Bootstrap modal (if open)
          const modalEl = document.getElementById('loginModal');
          const modal = bootstrap.Modal.getInstance(modalEl);
          if (modal) modal.hide();

          // SweetAlert2 success message
          Swal.fire({
            icon: 'success',
            title: 'Login Successful!',
            text: 'Redirecting to dashboard...',
            timer: 1500,
            showConfirmButton: false
          }).then(() => {
            // Redirect to the admin dashboard root
            window.location.href = '/LINYA/dashboard/';
          });
        } else {
          showError(json && json.error ? json.error : 'Login failed');
        }
      })
      .catch((err) => {
        // Show backend error message to help debugging (local dev only)
        showError(err && err.message ? err.message : 'Server error');
        submit.disabled = false;
      });
  });

  function showError(message) {
    // If SweetAlert2 is available, use it
    if (typeof Swal !== 'undefined') {
      Swal.fire({
        icon: 'error',
        title: 'Login Failed',
        text: message
      });
    } else {
      // fallback for plain error text in modal
      err.textContent = message;
      err.style.display = 'block';
    }
  }
});
