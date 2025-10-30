// Robust kiosk menu client script with debug logs and external multi-select toggle

// global state
window.multiSelectMode = window.multiSelectMode || false;
window.selectedServicesSet = window.selectedServicesSet || new Set();
// new: soft copy request state (default false = hard copy)
window.requestSoftCopy = window.requestSoftCopy || false;

// helper: update UI elements that depend on multiSelectMode
function refreshUIForMode() {
    console.log('refreshUIForMode, multiSelectMode=', window.multiSelectMode);
    const serviceActions = document.getElementById('serviceActions');
    const toggleBtn = document.getElementById('multiSelectToggle');

    if (window.multiSelectMode) {
        document.body.classList.add('multi-select-mode');
        if (serviceActions) serviceActions.style.display = 'flex';
        if (toggleBtn) toggleBtn.textContent = 'Cancel multi-select';
    } else {
        document.body.classList.remove('multi-select-mode');
        // show actions only if there are selections (even in single-select mode)
        if (serviceActions) {
            serviceActions.style.display = (window.selectedServicesSet && window.selectedServicesSet.size > 0) ? 'flex' : 'none';
        }
        if (toggleBtn) toggleBtn.textContent = 'Select multiple';
    }

    // update confirm button enabled state
    const confirmBtn = document.getElementById('confirmBtn');
    if (confirmBtn) confirmBtn.disabled = !(window.selectedServicesSet && window.selectedServicesSet.size > 0);
}

// load services for an office
function loadServices(officeId, officeName) {
    console.log('loadServices', officeId, officeName);
    const officeSection = document.getElementById("officeSection");
    const serviceSection = document.getElementById("serviceSection");
    const serviceTitle = document.getElementById("serviceTitle");
    const serviceGrid = document.getElementById("serviceGrid");
    const selectedCountEl = document.getElementById("selectedCount");
    const confirmBtn = document.getElementById("confirmBtn");

    if (!serviceGrid) {
        console.error('serviceGrid element not found');
        return;
    }

    serviceTitle && (serviceTitle.textContent = "Services for " + officeName);

    // store current office for confirmSelection
    window.currentOfficeId = officeId;

    // reset selection for this load
    window.selectedServicesSet = new Set();

    refreshUIForMode(); // apply current mode

    fetch(`../backend/kioskMenuData.php?office_id=${encodeURIComponent(officeId)}`)
        .then(res => {
            console.log('fetch status', res.status, res.statusText);
            return res.text();
        })
        .then(data => {
            console.log('kioskMenuData response length', data.length);
            serviceGrid.innerHTML = data;

            const buttons = serviceGrid.querySelectorAll('.service-btn');
            if (!buttons.length) console.warn('No .service-btn found in response');

            buttons.forEach(btn => {
                const cb = btn.querySelector('.service-checkbox');
                const sid = btn.dataset.serviceId;
                if (!sid) {
                    console.warn('service missing data-service-id', btn);
                    return;
                }

                // ensure checkbox presence when needed (backend should include it)
                if (cb) {
                    // ensure checkbox visual state follows body.multi-select-mode
                    if (!window.multiSelectMode) cb.style.display = 'none';
                    else cb.style.display = '';

                    cb.addEventListener('change', () => {
                        if (cb.checked) {
                            btn.classList.add('selected');
                            window.selectedServicesSet.add(sid);
                        } else {
                            btn.classList.remove('selected');
                            window.selectedServicesSet.delete(sid);
                        }
                        refreshUIForMode();
                        updateSelectedCount();
                    });
                }

                // remove per-button click here - serviceGrid click will handle selection uniformly
            });

            // show services screen (transition)
            if (officeSection) officeSection.classList.remove("active");
            setTimeout(() => {
                if (officeSection) officeSection.style.display = "none";
                if (serviceSection) serviceSection.style.display = "block";
                setTimeout(() => serviceSection && serviceSection.classList.add("active"), 100);
            }, 400);

            updateSelectedCount();
            refreshUIForMode();
        })
        .catch(err => {
            console.error('fetch error:', err);
            serviceGrid.innerHTML = "<p>Error loading services</p>";
        });

    function updateSelectedCount() {
        const count = window.selectedServicesSet ? window.selectedServicesSet.size : 0;
        if (selectedCountEl) selectedCountEl.textContent = `${count} selected`;
        if (confirmBtn) confirmBtn.disabled = count === 0;
        const serviceActions = document.getElementById('serviceActions');
        if (serviceActions) serviceActions.style.display = (count > 0) ? 'flex' : 'none';
    }
}

