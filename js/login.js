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

    // Simple validation
    if (!data.get('user_name') || !data.get('password')) {
      showError('Please enter username and password');
      submit.disabled = false;
      return;
    }

    fetch('../backend/loginData.php', {
      method: 'POST',
      body: data,
      credentials: 'same-origin'
    })
      .then(r => r.json())
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
            text: 'Redirecting to queue page...',
            timer: 1500,
            showConfirmButton: false
          }).then(() => {
            window.location.href = '?page=queueSelect';
          });
        } else {
          showError(json && json.error ? json.error : 'Login failed');
        }
      })
      .catch(() => {
        showError('Server error');
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
