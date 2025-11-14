// Modernized kiosk menu JS (per-service soft toggle + checkbox UI)

window.multiSelectMode = window.multiSelectMode || false;
window.selectedServicesSet = window.selectedServicesSet || new Set();
window.perServiceSoft = window.perServiceSoft || {}; // { id: 0|1 }

// Add visitor form handling at top of file (keeps existing functions below working)
window.visitorInfo = window.visitorInfo || { name: '', phone: '', email: '' };

function isValidPhone(p) {
    // allow only digits, must be 11 chars and start with 09
    return typeof p === 'string' && /^\s*09\d{9}\s*$/.test(p);
}

function isValidEmail(e) {
    if (!e) return false;
    // simple email regex
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(e);
}

function visitorSyncState() {
    const nameEl = document.getElementById('visitorName');
    const phoneEl = document.getElementById('visitorPhone');
    const emailEl = document.getElementById('visitorEmail');
    const proceedBtn = document.getElementById('visitorProceed');

    const name = nameEl ? nameEl.value.trim() : '';
    const phone = phoneEl ? phoneEl.value.trim() : '';
    const email = emailEl ? emailEl.value.trim() : '';

    const phoneOk = isValidPhone(phone);
    const emailOk = email === '' ? true : isValidEmail(email);

    // show inline hints
    const phoneHint = document.getElementById('visitorPhoneHint');
    if (phoneHint) phoneHint.style.display = phone === '' ? 'none' : (phoneOk ? 'none' : 'block');

    const emailHint = document.getElementById('visitorEmailHint');
    if (emailHint) emailHint.style.display = email === '' ? 'none' : (emailOk ? 'none' : 'block');

    proceedBtn.disabled = !(name.length > 0 && phoneOk && emailOk);
}

function openVisitorConfirm() {
    const name = document.getElementById('visitorName').value.trim();
    const phone = document.getElementById('visitorPhone').value.trim();
    const email = document.getElementById('visitorEmail').value.trim();

    document.getElementById('confirmName').textContent = name || '-';
    document.getElementById('confirmPhone').textContent = phone || '-';
    document.getElementById('confirmEmail').textContent = email || '-';

    const modal = document.getElementById('visitorConfirmModal');
    if (modal) modal.style.display = 'flex';
}

function closeVisitorConfirm() {
    const modal = document.getElementById('visitorConfirmModal');
    if (modal) modal.style.display = 'none';
}

function acceptVisitorAndEnter() {
    // store info
    window.visitorInfo.name = document.getElementById('visitorName').value.trim();
    window.visitorInfo.phone = document.getElementById('visitorPhone').value.trim();
    window.visitorInfo.email = document.getElementById('visitorEmail').value.trim();

    closeVisitorConfirm();

    // show kiosk (officeSection) and hide visitor form
    const visitor = document.getElementById('visitorForm');
    const officeSection = document.getElementById('officeSection');
    if (visitor) visitor.style.display = 'none';
    if (officeSection) {
        officeSection.style.display = 'block';
        officeSection.classList.add('active');
    }
}

function refreshUIForMode() {
    const toggleBtn = document.getElementById('multiSelectToggle');

    if (window.multiSelectMode) {
        document.body.classList.add('multi-select-mode');
        if (toggleBtn) toggleBtn.textContent = 'Cancel';
        // Checkboxes are completely removed - no display logic needed
    } else {
        document.body.classList.remove('multi-select-mode');
        if (toggleBtn) toggleBtn.textContent = 'Select multiple';
        // Checkboxes are completely removed - no display logic needed
    }

    const confirmBtn = document.getElementById('confirmBtn');
    if (confirmBtn) confirmBtn.disabled = !(window.selectedServicesSet && window.selectedServicesSet.size > 0);
}