// toggles multi-select mode (external button)
function toggleMultiSelect() {
    window.multiSelectMode = !window.multiSelectMode;
    console.log('toggleMultiSelect ->', window.multiSelectMode);

    // when turning off, clear selections
    if (!window.multiSelectMode) {
        // keep selections cleared to avoid accidental carries between modes
        window.selectedServicesSet && window.selectedServicesSet.clear();
        const serviceGrid = document.getElementById("serviceGrid");
        if (serviceGrid) {
            serviceGrid.querySelectorAll('.service-btn.selected').forEach(b => b.classList.remove('selected'));
            serviceGrid.querySelectorAll('.service-checkbox').forEach(cb => { cb.checked = false; cb.style.display = 'none'; });
        }
        const selectedCountEl = document.getElementById("selectedCount");
        const confirmBtn = document.getElementById("confirmBtn");
        if (selectedCountEl) selectedCountEl.textContent = '0 selected';
        if (confirmBtn) confirmBtn.disabled = true;
    } else {
        // show checkboxes
        const serviceGrid = document.getElementById("serviceGrid");
        if (serviceGrid) serviceGrid.querySelectorAll('.service-checkbox').forEach(cb => cb.style.display = '');
    }

    refreshUIForMode();
}

// helper: post selected services to backend
function postQueue(services) {
    if (!services || !services.length) {
        alert('No services selected');
        return;
    }
    const officeId = window.currentOfficeId || 0;
    if (!officeId) {
        alert('Missing office context');
        return;
    }

    const confirmBtn = document.getElementById("confirmBtn");
    if (confirmBtn) confirmBtn.disabled = true; // prevent duplicate submits

    const form = new FormData();
    form.append('office_id', officeId);
    // append services
    services.forEach(s => form.append('services[]', s));
    // append soft_copy flag for the request (0 = hard copy default, 1 = soft copy)
    form.append('soft_copy', window.requestSoftCopy ? 1 : 0);

    fetch('../backend/queueInsert.php', { method: 'POST', body: form })
        .then(r => r.json())
        .then(json => {
            if (json.success) {
                alert('Queue created: ' + json.queue_code);
                // clear selection UI/state
                window.selectedServicesSet.clear();
                refreshUIForMode();
                // return to main menu (office list)
                if (typeof goBack === 'function') goBack();
            } else {
                alert('Error: ' + (json.error || 'unknown'));
                if (confirmBtn) confirmBtn.disabled = false;
            }
        })
        .catch(err => {
            console.error(err);
            alert('Network or server error');
            if (confirmBtn) confirmBtn.disabled = false;
        });
}

// update confirmSelection to reuse postQueue
function confirmSelection() {
    // gather selected services (use Set if you maintain it, otherwise fallback)
    let selected = window.selectedServicesSet ? Array.from(window.selectedServicesSet) : [];
    if (!selected.length) {
        const checked = document.querySelectorAll('#serviceGrid .service-checkbox:checked');
        selected = Array.from(checked).map(cb => cb.value || cb.getAttribute('data-service-id') || cb.id.replace('svc-',''));
    }
    postQueue(selected);
}

// go back to office list
function goBack() {
    const officeSection = document.getElementById("officeSection");
    const serviceSection = document.getElementById("serviceSection");
    const serviceGrid = document.getElementById("serviceGrid");

    serviceSection && serviceSection.classList.remove("active");
    setTimeout(() => {
        serviceSection && (serviceSection.style.display = "none");
        officeSection && (officeSection.style.display = "block");
        setTimeout(() => officeSection && officeSection.classList.add("active"), 100);
    }, 400);

    // clear selections and reset mode
    window.selectedServicesSet && window.selectedServicesSet.clear();
    window.multiSelectMode = false;
    window.requestSoftCopy = false;
    document.body.classList.remove('multi-select-mode');
    if (serviceGrid) {
        serviceGrid.querySelectorAll('.service-btn.selected').forEach(b => b.classList.remove('selected'));
        serviceGrid.querySelectorAll('.service-checkbox').forEach(cb => { cb.checked = false; cb.style.display = 'none'; });
    }
    const selectedCountEl = document.getElementById("selectedCount");
    const confirmBtn = document.getElementById("confirmBtn");
    if (selectedCountEl) selectedCountEl.textContent = "0 selected";
    if (confirmBtn) confirmBtn.disabled = true;

    const softToggle = document.getElementById('softCopyToggle');
    const softLabel = document.getElementById('softCopyLabel');
    if (softToggle) softToggle.checked = false;
    if (softLabel) softLabel.textContent = 'Hard copy (default)';

    const serviceActions = document.getElementById("serviceActions");
    if (serviceActions) serviceActions.style.display = 'none';
}

