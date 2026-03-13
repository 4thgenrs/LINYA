// Modernized kiosk menu JS — student details via modal

window.multiSelectMode      = window.multiSelectMode      || false;
window.selectedServicesSet  = window.selectedServicesSet  || new Set();
window.perServiceSoft       = window.perServiceSoft       || {};
window.visitorInfo          = window.visitorInfo          || { name:'', phone:'', email:'', student_number:'', course:'' };

// ========== PAGINATION STATE ==========
window.paginationState = {
    office:  { currentPage:1, itemsPerPage:6, totalItems:0 },
    service: { currentPage:1, itemsPerPage:6, totalItems:0 }
};

function createPaginationHTML(type, currentPage, totalPages) {
    if (totalPages <= 1) return '';
    let html = '<div class="pagination-container">';
    html += `<div class="pagination-info">Page ${currentPage} of ${totalPages}</div>`;
    html += '<div class="pagination-controls">';
    html += `<button class="pagination-btn prev-btn" onclick="changePage('${type}',${currentPage-1})" ${currentPage===1?'disabled':''}>Prev</button>`;
    const maxVisible = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxVisible/2));
    let endPage   = Math.min(totalPages, startPage + maxVisible - 1);
    if (endPage - startPage < maxVisible - 1) startPage = Math.max(1, endPage - maxVisible + 1);
    if (startPage > 1) { html += `<button class="pagination-btn" onclick="changePage('${type}',1)">1</button>`; if (startPage > 2) html += '<span class="pagination-dots">...</span>'; }
    for (let i = startPage; i <= endPage; i++) html += `<button class="pagination-btn ${i===currentPage?'active':''}" onclick="changePage('${type}',${i})">${i}</button>`;
    if (endPage < totalPages) { if (endPage < totalPages-1) html += '<span class="pagination-dots">...</span>'; html += `<button class="pagination-btn" onclick="changePage('${type}',${totalPages})">${totalPages}</button>`; }
    html += `<button class="pagination-btn next-btn" onclick="changePage('${type}',${currentPage+1})" ${currentPage===totalPages?'disabled':''}>Next</button>`;
    html += '</div></div>';
    return html;
}

function paginateItems(items, page, itemsPerPage) {
    return items.slice((page-1)*itemsPerPage, page*itemsPerPage);
}

function changePage(type, newPage) {
    const state = window.paginationState[type];
    const totalPages = Math.ceil(state.totalItems / state.itemsPerPage);
    if (newPage < 1 || newPage > totalPages) return;
    state.currentPage = newPage;
    if (type === 'office') renderOfficePage();
    else renderServicePage();
    const container = type === 'office'
        ? document.querySelector('.office-section-container')
        : document.querySelector('.service-section-container');
    if (container) container.scrollIntoView({ behavior:'smooth', block:'start' });
}

// ========== NAVIGATION HELPERS ==========
function forceNavigate(url) {
    try { const f=document.createElement('form'); f.action=url; f.method='GET'; f.style.display='none'; document.body.appendChild(f); f.submit(); return; } catch(e){}
    try { window.location.assign(url); } catch(e){}
    setTimeout(()=>{ try{window.location.href=url;}catch(e){} }, 150);
    setTimeout(()=>{ try{ const a=document.createElement('a'); a.href=url; a.style.display='none'; document.body.appendChild(a); a.click(); setTimeout(()=>a.remove(),50); }catch(e){} }, 300);
    setTimeout(()=>{ try{top.location.href=url;}catch(e){} }, 450);
    setTimeout(()=>{ try{window.location.replace(url);}catch(e){} }, 600);
}

function redirectToKioskSelect() {
    try { const anchor=document.querySelector('a[href*="kioskSelect.php"]'); if(anchor&&anchor.href){forceNavigate(anchor.href);return;} } catch(e){}
    try { const parts=window.location.pathname.split('/').filter(Boolean); const root=parts.length>0?parts[0]:'LINYA'; forceNavigate(window.location.origin+'/'+root+'/includes/kioskSelect.php'); return; } catch(e){}
    forceNavigate('kioskSelect.php');
}