function loadServices(officeId, officeName) {
    const officeSection = document.getElementById("officeSection");
    const serviceSection = document.getElementById("serviceSection");
    const serviceTitle = document.getElementById("serviceTitle");
    const serviceGrid = document.getElementById("serviceGrid");

    if (!serviceGrid) return;
    serviceTitle && (serviceTitle.textContent = "Services — " + officeName);
    window.currentOfficeId = officeId;
    window.selectedServicesSet = new Set();
    window.perServiceSoft = {};

    refreshUIForMode();

    fetch(`../backend/kioskMenuData.php?office_id=${encodeURIComponent(officeId)}`)
    .then(r => r.text())
    .then(html => {
        serviceGrid.innerHTML = html;

        // Ensure PDF checkboxes are created if missing from backend
        serviceGrid.querySelectorAll('.service-card').forEach(card => {
            const sid = card.getAttribute('data-service-id');
            let serviceControls = card.querySelector('.service-controls');
            
            // Create service-controls if missing
            if (!serviceControls) {
                serviceControls = document.createElement('div');
                serviceControls.className = 'service-controls';
                card.appendChild(serviceControls);
            }
            
            // Create PDF checkbox if missing
            let softCb = card.querySelector('.pdf-checkbox');
            if (!softCb) {
                serviceControls.innerHTML = `
                    <span class="soft-label">PDF</span>
                    <input type="checkbox" class="pdf-checkbox" id="pdf-${sid}">
                    <input type="checkbox" class="service-checkbox" value="${sid}" style="display:none;">
                `;
                softCb = card.querySelector('.pdf-checkbox');
            }

            const hiddenCb = card.querySelector('.service-checkbox');

            // initialize per-service soft default (0 = hard)
            window.perServiceSoft[sid] = (softCb && softCb.checked) ? 1 : 0;

            // ensure soft toggle is clickable and does not bubble to card click
            if (softCb) {
                // remove disabling — allow click; clicking will auto-select the card if needed
                softCb.disabled = false;

                // stop clicks on the checkbox from triggering the card click
                softCb.addEventListener('click', (ev) => {
                    ev.stopPropagation();
                }, {passive: true});

                // handle change: update perServiceSoft and auto-select on toggle ON
                softCb.addEventListener('change', (e) => {
                    // update mapping
                    window.perServiceSoft[sid] = softCb.checked ? 1 : 0;

                    // if user toggled ON and service not selected yet, select it
                    if (softCb.checked && !(window.selectedServicesSet && window.selectedServicesSet.has(sid))) {
                        // If NOT in multi-select mode, selecting a soft copy should behave like a single selection:
                        // clear other selections and select this one only.
                        if (!window.multiSelectMode) {
                            clearAllSelections();
                        }
                        if (!window.selectedServicesSet) window.selectedServicesSet = new Set();
                        window.selectedServicesSet.add(sid);
                        card.classList.add('selected');
                        if (hiddenCb) hiddenCb.checked = true;
                        // service actions is always visible now
                        refreshUIForMode();
                        updateSelectedCount();
                    }
                });
            }

            // card click handles selection (ignore clicks inside controls area)
            card.addEventListener('click', (e) => {
                // if click happened inside the controls area (checkbox/label), do nothing here
                if (e.target.closest('.service-controls') || e.target.closest('.pdf-checkbox') || e.target.closest('label')) {
                    return;
                }

                if (window.multiSelectMode) {
                    toggleCardSelection(card, sid, hiddenCb, softCb);
                } else {
                    // single selection: toggle the single selection (allow deselect)
                    if (!window.selectedServicesSet) window.selectedServicesSet = new Set();
                    if (window.selectedServicesSet.has(sid)) {
                        // deselect current
                        clearAllSelections();
                    } else {
                        // select this single card (clear others)
                        clearAllSelections();
                        window.selectedServicesSet.add(sid);
                        card.classList.add('selected');
                        if (hiddenCb) hiddenCb.checked = true;
                    }
                    // keep soft toggle state as-is (user can toggle after)
                    refreshUIForMode();
                    updateSelectedCount();
                }
            });
        });

        // transition to services view
        if (officeSection) officeSection.classList.remove("active");
        setTimeout(() => {
            if (officeSection) officeSection.style.display = "none";
            if (serviceSection) serviceSection.style.display = "block";
            setTimeout(() => serviceSection && serviceSection.classList.add("active"), 80);
        }, 260);

        refreshUIForMode();
        updateSelectedCount();
    })
    .catch(err => {
        console.error(err);
        serviceGrid.innerHTML = "<p class='muted'>Error loading services</p>";
    });

    function toggleCardSelection(card, sid, hiddenCb, softCb){
        if (!window.selectedServicesSet) window.selectedServicesSet = new Set();
        if (window.selectedServicesSet.has(sid)) {
            window.selectedServicesSet.delete(sid);
            card.classList.remove('selected');
            if (hiddenCb) hiddenCb.checked = false;
            // when deselected, clear soft toggle mapping but keep the user's checkbox visual state (optional)
            if (softCb) { softCb.checked = false; window.perServiceSoft[sid] = 0; }
        } else {
            window.selectedServicesSet.add(sid);
            card.classList.add('selected');
            if (hiddenCb) hiddenCb.checked = true;
            // keep soft toggle as-is (user may have toggled it already)
        }
        refreshUIForMode();
        updateSelectedCount();
    }

    function clearAllSelections(){
        const grid = document.getElementById('serviceGrid');
        if (!grid) return;
        grid.querySelectorAll('.service-card.selected').forEach(c => c.classList.remove('selected'));
        grid.querySelectorAll('.service-checkbox').forEach(cb => cb.checked = false);
        // clear soft toggle visuals and mapping
        grid.querySelectorAll('.pdf-checkbox').forEach(s => { s.checked = false; });
        window.selectedServicesSet && window.selectedServicesSet.clear();
        window.perServiceSoft = {};
    }

    function updateSelectedCount(){
        const cnt = window.selectedServicesSet ? window.selectedServicesSet.size : 0;
        const el = document.getElementById('selectedCount');
        if (el) el.textContent = `${cnt} selected`;
        const confirmBtn = document.getElementById('confirmBtn');
        if (confirmBtn) confirmBtn.disabled = cnt === 0;
    }
}

