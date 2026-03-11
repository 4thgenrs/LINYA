document.addEventListener("DOMContentLoaded", function () {
    const editButtons = document.querySelectorAll(".editBtn");
    const officeIdField = document.getElementById("office_id");
    const officeCodeField = document.getElementById("office_code");
    const officeDescField = document.getElementById("office_description");
    const submitBtn = document.getElementById("submitBtn");
    const officeForm = document.getElementById("officeForm");
    const formActionField = document.getElementById('form_action');
    const cancelBtn = document.getElementById('cancelBtn');
    const modalElement = document.getElementById('officeModal');
    const modalTitle = document.getElementById('officeModalTitle');
    const openAddOfficeBtn = document.getElementById('openAddOfficeBtn');
    const modalInstance = modalElement && window.bootstrap ? bootstrap.Modal.getOrCreateInstance(modalElement) : null;

    function setSubmitLabel(text) {
        submitBtn.textContent = text;
    }

    function resetOfficeForm() {
        officeForm.reset();
        officeIdField.value = "";
        formActionField.value = '';
        setSubmitLabel('Add Office');
        if (modalTitle) modalTitle.textContent = 'Add Office';
    }

    // Handle Edit Button
    editButtons.forEach(btn => {
        btn.addEventListener("click", function () {
            const row = this.closest("[data-id]");
            const id = row.getAttribute("data-id");
            const code = row.getAttribute("data-code");
            const description = row.getAttribute("data-description");

            officeIdField.value = id;
            officeCodeField.value = code;
            officeDescField.value = description;

            // Switch to update mode
            formActionField.value = 'update';
            setSubmitLabel('Update Office');
            if (modalTitle) modalTitle.textContent = 'Edit Office';

            if (modalInstance) {
                modalInstance.show();
            }
        });
    });

    officeForm.addEventListener("reset", resetOfficeForm);

    if (cancelBtn) {
        cancelBtn.addEventListener('click', resetOfficeForm);
    }

    if (openAddOfficeBtn) {
        openAddOfficeBtn.addEventListener('click', resetOfficeForm);
    }

    if (modalElement) {
        modalElement.addEventListener('hidden.bs.modal', resetOfficeForm);
    }
});


// Initialize DataTable
$(document).ready(function() {
    $('#officeTable').DataTable({
        responsive: true,
        ordering: true,
        pageLength: 10,
        order: [[0, 'asc']], // Sort by # column
        columnDefs: [
            { orderable: false, targets: -1 } // Disable sorting on Action column
        ],
        language: {
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_ to _END_ of _TOTAL_ entries',
            search: "Search:"
        }
    });

    $('.dataTables_filter input').attr('placeholder', 'Search offices...');
});

// Handle form submission
document.getElementById('officeForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formAction = document.getElementById('form_action').value;
    const form = this;
    const officeCode = document.getElementById('office_code').value.trim();
    const officeDescription = document.getElementById('office_description').value.trim();
    
    // Validation
    if (!officeCode || !officeDescription) {
        Swal.fire({
            title: 'Validation Error!',
            text: 'Please fill in all required fields.',
            icon: 'warning',
            confirmButtonColor: '#ffc107'
        });
        return;
    }
    
    const actionText = formAction === 'update' ? 'update' : 'add';
    const actionTitle = formAction === 'update' ? 'Update Office?' : 'Add Office?';
    
    // Confirmation dialog
    Swal.fire({
        title: actionTitle,
        html: `Do you want to ${actionText} office <strong>${officeDescription}</strong>?`,
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
                title: formAction === 'update' ? 'Updating...' : 'Adding...',
                text: 'Please wait',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Add the appropriate action input
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            
            if (formAction === 'update') {
                actionInput.name = 'update_office';
            } else {
                actionInput.name = 'add_office';
            }
            
            actionInput.value = '1';
            form.appendChild(actionInput);
            
            // Submit the form
            form.submit();
        }
    });
});

// TOGGLE (ENABLE/DISABLE) BUTTON JS WITH SWEETALERT
document.querySelectorAll('.toggleBtn').forEach(button => {
    button.addEventListener('click', function() {
        const officeId = this.getAttribute('data-office-id');
        const officeName = this.getAttribute('data-office-name');
        const currentStatus = this.getAttribute('data-current-status');
        const isEnabled = currentStatus === '1';
        const action = isEnabled ? 'disable' : 'enable';

        Swal.fire({
            title: 'Are you sure?',
            text: `Do you want to ${action} "${officeName}"?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: isEnabled ? '#6c757d' : '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: `Yes, ${action} it!`,
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading state
                Swal.fire({
                    title: 'Processing...',
                    text: 'Please wait',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Create a form and submit it
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '../backend/officeData.php';
                
                const officeIdInput = document.createElement('input');
                officeIdInput.type = 'hidden';
                officeIdInput.name = 'office_id';
                officeIdInput.value = officeId;
                
                const toggleInput = document.createElement('input');
                toggleInput.type = 'hidden';
                toggleInput.name = 'toggle_office';
                toggleInput.value = '1';
                
                form.appendChild(officeIdInput);
                form.appendChild(toggleInput);
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
});

// DELETE BUTTON JS WITH SWEETALERT
document.querySelectorAll('.deleteBtn').forEach(button => {
    button.addEventListener('click', function() {
        const officeId = this.getAttribute('data-office-id');
        const officeName = this.getAttribute('data-office-name');

        Swal.fire({
            title: 'Are you sure?',
            text: `Do you want to delete "${officeName}"? This action cannot be undone!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading state
                Swal.fire({
                    title: 'Deleting...',
                    text: 'Please wait',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Create a form and submit it
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '../backend/officeData.php';
                
                const officeIdInput = document.createElement('input');
                officeIdInput.type = 'hidden';
                officeIdInput.name = 'office_id';
                officeIdInput.value = officeId;
                
                const deleteInput = document.createElement('input');
                deleteInput.type = 'hidden';
                deleteInput.name = 'delete_office';
                deleteInput.value = '1';
                
                form.appendChild(officeIdInput);
                form.appendChild(deleteInput);
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
});

// Show success message if redirected with success parameter
const urlParams = new URLSearchParams(window.location.search);

if (urlParams.get('success') === 'deleted') {
    Swal.fire({
        title: 'Deleted!',
        text: 'Office has been deleted successfully.',
        icon: 'success',
        confirmButtonColor: '#28a745'
    });
}

if (urlParams.get('success') === 'added') {
    Swal.fire({
        title: 'Success!',
        text: 'Office has been added successfully.',
        icon: 'success',
        confirmButtonColor: '#28a745'
    });
}

if (urlParams.get('success') === 'updated') {
    Swal.fire({
        title: 'Updated!',
        text: 'Office has been updated successfully.',
        icon: 'success',
        confirmButtonColor: '#28a745'
    });
}

if (urlParams.get('success') === 'toggled') {
    Swal.fire({
        title: 'Success!',
        text: 'Office status has been updated successfully.',
        icon: 'success',
        confirmButtonColor: '#28a745'
    });
}

if (urlParams.get('error')) {
    let errorMsg = 'An error occurred.';
    
    switch (urlParams.get('error')) {
        case 'duplicate_office':
            errorMsg = 'An office with that code or description already exists.';
            break;
        case 'active_queues':
            errorMsg = 'Cannot delete office: there are active queues for this office.';
            break;
    }
    
    Swal.fire({
        title: 'Error!',
        text: errorMsg,
        icon: 'error',
        confirmButtonColor: '#d33'
    });
}