function showDuplicateModal(message) {
    try {
        if (document.getElementById('customDuplicateModal')) return;
        const overlay = buildSimpleModal('Unable to join queue', message, 'OK', function(){
            try{document.body.removeChild(overlay);}catch(e){}
            redirectToKioskSelect();
            setTimeout(()=>{ try{window.location.href='/LINYA/includes/kioskSelect.php';}catch(e){} }, 200);
        }, 20000);
        document.body.appendChild(overlay);
        overlay.id = 'customDuplicateModal';
    } catch(e) { try{window.location.href='/LINYA/includes/kioskSelect.php';}catch(e2){window.location.href='kioskSelect.php';} }
}

function showGenericModal(message, onOk) {
    try {
        if (document.getElementById('customGenericModal')) return;
        const overlay = buildSimpleModal('Error', message, 'OK', function(){
            try{document.body.removeChild(overlay);}catch(e){}
            try{if(typeof onOk==='function')onOk();}catch(e){}
        }, 20000);
        document.body.appendChild(overlay);
        overlay.id = 'customGenericModal';
    } catch(e) { try{if(typeof onOk==='function')onOk();}catch(e2){} }
}

function buildSimpleModal(titleText, bodyText, btnLabel, onBtn, zIndex) {
    const overlay = document.createElement('div');
    Object.assign(overlay.style, { position:'fixed', left:'0', top:'0', right:'0', bottom:'0', background:'rgba(0,0,0,0.45)', display:'flex', alignItems:'center', justifyContent:'center', zIndex:zIndex||20000 });
    const card = document.createElement('div');
    Object.assign(card.style, { background:'#fff', borderRadius:'8px', padding:'20px', maxWidth:'520px', boxShadow:'0 8px 32px rgba(0,0,0,0.3)', fontFamily:'Segoe UI,Roboto,Arial,sans-serif' });
    const t=document.createElement('h3'); t.textContent=titleText; t.style.margin='0 0 8px 0';
    const p=document.createElement('p');  p.textContent=bodyText||''; p.style.margin='0 0 18px 0'; p.style.color='#333';
    const btn=document.createElement('button'); btn.textContent=btnLabel;
    Object.assign(btn.style, { background:'#0b74de', color:'#fff', border:'none', padding:'10px 18px', borderRadius:'6px', cursor:'pointer' });
    btn.addEventListener('click', onBtn);
    card.appendChild(t); card.appendChild(p); card.appendChild(btn);
    overlay.appendChild(card);
    return overlay;
}

// ========== PAGINATION RENDERERS ==========
function renderOfficePage() {
    const grid = document.getElementById('officeGrid'); if (!grid) return;
    const allOffices = Array.from(grid.querySelectorAll('.office-btn'));
    window.paginationState.office.totalItems = allOffices.length;
    const state = window.paginationState.office;
    const totalPages = Math.ceil(state.totalItems / state.itemsPerPage);
    allOffices.forEach(btn => btn.style.display = 'none');
    paginateItems(allOffices, state.currentPage, state.itemsPerPage).forEach(btn => btn.style.display = '');
    grid.classList.add('animating'); setTimeout(()=>grid.classList.remove('animating'), 400);
    let pd = document.getElementById('officePagination');
    if (!pd) { pd=document.createElement('div'); pd.id='officePagination'; grid.parentNode.appendChild(pd); }
    pd.innerHTML = createPaginationHTML('office', state.currentPage, totalPages);
}

function renderServicePage() {
    const grid = document.getElementById('serviceGrid'); if (!grid) return;
    const allServices = Array.from(grid.querySelectorAll('.service-card'));
    window.paginationState.service.totalItems = allServices.length;
    const state = window.paginationState.service;
    const totalPages = Math.ceil(state.totalItems / state.itemsPerPage);
    allServices.forEach(c => c.style.display = 'none');
    paginateItems(allServices, state.currentPage, state.itemsPerPage).forEach(c => c.style.display = '');
    grid.classList.add('animating'); setTimeout(()=>grid.classList.remove('animating'), 400);
    let pd = document.getElementById('servicePagination');
    if (!pd) { pd=document.createElement('div'); pd.id='servicePagination'; const ss=document.getElementById('serviceSection'); if(ss)ss.appendChild(pd); }
    pd.innerHTML = createPaginationHTML('service', state.currentPage, totalPages);
}

