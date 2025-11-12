document.addEventListener('DOMContentLoaded', () => {
  const button = document.getElementById('logoutBtn');

  if (!button) return;

  button.addEventListener('click', (e) => {
    e.preventDefault();

    // Ask for confirmation
    if (typeof Swal !== 'undefined') {
      Swal.fire({
        title: 'Are you sure you want to logout?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, logout',
        cancelButtonText: 'Cancel'
      }).then((result) => {
        if (result.isConfirmed) {
          performLogout();
        }
      });
    } else {
      if (confirm('Are you sure you want to logout?')) {
        performLogout();
      }
    }
  });

  function performLogout() {
    fetch('/LINYA/backend/logout.php', {
      method: 'POST',
      credentials: 'same-origin'
    })
      .then(response => response.json())
      .then(json => {
        if (json.success) {
          if (typeof Swal !== 'undefined') {
            Swal.fire({
              icon: 'success',
              title: 'Logged out successfully!',
              timer: 1500,
              showConfirmButton: false
            }).then(() => {
              window.location.href = '/LINYA/index.php';
            });
          } else {
            window.location.href = '/LINYA/index.php';
          }
        } else {
          showError('Logout failed. Please try again.');
        }
      })
      .catch(() => {
        showError('Server error. Please try again.');
      });
  }

  function showError(message) {
    if (typeof Swal !== 'undefined') {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: message
      });
    } else {
      alert(message);
    }
  }
});