function toggleMultiSelect(){
    window.multiSelectMode = !window.multiSelectMode;
    if (!window.multiSelectMode) {
        // clear any multi selections on exit
        window.selectedServicesSet && window.selectedServicesSet.clear();
        document.querySelectorAll('.service-card.selected').forEach(c => c.classList.remove('selected'));
        // Checkboxes removed - no need to manipulate display
        document.querySelectorAll('.service-checkbox').forEach(cb => { cb.checked = false; });
        // keep soft toggles visible and reset them to unchecked for clarity
        document.querySelectorAll('.pdf-checkbox').forEach(cb => cb.checked = false);
        document.getElementById('selectedCount').textContent = '0 selected';
        const confirmBtn = document.getElementById('confirmBtn');
        if (confirmBtn) confirmBtn.disabled = true;
    }
    refreshUIForMode();
}

function confirmSelection(){
    const selected = Array.from(window.selectedServicesSet || []);
    if (!selected.length) {
        const checked = document.querySelectorAll('#serviceGrid .service-checkbox:checked');
        checked.forEach(cb => selected.push(cb.value));
    }
    if (!selected.length) { alert('No services selected'); return; }
    postQueue(selected);
}

function postQueue(services){
    if (!services || !services.length) return;
    const officeId = window.currentOfficeId || 0;
    if (!officeId){ alert('Missing office'); return; }

    const btn = document.getElementById('confirmBtn');
    if (btn) btn.disabled = true;

    const form = new FormData();
    form.append('office_id', officeId);

    services.forEach(sid => {
        form.append('services[]', sid);
        form.append('soft_copy[]', window.perServiceSoft && window.perServiceSoft[sid] ? 1 : 0);
    });

    // include visitor info if present
    if (window.visitorInfo) {
        form.append('customer_name', window.visitorInfo.name || '');
        form.append('customer_phone', window.visitorInfo.phone || '');
        form.append('customer_email', window.visitorInfo.email || '');
    }

    fetch('../backend/queueInsert.php', { method: 'POST', body: form })
        .then(r => r.json())
        .then(json => {
            if (json && json.success) {
                window.location.href = `../includes/receipt.php?queue_id=${encodeURIComponent(json.queue_id)}`;
            } else {
                alert('Error: ' + (json && json.error ? json.error : 'unknown'));
                if (btn) btn.disabled = false;
            }
        })
        .catch(err => {
            console.error(err);
            alert('Server error');
            if (btn) btn.disabled = false;
        });
}

