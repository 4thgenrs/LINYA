document.addEventListener('DOMContentLoaded', function() {
    const editButtons = document.querySelectorAll('.editBtn');
    const usernameInput = document.getElementById('account_username');
    const passwordInput = document.getElementById('account_password');
    const nameInput = document.getElementById('account_name');
    const roleSelect = document.getElementById('role_id');
    const officeSelect = document.getElementById('office_id');
    const updateIdInput = document.getElementById('user_id');
    const submitBtn = document.getElementById('submitBtn');

    editButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const row = btn.closest('tr');

            // Get data from row attributes
            const userId = row.getAttribute('data-id');
            const username = row.getAttribute('data-username');
            const password = row.getAttribute('data-password');
            const name = row.getAttribute('data-name');
            const roleId = row.getAttribute('data-role');
            const officeId = row.getAttribute('data-office');

            // Fill form fieldsaada
            //asadada
            usernameInput.value = username;
            passwordInput.value = password;
            nameInput.value = name;
            roleSelect.value = roleId;
            officeSelect.value = officeId;
            updateIdInput.value = userId;

            // Change button text to Update


            submitBtn.value = "Update Account";
        });
    });
});