// ========== VALIDATION HELPERS ==========
function isValidPhone(p)         { return typeof p==='string' && /^\s*09\d{9}\s*$/.test(p); }
function isValidEmail(e)         { if(!e) return false; return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(e); }
function isValidStudentNumber(s) {
    if (!s) return false;
    return /^(?:200[0-9]|201[0-9]|202[0-6])\d{4}-[A-Z]$/.test((s||'').trim().toUpperCase());
}

// ========== VISITOR FORM ==========
function visitorSyncState() {
    const name  = (document.getElementById('visitorName')  || {}).value?.trim()  || '';
    const phone = (document.getElementById('visitorPhone') || {}).value?.trim()  || '';
    const email = (document.getElementById('visitorEmail') || {}).value?.trim()  || '';
    const proceed = document.getElementById('visitorProceed');

    const phoneOk = isValidPhone(phone);
    const emailOk = email === '' ? true : isValidEmail(email);

    const phoneHint = document.getElementById('visitorPhoneHint');
    if (phoneHint) phoneHint.style.display = phone==='' ? 'none' : (phoneOk ? 'none' : 'block');
    const emailHint = document.getElementById('visitorEmailHint');
    if (emailHint) emailHint.style.display = email==='' ? 'none' : (emailOk ? 'none' : 'block');

    // Student check: read from hidden fields (filled by modal)
    const isStudent   = document.getElementById('visitorIsStudent')?.checked || false;
    const studentNum  = document.getElementById('visitorStudentNumber')?.value?.trim() || '';
    const courseVal   = document.getElementById('visitorCourse')?.value || '';
    const studentOk   = !isStudent || (isStudent && isValidStudentNumber(studentNum) && courseVal !== '');

    if (proceed) proceed.disabled = !(name.length > 0 && phoneOk && emailOk && studentOk);
}

function openVisitorConfirm() {
    const name  = document.getElementById('visitorName').value.trim();
    const phone = document.getElementById('visitorPhone').value.trim();
    const email = document.getElementById('visitorEmail').value.trim();
    document.getElementById('confirmName').textContent  = name  || '-';
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
    window.visitorInfo.name           = document.getElementById('visitorName').value.trim();
    window.visitorInfo.phone          = document.getElementById('visitorPhone').value.trim();
    window.visitorInfo.email          = document.getElementById('visitorEmail').value.trim();
    window.visitorInfo.student_number = document.getElementById('visitorStudentNumber')?.value?.trim() || '';
    window.visitorInfo.course         = document.getElementById('visitorCourse')?.value || '';

    closeVisitorConfirm();

    const kioskContainer = document.querySelector('.kiosk-container');
    const officeSection  = document.getElementById('officeSection');
    if (kioskContainer) kioskContainer.style.display = 'none';
    if (officeSection)  { officeSection.style.display='block'; setTimeout(()=>officeSection.classList.add('active'),80); }
}

// ========== STUDENT MODAL ==========
function openStudentModal() {
    const modal = document.getElementById('studentModal');
    if (modal) modal.style.display = 'flex';
}

function closeStudentModal() {
    const modal = document.getElementById('studentModal');
    if (modal) modal.style.display = 'none';
}

function confirmStudentModal() {
    const snInput  = document.getElementById('modalStudentNumber');
    const courseEl = document.getElementById('modalCourse');
    const hint     = document.getElementById('modalStudentNumberHint');
    const sn       = snInput ? snInput.value.trim().toUpperCase() : '';
    const course   = courseEl ? courseEl.value : '';

    // Validate student number
    if (!isValidStudentNumber(sn)) {
        if (hint) hint.style.display = 'block';
        if (snInput) snInput.focus();
        return;
    }
    if (hint) hint.style.display = 'none';

    // Validate course
    if (!course) {
        alert('Please select a course.');
        return;
    }

    // Save to hidden fields
    document.getElementById('visitorStudentNumber').value = sn;
    document.getElementById('visitorCourse').value        = course;

    // Show badge on form
    const badge       = document.getElementById('studentBadge');
    const badgeSN     = document.getElementById('badgeStudentNumber');
    const badgeCourse = document.getElementById('badgeCourse');
    if (badge)       badge.style.display = 'flex';
    if (badgeSN)     badgeSN.textContent     = sn;
    if (badgeCourse) badgeCourse.textContent = course;

    closeStudentModal();
    visitorSyncState();
}