// initial page setup
document.addEventListener("DOMContentLoaded", () => {
    console.log('kioskMenu.js DOMContentLoaded');
    const ss = document.getElementById("serviceSection");
    if (ss) ss.style.display = "none";
    const serviceActions = document.getElementById("serviceActions");
    if (serviceActions) serviceActions.style.display = 'none';
    refreshUIForMode();

    // attach external controls here so functions exist when clicked
    const toggleBtn = document.getElementById('multiSelectToggle');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', (e) => {
            e.preventDefault();
            toggleMultiSelect();
        });
    } else {
        console.warn('#multiSelectToggle not found');
    }

    // soft copy toggle handler
    const softToggle = document.getElementById('softCopyToggle');
    const softLabel = document.getElementById('softCopyLabel');
    if (softToggle) {
        softToggle.addEventListener('change', (e) => {
            window.requestSoftCopy = !!softToggle.checked;
            if (softLabel) softLabel.textContent = window.requestSoftCopy ? 'Soft copy (PDF)' : 'Hard copy (default)';
        });
    }

    const confirmBtn = document.getElementById('confirmBtn');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', (e) => {
            e.preventDefault();
            confirmSelection();
        });
    }

    const backBtn = document.getElementById('backBtn');
    if (backBtn) {
        backBtn.addEventListener('click', (e) => {
            e.preventDefault();
            goBack();
        });
    }

    // attach click handler to service grid for single-click behavior and multi-select toggling
    const serviceGrid = document.getElementById("serviceGrid");
    if (serviceGrid) {
        serviceGrid.addEventListener('click', (ev) => {
            const btn = ev.target.closest('.service-btn');
            if (!btn) return;
            const svcId = btn.getAttribute('data-service-id') || btn.querySelector('.service-checkbox')?.value;
            if (!svcId) return;

            if (window.multiSelectMode) {
                // toggle selection set and visual state
                if (!window.selectedServicesSet) window.selectedServicesSet = new Set();
                if (window.selectedServicesSet.has(svcId)) {
                    window.selectedServicesSet.delete(svcId);
                    btn.classList.remove('selected');
                    const cb = btn.querySelector('.service-checkbox');
                    if (cb) cb.checked = false;
                } else {
                    window.selectedServicesSet.add(svcId);
                    btn.classList.add('selected');
                    const cb = btn.querySelector('.service-checkbox');
                    if (cb) cb.checked = true;
                }
                refreshUIForMode();
            } else {
                // single select: do NOT immediately post — require confirmation
                // clear previous selection, select this one
                window.selectedServicesSet = new Set([svcId]);
                // update visuals
                serviceGrid.querySelectorAll('.service-btn.selected').forEach(b => b.classList.remove('selected'));
                btn.classList.add('selected');
                // ensure checkboxes hidden remain consistent
                const cb = btn.querySelector('.service-checkbox');
                if (cb) cb.checked = true;
                // show action bar so user must press Confirm
                const serviceActions = document.getElementById('serviceActions');
                if (serviceActions) serviceActions.style.display = 'flex';
                refreshUIForMode();
                updateSelectedCount();
            }

            function updateSelectedCount() {
                const selectedCountEl = document.getElementById("selectedCount");
                const count = window.selectedServicesSet ? window.selectedServicesSet.size : 0;
                if (selectedCountEl) selectedCountEl.textContent = `${count} selected`;
                const confirmBtn = document.getElementById('confirmBtn');
                if (confirmBtn) confirmBtn.disabled = count === 0;
            }
        });
    }
});

// expose to global scope used by HTML (office buttons use loadServices inline)
window.loadServices = loadServices;
window.toggleMultiSelect = toggleMultiSelect;
window.confirmSelection = confirmSelection;
window.goBack = goBack;
