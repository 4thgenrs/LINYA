// Modernized kiosk menu JS (per-service soft toggle + minimal UI + pagination)

window.multiSelectMode = window.multiSelectMode || false;
window.selectedServicesSet = window.selectedServicesSet || new Set();
window.perServiceSoft = window.perServiceSoft || {}; // { id: 0|1 }

// Add visitor form handling at top of file (keeps existing functions below working)
window.visitorInfo = window.visitorInfo || { name: '', phone: '', email: '' };

// ========== PAGINATION STATE AND FUNCTIONS ==========
window.paginationState = {
    office: { currentPage: 1, itemsPerPage: 6, totalItems: 0 },
    service: { currentPage: 1, itemsPerPage: 6, totalItems: 0 }
};

function createPaginationHTML(type, currentPage, totalPages) {
    if (totalPages <= 1) return '';

    let html = '<div class="pagination-container">';
    html += `<div class="pagination-info">Page ${currentPage} of ${totalPages}</div>`;
    html += '<div class="pagination-controls">';
    
    // Previous button
    html += `<button class="pagination-btn prev-btn" onclick="changePage('${type}', ${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>Prev</button>`;
    
    // Page numbers
    const maxVisible = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxVisible / 2));
    let endPage = Math.min(totalPages, startPage + maxVisible - 1);
    
    if (endPage - startPage < maxVisible - 1) {
        startPage = Math.max(1, endPage - maxVisible + 1);
    }
    
    if (startPage > 1) {
        html += `<button class="pagination-btn" onclick="changePage('${type}', 1)">1</button>`;
        if (startPage > 2) {
            html += '<span class="pagination-dots">...</span>';
        }
    }
    
    for (let i = startPage; i <= endPage; i++) {
        html += `<button class="pagination-btn ${i === currentPage ? 'active' : ''}" onclick="changePage('${type}', ${i})">${i}</button>`;
    }
    
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            html += '<span class="pagination-dots">...</span>';
        }
        html += `<button class="pagination-btn" onclick="changePage('${type}', ${totalPages})">${totalPages}</button>`;
    }
    
    // Next button
    html += `<button class="pagination-btn next-btn" onclick="changePage('${type}', ${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>Next</button>`;
    
    html += '</div></div>';
    return html;
}