function cancelStudentModal() {
    // Uncheck the checkbox & clear stored data
    const chk = document.getElementById('visitorIsStudent');
    if (chk) chk.checked = false;

    document.getElementById('visitorStudentNumber').value = '';
    document.getElementById('visitorCourse').value        = '';

    // Hide badge
    const badge = document.getElementById('studentBadge');
    if (badge) badge.style.display = 'none';

    // Reset modal inputs
    const snInput  = document.getElementById('modalStudentNumber');
    const courseEl = document.getElementById('modalCourse');
    const hint     = document.getElementById('modalStudentNumberHint');
    if (snInput)  snInput.value  = '';
    if (courseEl) courseEl.value = '';
    if (hint)     hint.style.display = 'none';

    closeStudentModal();
    visitorSyncState();
}

// ========== SERVICE SELECTION ==========
function refreshUIForMode() {
    const serviceActions = document.getElementById('serviceActions');
    const toggleBtn      = document.getElementById('multiSelectToggle');
    if (window.multiSelectMode) {
        document.body.classList.add('multi-select-mode');
        if (serviceActions) serviceActions.style.display = 'flex';
        if (toggleBtn) toggleBtn.textContent = 'Cancel';
        document.querySelectorAll('.service-checkbox').forEach(cb=>cb.style.display='');
    } else {
        document.body.classList.remove('multi-select-mode');
        if (serviceActions) serviceActions.style.display = (window.selectedServicesSet&&window.selectedServicesSet.size>0)?'flex':'none';
        if (toggleBtn) toggleBtn.textContent = 'Select multiple';
        document.querySelectorAll('.service-checkbox').forEach(cb=>cb.style.display='none');
    }
    const confirmBtn = document.getElementById('confirmBtn');
    if (confirmBtn) confirmBtn.disabled = !(window.selectedServicesSet&&window.selectedServicesSet.size>0);
}

