document.addEventListener('DOMContentLoaded', function() {
    const editButtons = document.querySelectorAll('.editBtn');
    const descriptionInput = document.getElementById('service_description');
    const officeSelect = document.getElementById('office_id');
    const updateIdInput = document.getElementById('update_id');
    const submitBtn = document.getElementById('submitBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const serviceForm = document.getElementById('serviceForm');
    const modalElement = document.getElementById('servicesModal');
    const modalTitle = document.getElementById('servicesModalTitle');
    const openAddServiceBtn = document.getElementById('openAddServiceBtn');
    const modalInstance = modalElement && window.bootstrap ? bootstrap.Modal.getOrCreateInstance(modalElement) : null;

    function setSubmitLabel(text) {
        submitBtn.textContent = text;
    }

    function resetFormForAdd() {
        serviceForm.reset();
        updateIdInput.value = '';
        setSubmitLabel('Add Service');
        if (modalTitle) modalTitle.textContent = 'Add Service';
    }

    editButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const row = btn.closest('[data-id]');
            const serviceId = row.getAttribute('data-id');
            const serviceDesc = row.getAttribute('data-description');
            const officeId = row.getAttribute('data-office');

            // Fill form fields
            descriptionInput.value = serviceDesc;
            officeSelect.value = officeId;
            updateIdInput.value = serviceId;

            // Change button text to Update
            setSubmitLabel('Update Service');
            if (modalTitle) modalTitle.textContent = 'Edit Service';

            if (modalInstance) {
                modalInstance.show();
            }
        });
    });

    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            resetFormForAdd();
        });
    }

    if (openAddServiceBtn) {
        openAddServiceBtn.addEventListener('click', function() {
            resetFormForAdd();
        });
    }

    if (modalElement) {
        modalElement.addEventListener('hidden.bs.modal', function() {
            resetFormForAdd();
        });
    }

    // Handle form submission with SweetAlert
    serviceForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const serviceDesc = descriptionInput.value.trim();
        const officeId = officeSelect.value;
        const isUpdate = updateIdInput.value !== '';
        
        // Validation
        if (!serviceDesc || !officeId) {
            Swal.fire({
                title: 'Validation Error!',
                text: 'Please fill in all required fields.',
                icon: 'warning',
                confirmButtonColor: '#ffc107'
            });
            return;
        }
        
        const actionText = isUpdate ? 'update' : 'add';
        const actionTitle = isUpdate ? 'Update Service?' : 'Add Service?';
        
        // Confirmation dialog
        Swal.fire({
            title: actionTitle,
            html: `Do you want to ${actionText} service <strong>${serviceDesc}</strong>?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: `Yes, ${actionText} it!`,
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading state
                Swal.fire({
                    title: isUpdate ? 'Updating...' : 'Adding...',
                    text: 'Please wait',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Submit the form
                this.submit();
            }
        });
    });
});

// Initialize DataTable
$(document).ready(function() {
    $('#servicesTable').DataTable({
        responsive: true,
        ordering: true,
        pageLength: 10,
        order: [[0, 'asc']], // Sort by # column
        columnDefs: [
            { orderable: false, targets: -1 } // Disable sorting on Actions column
        ],
        language: {
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_ to _END_ of _TOTAL_ entries',
            search: "Search:"
        }
    });

    $('.dataTables_filter input').attr('placeholder', 'Search services...');
});