function goBack(){
    const officeSection = document.getElementById("officeSection");
    const serviceSection = document.getElementById("serviceSection");
    const serviceGrid = document.getElementById("serviceGrid");

    // IMMEDIATE transition - NO DELAYS
    if (serviceSection) {
        serviceSection.classList.remove("active");
        serviceSection.style.display = "none";
    }
    if (officeSection) {
        officeSection.style.display = "block";
        officeSection.classList.add("active");
    }

    window.selectedServicesSet && window.selectedServicesSet.clear();
    window.perServiceSoft = {};
    window.multiSelectMode = false;
    document.body.classList.remove('multi-select-mode');

    if (serviceGrid) {
        serviceGrid.innerHTML = '';
    }
    
    const selectedCount = document.getElementById('selectedCount');
    if (selectedCount) selectedCount.textContent = '0 selected';
    
    const confirmBtn = document.getElementById('confirmBtn');
    if (confirmBtn) confirmBtn.disabled = true;
}

document.addEventListener('DOMContentLoaded', () => {
    const toggleBtn = document.getElementById('multiSelectToggle');
    if (toggleBtn) toggleBtn.addEventListener('click', (e)=>{ e.preventDefault(); toggleMultiSelect(); });

    const confirmBtn = document.getElementById('confirmBtn');
    if (confirmBtn) confirmBtn.addEventListener('click', (e)=>{ e.preventDefault(); confirmSelection(); });

    const backBtn = document.getElementById('backBtn');
    if (backBtn) backBtn.addEventListener('click', (e)=>{ e.preventDefault(); goBack(); });

    const ss = document.getElementById('serviceSection');
    if (ss) ss.style.display = 'none';

    // visitor form initialisation
    const visitorForm = document.getElementById('visitorForm');
    if (visitorForm) {
        // show visitor form first, hide office section
        const officeSection = document.getElementById('officeSection');
        if (officeSection) officeSection.style.display = 'none';
        // attach input listeners
        ['visitorName','visitorPhone','visitorEmail'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('input', visitorSyncState, {passive:true});
        });

        const proceedBtn = document.getElementById('visitorProceed');
        if (proceedBtn) proceedBtn.addEventListener('click', (e) => {
            e.preventDefault();
            openVisitorConfirm();
        });

        const cancelConfirm = document.getElementById('visitorCancel');
        if (cancelConfirm) cancelConfirm.addEventListener('click', (e)=>{ e.preventDefault(); closeVisitorConfirm(); });

        const confirmBtn = document.getElementById('visitorConfirm');
        if (confirmBtn) confirmBtn.addEventListener('click', (e)=>{ e.preventDefault(); acceptVisitorAndEnter(); });

        // initialize state
        visitorSyncState();
    }
});

// expose for inline office buttons
window.loadServices = loadServices;
window.toggleMultiSelect = toggleMultiSelect;
window.confirmSelection = confirmSelection;
window.goBack = goBack;