function loadServices(officeId, officeName) {
    const officeSection          = document.getElementById('officeSection');
    const serviceSection         = document.getElementById('serviceSection');
    const serviceSectionContainer= document.querySelector('.service-section-container');
    const serviceTitle           = document.getElementById('serviceTitle');
    const serviceGrid            = document.getElementById('serviceGrid');
    if (!serviceGrid) return;

    serviceTitle && (serviceTitle.textContent = 'Services — ' + officeName);
    window.currentOfficeId      = officeId;
    window.selectedServicesSet  = new Set();
    window.perServiceSoft       = {};
    window.paginationState.service.currentPage = 1;
    refreshUIForMode();

    fetch(`../backend/kioskMenuData.php?office_id=${encodeURIComponent(officeId)}`)
        .then(r=>r.text())
        .then(html=>{
            serviceGrid.innerHTML = html;
            serviceGrid.querySelectorAll('.service-card').forEach(card=>{
                const sid    = card.getAttribute('data-service-id');
                const softCb = card.querySelector('.service-soft-checkbox');
                const hidCb  = card.querySelector('.service-checkbox');
                window.perServiceSoft[sid] = (softCb&&softCb.checked)?1:0;

                if (softCb) {
                    softCb.disabled = false;
                    softCb.addEventListener('click', ev=>ev.stopPropagation(), {passive:true});
                    softCb.addEventListener('change', ()=>{
                        window.perServiceSoft[sid] = softCb.checked?1:0;
                        if (softCb.checked && !(window.selectedServicesSet&&window.selectedServicesSet.has(sid))) {
                            if (!window.multiSelectMode) clearAllSelections();
                            window.selectedServicesSet.add(sid);
                            card.classList.add('selected');
                            if (hidCb) hidCb.checked = true;
                            const sa=document.getElementById('serviceActions'); if(sa)sa.style.display='flex';
                            refreshUIForMode(); updateSelectedCount();
                        }
                    });
                }

                card.addEventListener('click', e=>{
                    if (e.target.closest('.service-controls')||e.target.closest('.service-soft-checkbox')||e.target.closest('label.switch')) return;
                    if (window.multiSelectMode) {
                        toggleCardSelection(card, sid, hidCb, softCb);
                    } else {
                        if (!window.selectedServicesSet) window.selectedServicesSet=new Set();
                        if (window.selectedServicesSet.has(sid)) {
                            clearAllSelections();
                            const sa=document.getElementById('serviceActions'); if(sa)sa.style.display='none';
                        } else {
                            clearAllSelections();
                            window.selectedServicesSet.add(sid);
                            card.classList.add('selected');
                            if (hidCb) hidCb.checked=true;
                            const sa=document.getElementById('serviceActions'); if(sa)sa.style.display='flex';
                        }
                        refreshUIForMode(); updateSelectedCount();
                    }
                });
            });

            officeSection && officeSection.classList.remove('active');
            setTimeout(()=>{
                if (officeSection) officeSection.style.display='none';
                if (serviceSection) { serviceSection.style.display='block'; serviceSection.classList.remove('active'); }
                requestAnimationFrame(()=>{
                    if (serviceSectionContainer) serviceSectionContainer.style.display='block';
                    setTimeout(()=>{
                        if (serviceSection) serviceSection.classList.add('active');
                        setTimeout(()=>renderServicePage(), 100);
                    }, 50);
                });
            }, 300);
            refreshUIForMode(); updateSelectedCount();
        })
        .catch(err=>{ console.error(err); serviceGrid.innerHTML="<p class='muted'>Error loading services</p>"; });

    function toggleCardSelection(card, sid, hidCb, softCb) {
        if (!window.selectedServicesSet) window.selectedServicesSet=new Set();
        if (window.selectedServicesSet.has(sid)) {
            window.selectedServicesSet.delete(sid); card.classList.remove('selected');
            if (hidCb) hidCb.checked=false;
            if (softCb) { softCb.checked=false; window.perServiceSoft[sid]=0; }
        } else {
            window.selectedServicesSet.add(sid); card.classList.add('selected');
            if (hidCb) hidCb.checked=true;
        }
        refreshUIForMode(); updateSelectedCount();
    }

    function clearAllSelections() {
        const g=document.getElementById('serviceGrid'); if(!g) return;
        g.querySelectorAll('.service-card.selected').forEach(c=>c.classList.remove('selected'));
        g.querySelectorAll('.service-checkbox').forEach(cb=>cb.checked=false);
        g.querySelectorAll('.service-soft-checkbox').forEach(s=>s.checked=false);
        window.selectedServicesSet && window.selectedServicesSet.clear();
        window.perServiceSoft = {};
    }

    function updateSelectedCount() {
        const cnt = window.selectedServicesSet ? window.selectedServicesSet.size : 0;
        const el  = document.getElementById('selectedCount'); if(el) el.textContent=`${cnt} selected`;
        const cb  = document.getElementById('confirmBtn'); if(cb) cb.disabled=cnt===0;
    }
}

function toggleMultiSelect() {
    window.multiSelectMode = !window.multiSelectMode;
    if (!window.multiSelectMode) {
        window.selectedServicesSet && window.selectedServicesSet.clear();
        document.querySelectorAll('.service-card.selected').forEach(c=>c.classList.remove('selected'));
        document.querySelectorAll('.service-checkbox').forEach(cb=>{ cb.checked=false; cb.style.display='none'; });
        document.querySelectorAll('.service-soft-checkbox').forEach(cb=>cb.checked=false);
        const sc=document.getElementById('selectedCount'); if(sc)sc.textContent='0 selected';
        const cb=document.getElementById('confirmBtn'); if(cb)cb.disabled=true;
    } else {
        document.querySelectorAll('.service-checkbox').forEach(cb=>cb.style.display='');
    }
    refreshUIForMode();
}

function confirmSelection() {
    const selected = Array.from(window.selectedServicesSet||[]);
    if (!selected.length) {
        document.querySelectorAll('#serviceGrid .service-checkbox:checked').forEach(cb=>selected.push(cb.value));
    }
    if (!selected.length) { alert('No services selected'); return; }
    postQueue(selected);
}