function paginateItems(items, page, itemsPerPage) {
    const start = (page - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    return items.slice(start, end);
}

function changePage(type, newPage) {
    const state = window.paginationState[type];
    const totalPages = Math.ceil(state.totalItems / state.itemsPerPage);
    
    if (newPage < 1 || newPage > totalPages) return;
    
    state.currentPage = newPage;
    
    if (type === 'office') {
        renderOfficePage();
    } else if (type === 'service') {
        renderServicePage();
    }
    
    // Smooth scroll to top of section
    const container = type === 'office' 
        ? document.querySelector('.office-section-container')
        : document.querySelector('.service-section-container');
    
    if (container) {
        container.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

// Resolve and navigate to kioskSelect robustly (works when this script is included from different folders)
function forceNavigate(url) {
    console.log('[kioskMenu] forceNavigate ->', url);
    // First attempt: create and submit a hidden form (often succeeds where location assignments are blocked)
    try {
        const f = document.createElement('form');
        f.action = url;
        f.method = 'GET';
        f.style.display = 'none';
        document.body.appendChild(f);
        f.submit();
        return;
    } catch (e) {
        console.warn('[kioskMenu] form submit fallback failed', e);
    }

    try { window.location.assign(url); } catch (e) { console.warn('[kioskMenu] assign failed', e); }

    // retries using different methods — some environments may block one of these
    setTimeout(() => {
        try { window.location.href = url; } catch (e) { console.warn('[kioskMenu] href assignment failed', e); }
    }, 150);

    setTimeout(() => {
        try {
            const a = document.createElement('a');
            a.href = url;
            a.style.display = 'none';
            document.body.appendChild(a);
            a.click();
            setTimeout(() => a.remove(), 50);
        } catch (e) { console.warn('[kioskMenu] anchor click failed', e); }
    }, 300);

    setTimeout(() => {
        try { top.location.href = url; } catch (e) { console.warn('[kioskMenu] top.location failed', e); }
    }, 450);

    setTimeout(() => {
        try { window.location.replace(url); } catch (e) { console.warn('[kioskMenu] replace failed', e); }
    }, 600);
}

function redirectToKioskSelect() {
    console.log('[kioskMenu] redirectToKioskSelect() called');
    try {
        const anchor = document.querySelector('a[href*="kioskSelect.php"]');
        if (anchor && anchor.href) {
            console.log('[kioskMenu] redirect via anchor.href ->', anchor.href);
            forceNavigate(anchor.href);
            return;
        }
    } catch (e) {
        console.warn('[kioskMenu] redirect anchor check failed', e);
    }

    // Compute site root (first path segment) and build includes path
    try {
        const parts = window.location.pathname.split('/').filter(Boolean);
        let root = 'LINYA';
        if (parts.length > 0) root = parts[0];
        const origin = window.location.origin || '';
        const target = origin + '/' + root + '/includes/kioskSelect.php';
        console.log('[kioskMenu] redirect via computed path ->', target);
        forceNavigate(target);
        return;
    } catch (e) {
        console.warn('[kioskMenu] computed redirect failed', e);
    }

    // final fallback
    forceNavigate('kioskSelect.php');
}

// Simple built-in duplicate modal fallback (works when SweetAlert unavailable)
function showDuplicateModal(message) {
    try {
        if (document.getElementById('customDuplicateModal')) return;

        const overlay = document.createElement('div');
        overlay.id = 'customDuplicateModal';
        overlay.style.position = 'fixed';
        overlay.style.left = '0';
        overlay.style.top = '0';
        overlay.style.right = '0';
        overlay.style.bottom = '0';
        overlay.style.background = 'rgba(0,0,0,0.45)';
        overlay.style.display = 'flex';
        overlay.style.alignItems = 'center';
        overlay.style.justifyContent = 'center';
        overlay.style.zIndex = 20000;

        const card = document.createElement('div');
        card.style.background = '#fff';
        card.style.borderRadius = '8px';
        card.style.padding = '20px';
        card.style.maxWidth = '520px';
        card.style.boxShadow = '0 8px 32px rgba(0,0,0,0.3)';
        card.style.fontFamily = 'Segoe UI, Roboto, Arial, sans-serif';

        const title = document.createElement('h3');
        title.textContent = 'Unable to join queue';
        title.style.margin = '0 0 8px 0';

        const p = document.createElement('p');
        p.textContent = message || 'A duplicate queue exists.';
        p.style.margin = '0 0 18px 0';
        p.style.color = '#333';

        const btn = document.createElement('button');
        btn.textContent = 'OK';
        btn.style.background = '#0b74de';
        btn.style.color = '#fff';
        btn.style.border = 'none';
        btn.style.padding = '10px 18px';
        btn.style.borderRadius = '6px';
        btn.style.cursor = 'pointer';

        btn.addEventListener('click', function() {
            console.log('[kioskMenu] duplicate-ok clicked');
            try { document.body.removeChild(overlay); } catch (e) { console.warn('[kioskMenu] remove overlay failed', e); }
            try {
                redirectToKioskSelect();
            } catch (e) {
                console.warn('[kioskMenu] redirectToKioskSelect failed', e);
                try { window.location.href = '/LINYA/includes/kioskSelect.php'; } catch (e2) { console.warn('[kioskMenu] href assign failed', e2); try { window.location.href = 'kioskSelect.php'; } catch (e3) { console.error('[kioskMenu] final redirect failed', e3); } }
            }
            // final fallback: try again shortly after (some environments need a short delay)
            setTimeout(() => {
                try { window.location.href = '/LINYA/includes/kioskSelect.php'; } catch (e) {}
            }, 200);
        });

        card.appendChild(title);
        card.appendChild(p);
        card.appendChild(btn);
        overlay.appendChild(card);
        document.body.appendChild(overlay);
    } catch (e) {
        console.error('[kioskMenu] showDuplicateModal failed', e);
        try { window.location.href = '/LINYA/includes/kioskSelect.php'; } catch (e2) { window.location.href = 'kioskSelect.php'; }
    }
}

// Generic modal used for non-duplicate errors. `onOk` is optional.
function showGenericModal(message, onOk) {
    try {
        if (document.getElementById('customGenericModal')) return;

        const overlay = document.createElement('div');
        overlay.id = 'customGenericModal';
        overlay.style.position = 'fixed';
        overlay.style.left = '0';
        overlay.style.top = '0';
        overlay.style.right = '0';
        overlay.style.bottom = '0';
        overlay.style.background = 'rgba(0,0,0,0.45)';
        overlay.style.display = 'flex';
        overlay.style.alignItems = 'center';
        overlay.style.justifyContent = 'center';
        overlay.style.zIndex = 20000;

        const card = document.createElement('div');
        card.style.background = '#fff';
        card.style.borderRadius = '8px';
        card.style.padding = '20px';
        card.style.maxWidth = '520px';
        card.style.boxShadow = '0 8px 32px rgba(0,0,0,0.3)';
        card.style.fontFamily = 'Segoe UI, Roboto, Arial, sans-serif';

        const title = document.createElement('h3');
        title.textContent = 'Error';
        title.style.margin = '0 0 8px 0';

        const p = document.createElement('p');
        p.textContent = message || 'An error occurred.';
        p.style.margin = '0 0 18px 0';
        p.style.color = '#333';

        const btn = document.createElement('button');
        btn.textContent = 'OK';
        btn.style.background = '#0b74de';
        btn.style.color = '#fff';
        btn.style.border = 'none';
        btn.style.padding = '10px 18px';
        btn.style.borderRadius = '6px';
        btn.style.cursor = 'pointer';

        btn.addEventListener('click', function() {
            try { document.body.removeChild(overlay); } catch (e) {}
            try { if (typeof onOk === 'function') onOk(); } catch (e) {}
        });

        card.appendChild(title);
        card.appendChild(p);
        card.appendChild(btn);
        overlay.appendChild(card);
        document.body.appendChild(overlay);
    } catch (e) {
        console.error('[kioskMenu] showGenericModal failed', e);
        try { if (typeof onOk === 'function') onOk(); } catch (e2) {}
    }
}

function renderOfficePage() {
    const grid = document.getElementById('officeGrid');
    if (!grid) return;
    
    const allOffices = Array.from(grid.querySelectorAll('.office-btn'));
    window.paginationState.office.totalItems = allOffices.length;
    
    const state = window.paginationState.office;
    const totalPages = Math.ceil(state.totalItems / state.itemsPerPage);
    
    // Hide all offices first
    allOffices.forEach(btn => btn.style.display = 'none');
    
    // Show only current page offices
    const currentPageOffices = paginateItems(allOffices, state.currentPage, state.itemsPerPage);
    currentPageOffices.forEach(btn => btn.style.display = '');
    
    // Add animation
    grid.classList.add('animating');
    setTimeout(() => grid.classList.remove('animating'), 400);
    
    // Update or create pagination
    let paginationDiv = document.getElementById('officePagination');
    if (!paginationDiv) {
        paginationDiv = document.createElement('div');
        paginationDiv.id = 'officePagination';
        grid.parentNode.appendChild(paginationDiv);
    }
    
    paginationDiv.innerHTML = createPaginationHTML('office', state.currentPage, totalPages);
}

function renderServicePage() {
    const grid = document.getElementById('serviceGrid');
    if (!grid) return;
    
    const allServices = Array.from(grid.querySelectorAll('.service-card'));
    window.paginationState.service.totalItems = allServices.length;
    
    const state = window.paginationState.service;
    const totalPages = Math.ceil(state.totalItems / state.itemsPerPage);
    
    // Hide all services first
    allServices.forEach(card => card.style.display = 'none');
    
    // Show only current page services
    const currentPageServices = paginateItems(allServices, state.currentPage, state.itemsPerPage);
    currentPageServices.forEach(card => card.style.display = '');
    
    // Add animation
    grid.classList.add('animating');
    setTimeout(() => grid.classList.remove('animating'), 400);
    
    // Update or create pagination
    let paginationDiv = document.getElementById('servicePagination');
    if (!paginationDiv) {
        paginationDiv = document.createElement('div');
        paginationDiv.id = 'servicePagination';
        const serviceSection = document.getElementById('serviceSection');
        if (serviceSection) {
            serviceSection.appendChild(paginationDiv);
        }
    }
    
    paginationDiv.innerHTML = createPaginationHTML('service', state.currentPage, totalPages);
}

// ========== VISITOR FORM FUNCTIONS ==========
function isValidPhone(p) {
    // allow only digits, must be 11 chars and start with 09
    return typeof p === 'string' && /^\s*09\d{9}\s*$/.test(p);
}

function isValidEmail(e) {
    if (!e) return false;
    // simple email regex
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(e);
}

function isValidStudentNumber(s) {
    if (!s) return false;
    const re = /^(?:2000|2001|2002|2003|2004|2005|2006|2007|2008|2009|2010|2011|2012|2013|2014|2015|2016|2017|2018|2019|2020|2021|2022|2023|2024|2025|2026)\d{4}-[A-Z]$/;
    return re.test((s||'').trim().toUpperCase());
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

    // student-specific
    const isStudentEl = document.getElementById('visitorIsStudent');
    const studentNumberEl = document.getElementById('visitorStudentNumber');
    const courseEl = document.getElementById('visitorCourse');
    const isStudent = isStudentEl ? isStudentEl.checked : false;
    const studentNum = studentNumberEl ? studentNumberEl.value.trim() : '';
    const courseVal = courseEl ? courseEl.value : '';
    const studentOk = !isStudent || (isStudent && isValidStudentNumber(studentNum) && courseVal);

    const studentNumberHint = document.getElementById('visitorStudentNumberHint');
    if (studentNumberHint) studentNumberHint.style.display = (isStudent && studentNum !== '') ? (isValidStudentNumber(studentNum) ? 'none' : 'block') : 'none';

    proceedBtn.disabled = !(name.length > 0 && phoneOk && emailOk && studentOk);
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
    // student details (optional)
    try {
        window.visitorInfo.student_number = document.getElementById('visitorStudentNumber') ? document.getElementById('visitorStudentNumber').value.trim() : '';
        window.visitorInfo.course = document.getElementById('visitorCourse') ? document.getElementById('visitorCourse').value : '';
    } catch (e) { window.visitorInfo.student_number = ''; window.visitorInfo.course = ''; }

    closeVisitorConfirm();

    // Hide the entire kiosk container
    const kioskContainer = document.querySelector('.kiosk-container');
    const officeSection = document.getElementById('officeSection');
    
    if (kioskContainer) kioskContainer.style.display = 'none';
    
    // Show office section
    if (officeSection) {
        officeSection.style.display = 'block';
        setTimeout(() => officeSection.classList.add('active'), 80);
    }
}

// ========== SERVICE SELECTION FUNCTIONS ==========
function refreshUIForMode() {
    const serviceActions = document.getElementById('serviceActions');
    const toggleBtn = document.getElementById('multiSelectToggle');

    if (window.multiSelectMode) {
        document.body.classList.add('multi-select-mode');
        if (serviceActions) serviceActions.style.display = 'flex';
        if (toggleBtn) toggleBtn.textContent = 'Cancel';
        document.querySelectorAll('.service-checkbox').forEach(cb => cb.style.display = '');
    } else {
        document.body.classList.remove('multi-select-mode');
        if (serviceActions) serviceActions.style.display = (window.selectedServicesSet && window.selectedServicesSet.size > 0) ? 'flex' : 'none';
        if (toggleBtn) toggleBtn.textContent = 'Select multiple';
        document.querySelectorAll('.service-checkbox').forEach(cb => cb.style.display = 'none');
    }

    const confirmBtn = document.getElementById('confirmBtn');
    if (confirmBtn) confirmBtn.disabled = !(window.selectedServicesSet && window.selectedServicesSet.size > 0);
}

function loadServices(officeId, officeName) {
    const officeSection = document.getElementById("officeSection");
    const serviceSection = document.getElementById("serviceSection");
    const serviceSectionContainer = document.querySelector(".service-section-container");
    const serviceTitle = document.getElementById("serviceTitle");
    const serviceGrid = document.getElementById("serviceGrid");

    if (!serviceGrid) return;
    
    // Update title
    serviceTitle && (serviceTitle.textContent = "Services — " + officeName);
    window.currentOfficeId = officeId;
    window.selectedServicesSet = new Set();
    window.perServiceSoft = {};

    // Reset service pagination
    window.paginationState.service.currentPage = 1;

    refreshUIForMode();

    // Fetch services
    fetch(`../backend/kioskMenuData.php?office_id=${encodeURIComponent(officeId)}`)
        .then(r => r.text())
        .then(html => {
            serviceGrid.innerHTML = html;

            // wire events for cards
            serviceGrid.querySelectorAll('.service-card').forEach(card => {
                const sid = card.getAttribute('data-service-id');
                const softCb = card.querySelector('.service-soft-checkbox');
                const hiddenCb = card.querySelector('.service-checkbox');

                // initialize per-service soft default (0 = hard)
                window.perServiceSoft[sid] = (softCb && softCb.checked) ? 1 : 0;

                if (softCb) {
                    softCb.disabled = false;

                    softCb.addEventListener('click', (ev) => {
                        ev.stopPropagation();
                    }, {passive: true});

                    softCb.addEventListener('change', (e) => {
                        window.perServiceSoft[sid] = softCb.checked ? 1 : 0;

                        if (softCb.checked && !(window.selectedServicesSet && window.selectedServicesSet.has(sid))) {
                            if (!window.multiSelectMode) {
                                clearAllSelections();
                            }
                            if (!window.selectedServicesSet) window.selectedServicesSet = new Set();
                            window.selectedServicesSet.add(sid);
                            card.classList.add('selected');
                            if (hiddenCb) hiddenCb.checked = true;
                            const serviceActions = document.getElementById('serviceActions');
                            if (serviceActions) serviceActions.style.display = 'flex';
                            refreshUIForMode();
                            updateSelectedCount();
                        }
                    });
                }

                card.addEventListener('click', (e) => {
                    if (e.target.closest('.service-controls') || e.target.closest('.service-soft-checkbox') || e.target.closest('label.switch')) {
                        return;
                    }

                    if (window.multiSelectMode) {
                        toggleCardSelection(card, sid, hiddenCb, softCb);
                    } else {
                        if (!window.selectedServicesSet) window.selectedServicesSet = new Set();
                        if (window.selectedServicesSet.has(sid)) {
                            clearAllSelections();
                            const serviceActions = document.getElementById('serviceActions');
                            if (serviceActions) serviceActions.style.display = 'none';
                        } else {
                            clearAllSelections();
                            window.selectedServicesSet.add(sid);
                            card.classList.add('selected');
                            if (hiddenCb) hiddenCb.checked = true;
                            const serviceActions = document.getElementById('serviceActions');
                            if (serviceActions) serviceActions.style.display = 'flex';
                        }
                        refreshUIForMode();
                        updateSelectedCount();
                    }
                });
            });

            // Smooth Transition: Fade out office section, populate content, then show service container
            if (officeSection) {
                officeSection.classList.remove("active");
            }
            
            setTimeout(() => {
                // Hide office section
                if (officeSection) officeSection.style.display = "none";
                
                // Prepare service section (but keep hidden)
                if (serviceSection) {
                    serviceSection.style.display = "block";
                    serviceSection.classList.remove("active"); // ensure it starts hidden
                }
                
                // Wait a tiny bit for content to render, then show container
                requestAnimationFrame(() => {
                    if (serviceSectionContainer) serviceSectionContainer.style.display = 'block';
                    
                    // Then trigger the animation
                    setTimeout(() => {
                        if (serviceSection) serviceSection.classList.add("active");
                        
                        // Initialize service pagination AFTER a short delay to ensure DOM is ready
                        setTimeout(() => {
                            renderServicePage();
                        }, 100);
                    }, 50);
                });
            }, 300);

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
            if (softCb) { softCb.checked = false; window.perServiceSoft[sid] = 0; }
        } else {
            window.selectedServicesSet.add(sid);
            card.classList.add('selected');
            if (hiddenCb) hiddenCb.checked = true;
        }
        refreshUIForMode();
        updateSelectedCount();
    }

    function clearAllSelections(){
        const grid = document.getElementById('serviceGrid');
        if (!grid) return;
        grid.querySelectorAll('.service-card.selected').forEach(c => c.classList.remove('selected'));
        grid.querySelectorAll('.service-checkbox').forEach(cb => cb.checked = false);
        grid.querySelectorAll('.service-soft-checkbox').forEach(s => { s.checked = false; });
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
        window.selectedServicesSet && window.selectedServicesSet.clear();
        document.querySelectorAll('.service-card.selected').forEach(c => c.classList.remove('selected'));
        document.querySelectorAll('.service-checkbox').forEach(cb => { cb.checked = false; cb.style.display = 'none'; });
        document.querySelectorAll('.service-soft-checkbox').forEach(cb => cb.checked = false);
        document.getElementById('selectedCount').textContent = '0 selected';
        const confirmBtn = document.getElementById('confirmBtn');
        if (confirmBtn) confirmBtn.disabled = true;
    } else {
        document.querySelectorAll('.service-checkbox').forEach(cb => cb.style.display = '');
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

    if (window.visitorInfo) {
        form.append('customer_name', window.visitorInfo.name || '');
        form.append('customer_phone', window.visitorInfo.phone || '');
        form.append('customer_email', window.visitorInfo.email || '');
        if (window.visitorInfo.client_id && parseInt(window.visitorInfo.client_id) > 0) {
            form.append('client_id', window.visitorInfo.client_id);
        }
        // optional student info
        if (window.visitorInfo.student_number) form.append('student_number', window.visitorInfo.student_number);
        if (window.visitorInfo.course) form.append('course', window.visitorInfo.course);
    }

    (async function(){
        try {
            const resp = await fetch('../backend/queueInsert.php', { method: 'POST', body: form });
            const text = await resp.text();
            let json = null;
            try { json = text ? JSON.parse(text) : null; } catch (e) { json = null; }

            const code = json && json.error_code ? json.error_code : (json && json.error ? json.error : null);
            const message = (json && (json.error || json.message)) ? (json.error || json.message) : (text && text.trim() ? text.trim() : `HTTP ${resp.status}`);
            const isDuplicate = code === 'duplicate_client_pending' || code === 'duplicate_guest_pending' || /duplicate|already have|already an existing|already exists|already have an/i.test((message||'').toLowerCase());

            if (resp.ok && json && json.success) {
                window.location.href = `../includes/receipt.php?queue_id=${encodeURIComponent(json.queue_id)}`;
                return;
            }

            // show friendly modal and redirect on duplicate (use built-in modal only)
            if (isDuplicate) {
                showDuplicateModal(message);
            } else {
                // non-duplicate error: show generic modal and re-enable button when OK pressed
                showGenericModal(message, () => { if (btn) btn.disabled = false; });
            }
        } catch (err) {
            console.error('[kioskMenu] postQueue fetch error', err);
            const errMsg = 'A server error occurred. Please try again.';
            showGenericModal(errMsg, () => { if (btn) btn.disabled = false; });
        }
    })();
}

function goBack(){
    const officeSection = document.getElementById("officeSection");
    const serviceSection = document.getElementById("serviceSection");
    const serviceSectionContainer = document.querySelector(".service-section-container");
    const serviceGrid = document.getElementById("serviceGrid");

    // Hide service section
    serviceSection && serviceSection.classList.remove("active");
    
    setTimeout(() => {
        if (serviceSectionContainer) serviceSectionContainer.style.display = 'none';
        serviceSection && (serviceSection.style.display = "none");
        
        // Show office section
        if (officeSection) {
            officeSection.style.display = "block";
            setTimeout(() => officeSection && officeSection.classList.add("active"), 80);
        }
    }, 260);

    // Reset state
    window.selectedServicesSet && window.selectedServicesSet.clear();
    window.perServiceSoft = {};
    window.multiSelectMode = false;
    window.paginationState.service.currentPage = 1; // Reset service pagination
    document.body.classList.remove('multi-select-mode');

    if (serviceGrid) {
        serviceGrid.innerHTML = '';
    }
    const actions = document.getElementById('serviceActions');
    if (actions) actions.style.display = 'none';
    const selectedCount = document.getElementById('selectedCount');
    if (selectedCount) selectedCount.textContent = '0 selected';
    
    // Remove service pagination
    const servicePagination = document.getElementById('servicePagination');
    if (servicePagination) servicePagination.innerHTML = '';
}

// ========== DOM CONTENT LOADED ==========
// ========== DOM CONTENT LOADED ==========
document.addEventListener('DOMContentLoaded', () => {
    // Skip visitor form if user logged in via face recognition
    if (window.SKIP_VISITOR_FORM === true) {
        try {
            if (window.__CLIENT_SESSION) {
                window.visitorInfo = {
                    client_id: window.__CLIENT_SESSION.client_id || 0,
                    name: window.__CLIENT_SESSION.name || '',
                    phone: window.__CLIENT_SESSION.phone || '',
                    email: window.__CLIENT_SESSION.email || ''
                };
            }
            
            // Hide kiosk container, show office section
            const kioskContainer = document.querySelector('.kiosk-container');
            const officeSection = document.getElementById('officeSection');
            
            if (kioskContainer) kioskContainer.style.display = 'none';
            
            if (officeSection) { 
                officeSection.style.display = 'block'; 
                setTimeout(() => {
                    officeSection.classList.add('active');
                    
                    // Wait for CSS to fully render before applying pagination
                    setTimeout(() => {
                        renderOfficePage();
                    }, 300); // Increased delay for CSS rendering
                }, 80); 
            }
        } catch (e) { 
            console.error('SKIP_VISITOR_FORM error:', e); 
        }
        
        // Initialize buttons
        const toggleBtn = document.getElementById('multiSelectToggle');
        if (toggleBtn) toggleBtn.addEventListener('click', (e)=>{ e.preventDefault(); toggleMultiSelect(); });

        const confirmBtn = document.getElementById('confirmBtn');
        if (confirmBtn) confirmBtn.addEventListener('click', (e)=>{ e.preventDefault(); confirmSelection(); });

        const backBtn = document.getElementById('backBtn');
        if (backBtn) backBtn.addEventListener('click', (e)=>{ e.preventDefault(); goBack(); });

        const ss = document.getElementById('serviceSection');
        if (ss) ss.style.display = 'none';
        
        return;
    }

    // Normal flow: Initialize all buttons
    const toggleBtn = document.getElementById('multiSelectToggle');
    if (toggleBtn) toggleBtn.addEventListener('click', (e)=>{ e.preventDefault(); toggleMultiSelect(); });

    const confirmBtn = document.getElementById('confirmBtn');
    if (confirmBtn) confirmBtn.addEventListener('click', (e)=>{ e.preventDefault(); confirmSelection(); });

    const backBtn = document.getElementById('backBtn');
    if (backBtn) backBtn.addEventListener('click', (e)=>{ e.preventDefault(); goBack(); });

    const ss = document.getElementById('serviceSection');
    if (ss) ss.style.display = 'none';

    // Visitor form initialization
    const visitorForm = document.getElementById('visitorForm');
    if (visitorForm) {
        // Show kiosk container with visitor form, hide office section initially
        const officeSection = document.getElementById('officeSection');
        if (officeSection) officeSection.style.display = 'none';
        
        // Attach input listeners
        ['visitorName','visitorPhone','visitorEmail'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('input', visitorSyncState, {passive:true});
        });

        // Student fields: checkbox, student number, course
        const isStudentCheckbox = document.getElementById('visitorIsStudent');
        const studentNumberField = document.getElementById('visitorStudentNumber');
        const studentNumberWrapper = document.getElementById('visitorStudentNumberField');
        const studentNumberHint = document.getElementById('visitorStudentNumberHint');
        const courseSelect = document.getElementById('visitorCourse');
        const courseWrapper = document.getElementById('visitorCourseField');

        if (isStudentCheckbox) {
            isStudentCheckbox.addEventListener('change', function() {
                const checked = !!this.checked;
                console.log('visitorIsStudent change ->', checked);
                if (studentNumberWrapper) studentNumberWrapper.style.display = checked ? '' : 'none';
                if (courseWrapper) courseWrapper.style.display = checked ? '' : 'none';
                if (studentNumberField) studentNumberField.disabled = !checked;
                if (courseSelect) courseSelect.disabled = !checked;
                visitorSyncState();
            }, {passive:true});
        }

        if (studentNumberField) {
            studentNumberField.addEventListener('input', function() {
                this.value = this.value.toUpperCase().replace(/[^0-9A-Z\-]/g, '');
                const ok = isValidStudentNumber(this.value || '');
                if (studentNumberHint) studentNumberHint.style.display = this.value === '' ? 'none' : (ok ? 'none' : 'block');
                visitorSyncState();
            }, {passive:true});
        }

        if (courseSelect) {
            courseSelect.addEventListener('change', visitorSyncState, {passive:true});
        }

        const proceedBtn = document.getElementById('visitorProceed');
        if (proceedBtn) proceedBtn.addEventListener('click', (e) => {
            e.preventDefault();
            openVisitorConfirm();
        });

        const cancelConfirm = document.getElementById('visitorCancel');
        if (cancelConfirm) cancelConfirm.addEventListener('click', (e)=>{ e.preventDefault(); closeVisitorConfirm(); });

        const confirmBtn = document.getElementById('visitorConfirm');
        if (confirmBtn) confirmBtn.addEventListener('click', (e)=>{ e.preventDefault(); acceptVisitorAndEnter(); });

        visitorSyncState();
    }
});

// Wait for window load (CSS and images fully loaded)
window.addEventListener('load', () => {
    // Initialize office pagination if offices exist
    const officeGrid = document.getElementById('officeGrid');
    if (officeGrid && officeGrid.querySelectorAll('.office-btn').length > 0) {
        // Small delay to ensure everything is rendered
        setTimeout(() => {
            renderOfficePage();
        }, 100);
    }
});

// Expose for inline office buttons
window.loadServices = loadServices;
window.toggleMultiSelect = toggleMultiSelect;
window.confirmSelection = confirmSelection;
window.goBack = goBack;
window.changePage = changePage;
window.renderOfficePage = renderOfficePage;
window.renderServicePage = renderServicePage;