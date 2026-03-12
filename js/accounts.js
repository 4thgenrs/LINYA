document.addEventListener('DOMContentLoaded', function() {
    const editButtons = document.querySelectorAll('.editBtn');
    const usernameInput = document.getElementById('account_username');
    const passwordInput = document.getElementById('account_password');
    const nameInput = document.getElementById('account_name');
    const roleSelect = document.getElementById('role_id');
    const officeSelect = document.getElementById('office_id');
    const updateIdInput = document.getElementById('user_id');
    const submitBtn = document.getElementById('submitBtn');
    const usernameFeedback = document.getElementById('usernameFeedback');
    const nameFeedback = document.getElementById('nameFeedback');
    const accountsForm = document.getElementById('accountsForm');
    const modalElement = document.getElementById('accountsModal');
    const modalTitle = document.getElementById('accountsModalTitle');
    const openAddAccountBtn = document.getElementById('openAddAccountBtn');
    const modalInstance = modalElement && window.bootstrap ? bootstrap.Modal.getOrCreateInstance(modalElement) : null;

    function setSubmitLabel(text) {
        if (!submitBtn) return;
        if ('value' in submitBtn) {
            submitBtn.value = text;
        } else {
            submitBtn.textContent = text;
        }
    }

    function resetFormForAdd() {
        if (!accountsForm) return;
        accountsForm.reset();
        updateIdInput.value = '';
        setSubmitLabel('Add User');
        if (modalTitle) modalTitle.textContent = 'Add User';
        usernameFeedback.style.display = 'none';
        nameFeedback.style.display = 'none';
        setSubmitState(true);
    }

    editButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const row = btn.closest('[data-id]');

            // Get data from row attributes
            const userId = row.getAttribute('data-id');
            const username = row.getAttribute('data-username');
            const password = row.getAttribute('data-password');
            const name = row.getAttribute('data-name');
            const roleId = row.getAttribute('data-role');
            const officeId = row.getAttribute('data-office');

            // Fill form fields
            usernameInput.value = username;
            passwordInput.value = password;
            nameInput.value = name;
            roleSelect.value = roleId;
            officeSelect.value = officeId;
            updateIdInput.value = userId;

            // Change button text to Update
            setSubmitLabel('Update User');
            if (modalTitle) modalTitle.textContent = 'Edit User';

            // clear feedback
            usernameFeedback.style.display = 'none';
            nameFeedback.style.display = 'none';

            if (modalInstance) {
                modalInstance.show();
            }
        });
    });

    if (openAddAccountBtn) {
        openAddAccountBtn.addEventListener('click', function() {
            resetFormForAdd();
        });
    }

    if (modalElement) {
        modalElement.addEventListener('hidden.bs.modal', function() {
            resetFormForAdd();
        });
    }

    // helper: debounce
    function debounce(fn, wait) {
        let t;
        return function(...args) {
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, args), wait);
        };
    }

    // server endpoint for uniqueness check
    const checkUrl = '../backend/accountsData.php?action=check';

    function setSubmitState(state) {
        submitBtn.disabled = !state;
    }

    // check uniqueness (username & name) using backend endpoint
    const doCheck = debounce(function() {
        const username = (usernameInput.value || '').trim();
        const name = (nameInput.value || '').trim();
        const exclude = parseInt(updateIdInput.value || 0, 10) || 0;

        // if both empty, nothing to check
        if (!username && !name) {
            usernameFeedback.style.display = 'none';
            nameFeedback.style.display = 'none';
            setSubmitState(true);
            return;
        }

        const params = new URLSearchParams();
        if (username) params.append('username', username);
        if (name) params.append('name', name);
        if (exclude) params.append('exclude', String(exclude));

        fetch(checkUrl + '&' + params.toString(), { credentials: 'same-origin' })
            .then(r => r.json())
            .then(json => {
                let ok = true;
                if (json.exists_username) {
                    usernameFeedback.textContent = 'This username is already in use.';
                    usernameFeedback.style.display = 'block';
                    ok = false;
                } else {
                    usernameFeedback.style.display = 'none';
                }
                if (json.exists_name) {
                    nameFeedback.textContent = 'This name is already in use.';
                    nameFeedback.style.display = 'block';
                    ok = false;
                } else {
                    nameFeedback.style.display = 'none';
                }
                setSubmitState(ok);
            })
            .catch(err => {
                // On error, don't block submission but log
                console.error('Validation check failed', err);
                usernameFeedback.style.display = 'none';
                nameFeedback.style.display = 'none';
                setSubmitState(true);
            });
    }, 350);

    if (usernameInput) {
        usernameInput.addEventListener('input', function(){ usernameFeedback.style.display = 'none'; doCheck(); });
        usernameInput.addEventListener('blur', doCheck);
    }
    if (nameInput) {
        nameInput.addEventListener('input', function(){ nameFeedback.style.display = 'none'; doCheck(); });
        nameInput.addEventListener('blur', doCheck);
    }

    // initial enable
    setSubmitState(true);

    // SweetAlert confirmation for add/update form submission
    if (accountsForm) {
        accountsForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const isUpdate = updateIdInput.value ? true : false;
            const action = isUpdate ? 'Update' : 'Add';
            const accountName = nameInput.value.trim();
            const accountUsername = usernameInput.value.trim();
            
            Swal.fire({
                title: `${action} Account?`,
                html: `Are you sure you want to ${action.toLowerCase()} this account?<br><strong>${accountName}</strong> (${accountUsername})`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: isUpdate ? '#ffc107' : '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: `Yes, ${action.toLowerCase()} it!`,
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    accountsForm.submit();
                }
            });
        });
    }

    function applyMobileFilters() {
        const searchInput = document.getElementById('accountsMobileSearch');
        const roleFilter = document.getElementById('accountsMobileRoleFilter');
        const officeFilter = document.getElementById('accountsMobileOfficeFilter');
        const cards = document.querySelectorAll('.mobile-account-card');
        const emptyState = document.getElementById('accountsMobileEmpty');

        if (!cards.length || !emptyState) return;

        const query = (searchInput?.value || '').trim().toLowerCase();
        const roleValue = roleFilter?.value || '';
        const officeValue = officeFilter?.value || '';
        let visibleCount = 0;

        cards.forEach(card => {
            const searchText = (card.getAttribute('data-search-text') || '').toLowerCase();
            const matchesSearch = !query || searchText.includes(query);
            const matchesRole = !roleValue || card.getAttribute('data-role') === roleValue;
            const matchesOffice = !officeValue || card.getAttribute('data-office') === officeValue;
            const shouldShow = matchesSearch && matchesRole && matchesOffice;
            card.style.display = shouldShow ? '' : 'none';
            if (shouldShow) visibleCount += 1;
        });

        emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
        emptyState.textContent = query || roleValue || officeValue
            ? 'No accounts match the selected filters.'
            : 'No accounts found.';
    }

    ['accountsMobileSearch', 'accountsMobileRoleFilter', 'accountsMobileOfficeFilter'].forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener(id === 'accountsMobileSearch' ? 'input' : 'change', applyMobileFilters);
        }
    });

    applyMobileFilters();

    // SweetAlert confirmation for delete action
    $(document).on('click', 'button[name="delete_account"]', function(e) {
        e.preventDefault();
        
        const deleteBtn = $(this);
        const form = deleteBtn.closest('form');
        const row = deleteBtn.closest('[data-id]');
        const accountName = row.data('name') || '';
        const accountUsername = row.data('username') || '';
        
        Swal.fire({
            title: 'Delete Account?',
            html: `Are you sure you want to delete the account:<br><strong>${accountName}</strong> (${accountUsername})?<br><br>This action cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                form[0].submit();
            }
        });
    });
});