function postQueue(services) {
    if (!services||!services.length) return;
    const officeId = window.currentOfficeId||0;
    if (!officeId) { alert('Missing office'); return; }
    const btn = document.getElementById('confirmBtn');
    if (btn) btn.disabled = true;

    const form = new FormData();
    form.append('office_id', officeId);
    services.forEach(sid=>{ form.append('services[]', sid); form.append('soft_copy[]', window.perServiceSoft&&window.perServiceSoft[sid]?1:0); });

    if (window.visitorInfo) {
        form.append('customer_name',  window.visitorInfo.name||'');
        form.append('customer_phone', window.visitorInfo.phone||'');
        form.append('customer_email', window.visitorInfo.email||'');
        if (window.visitorInfo.client_id && parseInt(window.visitorInfo.client_id)>0) form.append('client_id', window.visitorInfo.client_id);
        if (window.visitorInfo.student_number) form.append('student_number', window.visitorInfo.student_number);
        if (window.visitorInfo.course)         form.append('course',         window.visitorInfo.course);
    }

    (async function(){
        try {
            const resp  = await fetch('../backend/queueInsert.php', { method:'POST', body:form });
            const text  = await resp.text();
            let json = null;
            try { json = text ? JSON.parse(text) : null; } catch(e){}
            const code    = json&&json.error_code ? json.error_code : (json&&json.error?json.error:null);
            const message = (json&&(json.error||json.message)) ? (json.error||json.message) : (text&&text.trim()?text.trim():`HTTP ${resp.status}`);
            const isDup   = code==='duplicate_client_pending'||code==='duplicate_guest_pending'||/duplicate|already have|already an existing|already exists/i.test((message||''));

            if (resp.ok && json && json.success) { window.location.href=`../includes/receipt.php?queue_id=${encodeURIComponent(json.queue_id)}`; return; }
            if (isDup) showDuplicateModal(message);
            else showGenericModal(message, ()=>{ if(btn)btn.disabled=false; });
        } catch(err) {
            console.error('[kioskMenu] postQueue error', err);
            showGenericModal('A server error occurred. Please try again.', ()=>{ if(btn)btn.disabled=false; });
        }
    })();
}

function goBack() {
    const officeSection          = document.getElementById('officeSection');
    const serviceSection         = document.getElementById('serviceSection');
    const serviceSectionContainer= document.querySelector('.service-section-container');
    const serviceGrid            = document.getElementById('serviceGrid');

    serviceSection && serviceSection.classList.remove('active');
    setTimeout(()=>{
        if (serviceSectionContainer) serviceSectionContainer.style.display='none';
        serviceSection && (serviceSection.style.display='none');
        if (officeSection) { officeSection.style.display='block'; setTimeout(()=>officeSection.classList.add('active'),80); }
    }, 260);

    window.selectedServicesSet && window.selectedServicesSet.clear();
    window.perServiceSoft  = {};
    window.multiSelectMode = false;
    window.paginationState.service.currentPage = 1;
    document.body.classList.remove('multi-select-mode');
    if (serviceGrid) serviceGrid.innerHTML='';
    const actions = document.getElementById('serviceActions'); if(actions)actions.style.display='none';
    const sc      = document.getElementById('selectedCount');  if(sc)sc.textContent='0 selected';
    const spd     = document.getElementById('servicePagination'); if(spd)spd.innerHTML='';
}

