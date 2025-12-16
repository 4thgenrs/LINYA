document.addEventListener('DOMContentLoaded', function() {
    const editButtons = document.querySelectorAll('.editBtn');
    const descriptionInput = document.getElementById('service_description');
    const officeSelect = document.getElementById('office_id');
    const updateIdInput = document.getElementById('update_id');
    const submitBtn = document.getElementById('submitBtn');

    editButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const row = btn.closest('tr');
            const serviceId = row.getAttribute('data-id');
            const serviceDesc = row.getAttribute('data-description');
            const officeId = row.getAttribute('data-office');

            // Fill form fields
            descriptionInput.value = serviceDesc;
            officeSelect.value = officeId;
            updateIdInput.value = serviceId;

            // Change button text to Update
            submitBtn.value = "Update Service";
        });
    });
});