// ========== DOM CONTENT LOADED ==========
document.addEventListener('DOMContentLoaded', ()=>{

    // --- Skip visitor form (face recognition login) ---
    if (window.SKIP_VISITOR_FORM === true) {
        try {
            if (window.__CLIENT_SESSION) {
                window.visitorInfo = {
                    client_id: window.__CLIENT_SESSION.client_id||0,
                    name:       window.__CLIENT_SESSION.name||'',
                    phone:      window.__CLIENT_SESSION.phone||'',
                    email:      window.__CLIENT_SESSION.email||''
                };
            }
            const kc = document.querySelector('.kiosk-container');
            const os = document.getElementById('officeSection');
            if (kc) kc.style.display='none';
            if (os) { os.style.display='block'; setTimeout(()=>{ os.classList.add('active'); setTimeout(()=>renderOfficePage(),300); },80); }
        } catch(e) { console.error('SKIP_VISITOR_FORM error:', e); }

        wireCoreButtons();
        const ss=document.getElementById('serviceSection'); if(ss)ss.style.display='none';
        return;
    }

    // --- Normal visitor form flow ---
    wireCoreButtons();
    const ss=document.getElementById('serviceSection'); if(ss)ss.style.display='none';

    const visitorForm = document.getElementById('visitorForm');
    if (visitorForm) {
        const officeSection = document.getElementById('officeSection');
        if (officeSection) officeSection.style.display='none';

        // Text input listeners
        ['visitorName','visitorPhone','visitorEmail'].forEach(id=>{
            const el=document.getElementById(id); if(el) el.addEventListener('input',visitorSyncState,{passive:true});
        });

        // Student number input — auto-uppercase in modal
        const modalSN = document.getElementById('modalStudentNumber');
        if (modalSN) {
            modalSN.addEventListener('input', function(){
                this.value = this.value.toUpperCase().replace(/[^0-9A-Z\-]/g,'');
                const hint = document.getElementById('modalStudentNumberHint');
                if (hint) hint.style.display = (this.value==='')?'none':(isValidStudentNumber(this.value)?'none':'block');
            }, {passive:true});
        }

        // Student checkbox → open modal
        const isStudentChk = document.getElementById('visitorIsStudent');
        if (isStudentChk) {
            isStudentChk.addEventListener('change', function(){
                if (this.checked) {
                    openStudentModal();
                } else {
                    // Clear student data and badge
                    document.getElementById('visitorStudentNumber').value = '';
                    document.getElementById('visitorCourse').value        = '';
                    const badge=document.getElementById('studentBadge'); if(badge)badge.style.display='none';
                    // Reset modal inputs too
                    const snInput=document.getElementById('modalStudentNumber'); if(snInput)snInput.value='';
                    const cEl=document.getElementById('modalCourse'); if(cEl)cEl.value='';
                    const hint=document.getElementById('modalStudentNumberHint'); if(hint)hint.style.display='none';
                    visitorSyncState();
                }
            }, {passive:true});
        }

        // Badge edit button → reopen modal
        const badgeEdit = document.getElementById('studentBadgeEdit');
        if (badgeEdit) {
            badgeEdit.addEventListener('click', function(e){
                e.preventDefault();
                // Pre-fill modal with current values
                const snInput = document.getElementById('modalStudentNumber');
                const cEl     = document.getElementById('modalCourse');
                if (snInput) snInput.value = document.getElementById('visitorStudentNumber').value||'';
                if (cEl)     cEl.value     = document.getElementById('visitorCourse').value||'';
                openStudentModal();
            });
        }

        // Student modal buttons
        const smConfirm = document.getElementById('studentModalConfirm');
        if (smConfirm) smConfirm.addEventListener('click', e=>{ e.preventDefault(); confirmStudentModal(); });

        const smCancel = document.getElementById('studentModalCancel');
        if (smCancel) smCancel.addEventListener('click', e=>{ e.preventDefault(); cancelStudentModal(); });

        // Proceed button
        const proceedBtn = document.getElementById('visitorProceed');
        if (proceedBtn) proceedBtn.addEventListener('click', e=>{ e.preventDefault(); openVisitorConfirm(); });

        // Confirm modal buttons
        const cancelConfirm  = document.getElementById('visitorCancel');
        if (cancelConfirm)  cancelConfirm.addEventListener('click',  e=>{ e.preventDefault(); closeVisitorConfirm(); });
        const confirmVisitor = document.getElementById('visitorConfirm');
        if (confirmVisitor) confirmVisitor.addEventListener('click', e=>{ e.preventDefault(); acceptVisitorAndEnter(); });

        visitorSyncState();
    }
});

// Wire multi-select, confirm, back buttons
function wireCoreButtons() {
    const toggleBtn = document.getElementById('multiSelectToggle');
    if (toggleBtn) toggleBtn.addEventListener('click', e=>{ e.preventDefault(); toggleMultiSelect(); });
    const confirmBtn = document.getElementById('confirmBtn');
    if (confirmBtn) confirmBtn.addEventListener('click', e=>{ e.preventDefault(); confirmSelection(); });
    const backBtn = document.getElementById('backBtn');
    if (backBtn) backBtn.addEventListener('click', e=>{ e.preventDefault(); goBack(); });
}

// Office pagination on window load
window.addEventListener('load', ()=>{
    const officeGrid = document.getElementById('officeGrid');
    if (officeGrid && officeGrid.querySelectorAll('.office-btn').length > 0) {
        setTimeout(()=>renderOfficePage(), 100);
    }
});

// Expose globals
window.loadServices     = loadServices;
window.toggleMultiSelect= toggleMultiSelect;
window.confirmSelection = confirmSelection;
window.goBack           = goBack;
window.changePage       = changePage;
window.renderOfficePage = renderOfficePage;
window.renderServicePage= renderServicePage;