// Frontend logic for queue select page — externalized
(function(){
  const api = '../backend/queueSelectData.php';
  const attachApi = '../backend/queueAttach.php';

  // track current on-air queue id for this window to prevent calling next while one is active
  let currentOnAirId = null;
  let currentProcessStart = null; // ISO datetime string 'YYYY-MM-DD HH:MM:SS'

  function $(id){ return document.getElementById(id); }
  function qs(sel, root){ return (root||document).querySelector(sel); }
  function escapeHtml(s){ if (s === null || s === undefined) return ''; return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;'); }
  function pickField(obj, keys){ if (!obj) return null; for (const k of keys){ if (obj[k] !== undefined && obj[k] !== null && obj[k] !== '') return obj[k]; } return null; }

  // simple confirm modal that returns a Promise<boolean>
  function showConfirm(message, title = 'Confirm') {
    return new Promise(resolve => {
      const overlay = document.createElement('div');
      overlay.style.position = 'fixed';
      overlay.style.inset = '0';
      overlay.style.background = 'rgba(0,0,0,0.4)';
      overlay.style.display = 'flex';
      overlay.style.alignItems = 'center';
      overlay.style.justifyContent = 'center';
      overlay.style.zIndex = '9999';

      const dialog = document.createElement('div');
      dialog.style.width = '320px';
      dialog.style.maxWidth = '92%';
      dialog.style.background = '#fff';
      dialog.style.borderRadius = '8px';
      dialog.style.padding = '16px';
      dialog.style.boxShadow = '0 6px 20px rgba(0,0,0,0.12)';
      dialog.style.fontFamily = 'Inter, system-ui, sans-serif';

      const t = document.createElement('div');
      t.style.fontWeight = '700';
      t.style.marginBottom = '8px';
      t.textContent = title;

      const m = document.createElement('div');
      m.style.marginBottom = '14px';
      m.style.color = '#111';
      m.textContent = message;

      const btnRow = document.createElement('div');
      btnRow.style.display = 'flex';
      btnRow.style.justifyContent = 'flex-end';
      btnRow.style.gap = '8px';

      const cancelBtn = document.createElement('button');
      cancelBtn.textContent = 'Cancel';
      cancelBtn.style.background = '#f3f4f6';
      cancelBtn.style.color = '#111';
      cancelBtn.style.border = '1px solid rgba(11,18,48,0.04)';
      cancelBtn.style.padding = '8px 12px';
      cancelBtn.style.borderRadius = '8px';

      const okBtn = document.createElement('button');
      okBtn.textContent = 'OK';
      okBtn.style.background = 'var(--accent)';
      okBtn.style.color = '#fff';
      okBtn.style.padding = '8px 12px';
      okBtn.style.borderRadius = '8px';

      btnRow.appendChild(cancelBtn);
      btnRow.appendChild(okBtn);
      dialog.appendChild(t);
      dialog.appendChild(m);
      dialog.appendChild(btnRow);
      overlay.appendChild(dialog);
      document.body.appendChild(overlay);

      function clean(result) {
        try { document.body.removeChild(overlay); } catch (e) {}
        resolve(!!result);
      }

      cancelBtn.addEventListener('click', () => clean(false));
      okBtn.addEventListener('click', () => clean(true));
      overlay.addEventListener('click', (ev) => {
        if (ev.target === overlay) clean(false);
      });
      // keyboard
      function onKey(e) {
        if (e.key === 'Escape') { clean(false); }
        if (e.key === 'Enter') { clean(true); }
      }
      window.addEventListener('keydown', onKey, { once: true });
    });
  }

  async function fetchState(office, window) {
    try {
      const res = await fetch(`${api}?action=state&office=${encodeURIComponent(office)}&window=${encodeURIComponent(window)}`, {credentials:'same-origin'});
      return await parseResponse(res);
    } catch (e) {
      console.error('fetchState network error', e);
      return { success: false, message: 'Network error' };
    }
  }

  // safe response parser: always return an object and include raw text on parse errors
  async function parseResponse(res) {
    try {
      const text = await res.text();
      const trimmed = (text || '').trim();
      // quick HTML detection - server returned markup (likely PHP warning/HTML)
      if (trimmed && trimmed[0] === '<') {
        console.error('parseResponse: server returned HTML instead of JSON', trimmed.slice(0,200));
        // collect some header info for debugging
        const headers = {};
        try { for (const h of res.headers.entries()) headers[h[0]] = h[1]; } catch(e){}
        return { success: false, message: 'Invalid JSON (HTML response)', raw: text, status: res.status, headers };
      }
      try {
        return JSON.parse(text);
      } catch (e) {
        console.error('parseResponse: invalid JSON', text);
        return { success: false, message: 'Invalid JSON from server', raw: text, status: res.status };
      }
    } catch (e) {
      console.error('parseResponse network error', e);
      return { success: false, message: 'Network error' };
    }
  }

  async function postAction(action, office, window, extraParams = {}) {
    try {
      const params = new URLSearchParams({ action, office, window, ...extraParams });
      const res = await fetch(`${api}?${params.toString()}`, { credentials:'same-origin' });
      return await parseResponse(res);
    } catch (e) {
      console.error('postAction network error', e);
      return { success: false, message: 'Network error' };
    }
  }

  function renderQueueList(list) {
    const container = $('queueListContainer');
    container.innerHTML = '';
    if (!list || !list.length) {
      container.innerHTML = '<div class="queue-item small muted">No pending queues</div>';
      return;
    }
    list.forEach(q => {
      const d = document.createElement('div');
      d.className = 'queue-item';
      const code = document.createElement('div');
      code.textContent = q.queue_code;
      code.className = 'queue-code';
      d.appendChild(code);

      // services
      if (q.services && q.services.length) {
        const svcWrap = document.createElement('div');
        svcWrap.className = 'queue-services';
        q.services.forEach(s => {
          const span = document.createElement('span');
          span.className = 'svc-chip';
          span.textContent = s.service_description;
          if (s.soft_copy && (s.soft_copy === 1 || s.soft_copy === true)) {
            const badge = document.createElement('small');
            badge.className = 'soft-badge';
            badge.textContent = 'PDF';
            badge.title = 'Soft copy available';
            span.appendChild(badge);
          }
          svcWrap.appendChild(span);
        });
        d.appendChild(svcWrap);
      }

      // optional customer info
      const infoLines = [];
      if (q.customer_name || q.name) infoLines.push(q.customer_name || q.name);
      if (q.customer_phone || q.phone) infoLines.push(q.customer_phone || q.phone);
      if (q.customer_email || q.email) infoLines.push(q.customer_email || q.email);
      const studentVal = q.student_number || q.studentNumber || null;
      const courseVal = q.course || q.Course || null;
      if (studentVal) infoLines.push('Student: ' + studentVal);
      if (courseVal) infoLines.push('Course: ' + courseVal);
      if (infoLines.length) {
        const info = document.createElement('div');
        info.className = 'queue-info small muted';
        info.textContent = infoLines.join(' • ');
        d.appendChild(info);
      }

      container.appendChild(d);
    });
  }

  function updateOnAir(onAir) {
    const numberEl = $('number');
    const ongoingI = $('ongoingText');
    const startBtn = $('startProcessing');
    const nextBtn = $('nextQueue');
    const svcContainer = $('currentServices');
    const attachQueue = $('attachQueueId');
    const attachStatus = $('attachStatus');
    attachStatus.style.display = 'none';

    if (onAir && (onAir.queue_code || onAir.queue_id || onAir.queue_no)) {
      currentOnAirId = onAir.queue_id || onAir.queue_no || null; // set current on-air id (fallback to queue_no when queue_id missing)
      numberEl.textContent = onAir.queue_code || (onAir.queue_no || ('#' + (onAir.queue_id || '')));
      ongoingI.textContent = 'Ongoing';
      if (startBtn) startBtn.disabled = false;
      if (nextBtn) nextBtn.disabled = true; // prevent next while on-air
      // break control removed

      // show services as chips
      svcContainer.innerHTML = '';
      if (onAir.services && onAir.services.length) {
        onAir.services.forEach(s => {
          const chip = document.createElement('span');
          chip.className = 'svc-chip large';
          chip.textContent = s.service_description;
          if (s.soft_copy && (s.soft_copy === 1 || s.soft_copy === true)) {
            const badge = document.createElement('small');
            badge.className = 'soft-badge';
            badge.textContent = 'PDF';
            badge.title = 'Soft copy available';
            chip.appendChild(badge);
          }
          svcContainer.appendChild(chip);
        });
      }
      // set attach queue id
      attachQueue.value = onAir.queue_id || onAir.queue_no || '';
      // enable view details button when on-air
      const viewBtn = $('viewDetailsBtn');
      if (viewBtn) {
        viewBtn.disabled = false;
        viewBtn.dataset.queue = onAir.queue_id || onAir.queue_code || onAir.queue_no || '';
      }
    } else {
      currentOnAirId = null; // no on-air
      numberEl.textContent = 'Standby';
      ongoingI.textContent = '';

      // Rebuild default controls (Start Processing + Call Next) so UI resets after finish/void
      const rootEl = document.getElementById('boxRoot');
      const officeAttr = rootEl ? parseInt(rootEl.getAttribute('data-office'),10) || 1 : 1;
      const windowAttr = rootEl ? parseInt(rootEl.getAttribute('data-window'),10) || 1 : 1;
      const controlsEl = document.querySelector('.controls');
      if (controlsEl) {
        controlsEl.innerHTML = '';
        const startBtnNew = document.createElement('button');
        startBtnNew.id = 'startProcessing';
        startBtnNew.type = 'button';
        startBtnNew.textContent = 'Start Processing';
        startBtnNew.disabled = true;

        const nextBtnNew = document.createElement('button');
        nextBtnNew.id = 'nextQueue';
        nextBtnNew.type = 'button';
        nextBtnNew.textContent = 'Call Next';

        controlsEl.appendChild(startBtnNew);
        controlsEl.appendChild(nextBtnNew);

        startBtnNew.addEventListener('click', () => startProcessing(windowAttr));
        nextBtnNew.addEventListener('click', () => callNext(windowAttr, officeAttr));
      }

      // clear services and attach fields
      svcContainer.innerHTML = '';
      attachQueue.value = '';
      const viewBtn = $('viewDetailsBtn');
      if (viewBtn) {
        viewBtn.disabled = true;
        delete viewBtn.dataset.queue;
      }
    }
  }

  // public actions
  window.callNext = function(windowNumber, officeNumber){
    // client-side guard: if there's an active queue on this window, block calling next
    if (currentOnAirId) {
      alert('Finish the current queue before calling the next one.');
      return;
    }

    disableButtons(true);
    postAction('next', officeNumber, windowNumber)
      .then(json => {
        if (json && json.success) {
          // update UI and reload state
          loadAndRender(officeNumber, windowNumber, true);
        } else {
          alert((json && json.message) ? json.message : 'No next queue available');
          disableButtons(false);
        }
      })
      .catch(err => {
        console.error(err);
        alert('Error calling next queue. See console.');
        disableButtons(false);
      });
  };

  // Start processing: record process_start and show Finish / Void buttons
  window.startProcessing = function(windowNumber){
    // record start timestamp in local variable (server will accept it on finalize)
    const d = new Date();
    // format to 'YYYY-MM-DD HH:MM:SS'
    const iso = d.toISOString().slice(0,19).replace('T',' ');
    currentProcessStart = iso;

    // inform server that processing has started so public displays can update
    try {
      const root = document.getElementById('boxRoot');
      const office = root ? parseInt(root.getAttribute('data-office'),10) || 0 : 0;
      postAction('start', office, windowNumber, { process_start: iso })
        .then(json => {
          if (!json || !json.success) {
            console.warn('startProcessing: server did not confirm processing start', json && json.message);
          }
        })
        .catch(e => console.error('startProcessing error', e));
    } catch (e) {
      console.error('startProcessing announce error', e);
    }

    // Replace controls with Finish and Void buttons (keep Call Next)
    const controls = document.querySelector('.controls');
    if (!controls) return;
    controls.innerHTML = '';

    const finishBtn = document.createElement('button');
    finishBtn.id = 'finishNow';
    finishBtn.type = 'button';
    finishBtn.textContent = 'Finish';
    finishBtn.style.background = '#d32f2f';
    finishBtn.style.color = '#fff';

    const voidBtn = document.createElement('button');
    voidBtn.id = 'voidNow';
    voidBtn.type = 'button';
    voidBtn.textContent = 'Void';
    voidBtn.className = 'btn-ghost';

    const nextBtn = document.createElement('button');
    nextBtn.id = 'nextQueue';
    nextBtn.type = 'button';
    nextBtn.textContent = 'Call Next';

    controls.appendChild(finishBtn);
    controls.appendChild(voidBtn);
    controls.appendChild(nextBtn);

    finishBtn.addEventListener('click', () => finalizeProcessing(windowNumber, false));
    voidBtn.addEventListener('click', () => finalizeProcessing(windowNumber, true));
    nextBtn.addEventListener('click', () => callNext(windowNumber, parseInt(document.getElementById('boxRoot').dataset.office,10)||1));
  };

  // Show remarks modal and send finalize request
  function showRemarksModal(promptText){
    return new Promise(resolve => {
      const overlay = document.createElement('div');
      overlay.style.position = 'fixed'; overlay.style.inset = '0'; overlay.style.background = 'rgba(0,0,0,0.45)'; overlay.style.display = 'flex'; overlay.style.alignItems = 'center'; overlay.style.justifyContent = 'center'; overlay.style.zIndex = '9999';
      const box = document.createElement('div'); box.style.background = '#fff'; box.style.padding = '14px'; box.style.borderRadius = '8px'; box.style.width = '420px'; box.style.maxWidth = '92%';
      const title = document.createElement('div'); title.textContent = 'Remarks'; title.style.fontWeight = '700'; title.style.marginBottom = '8px';
      const p = document.createElement('div'); p.textContent = promptText || ''; p.style.marginBottom = '8px';
      const ta = document.createElement('textarea'); ta.style.width = '100%'; ta.style.height = '90px'; ta.style.marginBottom = '8px';
      const row = document.createElement('div'); row.style.display = 'flex'; row.style.justifyContent = 'flex-end'; row.style.gap = '8px';
      const cancel = document.createElement('button'); cancel.textContent = 'Cancel'; cancel.className = 'btn-ghost';
      const ok = document.createElement('button'); ok.textContent = 'Submit'; ok.style.background = 'var(--accent)'; ok.style.color = '#fff';
      row.appendChild(cancel); row.appendChild(ok);
      box.appendChild(title); box.appendChild(p); box.appendChild(ta); box.appendChild(row);
      overlay.appendChild(box); document.body.appendChild(overlay);

      cancel.addEventListener('click', () => { try{ document.body.removeChild(overlay);}catch(e){} resolve(null); });
      ok.addEventListener('click', () => { const v = ta.value.trim(); try{ document.body.removeChild(overlay);}catch(e){} resolve(v); });
    });
  }

  async function finalizeProcessing(windowNumber, isVoid){
    disableButtons(true);
    try {
      const process_finished = (new Date()).toISOString().slice(0,19).replace('T',' ');
      let processing_duration = null;
      if (currentProcessStart) {
        const s = Date.parse(currentProcessStart.replace(' ', 'T'));
        const f = Date.parse(process_finished.replace(' ', 'T'));
        const diff = Math.max(0, Math.floor((f - s) / 1000));
        const hh = String(Math.floor(diff/3600)).padStart(2,'0');
        const mm = String(Math.floor((diff%3600)/60)).padStart(2,'0');
        const ss = String(diff%60).padStart(2,'0');
        processing_duration = `${hh}:${mm}:${ss}`;
      }

      const remarks = await showRemarksModal('Enter remarks for this queue (optional)');
      if (remarks === null) { disableButtons(false); return; }

      const params = {
        process_start: currentProcessStart,
        process_finished: process_finished,
        processing_duration: processing_duration,
        remarks: remarks,
        isVoid: isVoid ? 1 : 0
      };

      const json = await postAction('finish', 0, windowNumber, params);
      if (json && json.success) {
        currentProcessStart = null;
        loadAndRenderFromDom();
      } else {
        alert((json && json.message) ? json.message : 'Failed to archive processing result');
        disableButtons(false);
      }
    } catch (e) {
      console.error('finalizeProcessing error', e);
      alert('Error finalizing processing. See console.');
      disableButtons(false);
    }
  }

  function disableButtons(state){
    ['startProcessing','nextQueue','finishNow','voidNow'].forEach(id => {
      const b = $(id);
      if (b) b.disabled = !!state;
    });
  }

  function bindUI(office, window) {
    const startBtn = $('startProcessing');
    const nextBtn = $('nextQueue');
    const logoutBtn = $('logoutBtn');
    const attachBtn = $('attachBtn');
    const notifyBtn = $('notifyBtn');
    const attachInput = $('attachmentInput');
    const attachForm = $('attachForm');
    const attachStatus = $('attachStatus');

    if (startBtn) startBtn.addEventListener('click', function(){ startProcessing(window); });
    if (nextBtn) nextBtn.addEventListener('click', function(){ callNext(window, office); });

    if (logoutBtn) {
      logoutBtn.addEventListener('click', function(){
        showConfirm('Are you sure you want to log out?', 'Log out')
          .then(confirmed => {
            if (!confirmed) return;
            disableButtons(true);
              fetch('../backend/logout.php', { method: 'POST', credentials:'same-origin' })
              .then(r => parseResponse(r))
              .then(json => {
                if (json && json.success) {
                  // load the login form HTML and replace the current document without a full page refresh
                  // this will execute scripts/styles referenced by the login page
                  fetch('login.php', { credentials: 'same-origin' })
                    .then(r => r.text())
                    .then(html => {
                      document.open();
                      document.write(html);
                      document.close();
                    })
                    .catch(err => {
                      console.error('Failed to load login form', err);
                      alert('Logged out but failed to load login form. Please navigate to the login page.');
                    });
                } else {
                  console.error('Logout failed response', json);
                  alert('Logout failed. Please try again.');
                  disableButtons(false);
                }
              })
              .catch(err => {
                console.error('Logout failed', err);
                alert('Logout failed. Please try again.');
                disableButtons(false);
              });
          });
      });
    }

    if (attachBtn && attachInput) {
      // modal elements
      const attachModal = document.getElementById('attachModal');
      const attachPreviewList = document.getElementById('attachPreviewList');
      const attachCloseBtn = document.getElementById('attachCloseBtn');
      const attachSendBtn = document.getElementById('attachSendBtn');
      const attachCancelBtn = document.getElementById('attachCancelBtn');
      const attachAddBtn = document.getElementById('attachAddBtn');
      const attachModalStatus = document.getElementById('attachModalStatus');

      if (!attachModal || !attachPreviewList || !attachCloseBtn || !attachSendBtn || !attachCancelBtn || !attachAddBtn || !attachModalStatus) {
        console.error('Attachment modal elements are missing from the page.');
        return;
      }

      function showAttachModal() {
        attachPreviewList.innerHTML = '';
        attachModalStatus.textContent = '';
        const files = Array.from(attachInput.files || []);
        if (!files.length) {
          attachModalStatus.textContent = 'No files selected.';
        }
        files.forEach(f => {
          const row = document.createElement('div');
          row.style.display = 'flex';
          row.style.alignItems = 'center';
          row.style.justifyContent = 'space-between';
          row.style.padding = '6px';
          row.style.borderBottom = '1px solid #f3f3f3';
          const left = document.createElement('div');
          left.style.display = 'flex';
          left.style.alignItems = 'center';
          left.style.gap = '10px';
          const info = document.createElement('div');
          info.innerHTML = `<strong style="font-size:13px">${f.name}</strong><div style="font-size:12px;color:#666">${(f.size/1024/1024).toFixed(2)} MB • ${f.type || 'unknown'}</div>`;
          left.appendChild(info);
          // thumbnail for images
          if (f.type && f.type.startsWith('image/')) {
            const img = document.createElement('img');
            img.style.width = '64px';
            img.style.height = '48px';
            img.style.objectFit = 'cover';
            img.style.borderRadius = '6px';
            const reader = new FileReader();
            reader.onload = e => { img.src = e.target.result; };
            reader.readAsDataURL(f);
            left.insertBefore(img, info);
          }
          const removeBtn = document.createElement('button');
          removeBtn.className = 'btn-ghost';
          removeBtn.textContent = 'Remove';
          removeBtn.addEventListener('click', () => {
            // remove file from FileList by rebuilding list (cannot modify FileList directly)
            const newFiles = Array.from(attachInput.files).filter(x => x !== f);
            const dt = new DataTransfer();
            newFiles.forEach(nf => dt.items.add(nf));
            attachInput.files = dt.files;
            showAttachModal();
          });
          row.appendChild(left);
          row.appendChild(removeBtn);
          attachPreviewList.appendChild(row);
        });

        attachModal.style.display = 'flex';
        attachModal.setAttribute('aria-hidden', 'false');
      }

      attachBtn.addEventListener('click', () => {
        showAttachModal();
      });

      // Add extra files: open ephemeral file picker, validate and merge into attachInput.files
      if (attachAddBtn) {
        attachAddBtn.addEventListener('click', () => {
          const tmp = document.createElement('input');
          tmp.type = 'file';
          tmp.accept = '.pdf,image/*';
          tmp.multiple = true;
          tmp.style.display = 'none';
          document.body.appendChild(tmp);
          tmp.addEventListener('change', () => {
            const newFiles = Array.from(tmp.files || []);
            const maxBytes = 10 * 1024 * 1024;
            const allowedExt = ['pdf','jpg','jpeg','png','gif','webp','bmp'];
            for (const f of newFiles) {
              if (f.size > maxBytes) {
                attachModalStatus.textContent = `File too large: ${f.name} (max 10 MB each).`;
                document.body.removeChild(tmp);
                return;
              }
              const ext = (f.name.split('.').pop() || '').toLowerCase();
              if (!allowedExt.includes(ext)) {
                attachModalStatus.textContent = `File type not allowed: ${f.name}`;
                document.body.removeChild(tmp);
                return;
              }
            }
            // merge files into existing FileList
            const dt = new DataTransfer();
            Array.from(attachInput.files || []).forEach(f => dt.items.add(f));
            newFiles.forEach(f => dt.items.add(f));
            attachInput.files = dt.files;
            // refresh preview
            showAttachModal();
            document.body.removeChild(tmp);
          });
          tmp.click();
        });
      }

      attachInput.addEventListener('change', () => {
        // validate sizes client-side before preview
        const maxBytes = 10 * 1024 * 1024;
        const files = Array.from(attachInput.files || []);
        const invalid = files.find(f => f.size > maxBytes);
        if (invalid) {
          attachStatus.style.display = 'block';
          attachStatus.textContent = `File too large: ${invalid.name} (max 10 MB each).`;
          return;
        }
        // show modal preview
        showAttachModal();
      });

      // Back / Cancel both dismiss; Back keeps input intact, Cancel clears selection
      attachCloseBtn.addEventListener('click', () => {
        attachModal.style.display = 'none';
        attachModal.setAttribute('aria-hidden', 'true');
      });
      attachCancelBtn.addEventListener('click', () => {
        attachInput.value = '';
        attachModal.style.display = 'none';
        attachModal.setAttribute('aria-hidden', 'true');
        attachStatus.style.display = 'none';
      });

      attachSendBtn.addEventListener('click', () => {
        const files = Array.from(attachInput.files || []);
        if (!files.length) {
          attachModalStatus.textContent = 'No files to send.';
          return;
        }
        const qid = $('attachQueueId').value;
        if (!qid) {
          attachModalStatus.textContent = 'No active queue to attach to.';
          return;
        }
        attachSendBtn.disabled = true;
        attachModalStatus.textContent = 'Sending...';

        const form = new FormData();
        form.append('queue_id', qid);
        files.forEach((f, idx) => {
          // append as attachments[] so backend receives an array
          form.append('attachments[]', f, f.name);
        });

        fetch(attachApi, {
          method: 'POST',
          credentials: 'same-origin',
          body: form
        })
        .then(r => parseResponse(r))
        .then(json => {
          if (json && json.success) {
            attachModalStatus.textContent = 'Sent: ' + (json.files ? json.files.join(', ') : 'attached');
            attachStatus.style.display = 'block';
            attachStatus.textContent = 'Sent: ' + (json.files ? json.files.join(', ') : 'attached');
            // clear input and close modal after short delay
            setTimeout(() => {
              attachInput.value = '';
              attachModal.style.display = 'none';
              attachModal.setAttribute('aria-hidden', 'true');
            }, 900);
          } else {
            attachModalStatus.textContent = 'Send failed: ' + (json && json.error ? json.error : 'unknown');
          }
        })
        .catch(err => {
          console.error(err);
          attachModalStatus.textContent = 'Send error';
        })
        .finally(() => {
          attachSendBtn.disabled = false;
        });
      });
    }

    // View Details modal: fetch and display the current on-air queue details
    const viewBtn = $('viewDetailsBtn');
    const detailsModal = document.getElementById('queueDetailsModal');
    const detailsContent = document.getElementById('queueDetailsContent');
    const detailsClose = document.getElementById('detailsCloseBtn');

    if (viewBtn) {
      viewBtn.addEventListener('click', async () => {
        const q = viewBtn.dataset.queue;
        if (!q) return;
        viewBtn.disabled = true;
        try {
          const res = await fetch(`${api}?action=details&queue=${encodeURIComponent(q)}`, { credentials: 'same-origin' });
          const json = await parseResponse(res);
          if (!json || !json.success) {
            detailsContent.innerHTML = `<div class="small muted">Failed to load details: ${escapeHtml((json && json.message) || 'unknown')}</div>`;
          } else {
            const d = json.data || {};
            let html = '';
            html += `<div style="margin-bottom:10px"><strong>Queue</strong>: ${escapeHtml(d.queue_code||d.queue_no||d.queue_id||'')}</div>`;
            if (d.status) html += `<div style="margin-bottom:8px"><strong>Status</strong>: ${escapeHtml(d.status)}</div>`;
            if (d.services && d.services.length) {
              html += '<div style="margin-bottom:8px"><strong>Services</strong>:<ul>';
              d.services.forEach(s=>{ html += '<li>' + escapeHtml(s.service_description || s) + ((s.soft_copy) ? ' <small>(PDF)</small>' : '') + '</li>'; });
              html += '</ul></div>';
            }
            if (d.client_id || d.customer_name || d.name || (d.client && (d.client.name || d.client.student_number) ) || d.customer_phone || d.customer_email) {
              html += '<div style="margin-bottom:8px"><strong>Customer</strong>:';
              const custName = pickField(d, ['customer_name','name']) || (d.client ? pickField(d.client, ['name']) : null) || '(unknown)';
              html += `<div>${escapeHtml(custName)}</div>`;
              const phone = pickField(d, ['customer_phone','phone']) || (d.client ? pickField(d.client, ['phone']) : null);
              const email = pickField(d, ['customer_email','email']) || (d.client ? pickField(d.client, ['email']) : null);
              if (phone) html += `<div>Phone: ${escapeHtml(phone)}</div>`;
              if (email) html += `<div>Email: ${escapeHtml(email)}</div>`;
              const studentVal = pickField(d, ['student_number','studentNumber','StudentNumber']) || (d.client ? pickField(d.client, ['student_number','studentNumber']) : null);
              const courseVal = pickField(d, ['course','Course']) || (d.client ? pickField(d.client, ['course','Course']) : null);
              html += `<div>Student #: ${escapeHtml(studentVal === null || studentVal === undefined || studentVal === '' ? 'Not provided' : studentVal)}</div>`;
              html += `<div>Course: ${escapeHtml(courseVal === null || courseVal === undefined || courseVal === '' ? 'Not provided' : courseVal)}</div>`;
              html += '</div>';
            }
            // fallback: raw JSON view for debugging if empty
            if (!html) html = `<pre>${escapeHtml(JSON.stringify(d, null, 2))}</pre>`;
            detailsContent.innerHTML = html;
          }
        } catch (e) {
          console.error(e);
          detailsContent.innerHTML = '<div class="small muted">Error loading details</div>';
        } finally {
          viewBtn.disabled = false;
          if (detailsModal) { detailsModal.style.display = 'flex'; detailsModal.setAttribute('aria-hidden','false'); }
        }
      });
      if (detailsClose) detailsClose.addEventListener('click', ()=>{ if (detailsModal){ detailsModal.style.display='none'; detailsModal.setAttribute('aria-hidden','true'); } });
      if (detailsModal) detailsModal.addEventListener('click', (ev)=>{ if (ev.target === detailsModal){ detailsModal.style.display='none'; detailsModal.setAttribute('aria-hidden','true'); } });
    }

    if (notifyBtn) {
      // no-op as requested; keep button UI only
      notifyBtn.addEventListener('click', () => {
        // intentionally left empty
      });
    }
  }

  function loadAndRender(office, window, suppressReload){
    fetchState(office, window)
      .then(json => {
        if (!json || !json.success) {
          console.error('Invalid state response', json);
          return;
        }
        const data = json.data || {};
        renderQueueList(data.queue_list || []);
        updateOnAir(data.on_air || null);
        // no break button in this UI
      })
      .catch(err => {
        console.error('Failed to load state', err);
      })
      .finally(() => {
        disableButtons(false);
      });
  }

  function loadAndRenderFromDom(){
    const root = document.getElementById('boxRoot');
    const office = parseInt(root.getAttribute('data-office'),10) || 1;
    const window = parseInt(root.getAttribute('data-window'),10) || 1;
    loadAndRender(office, window);
  }

  // initialize on DOM ready
  document.addEventListener('DOMContentLoaded', function(){
    const root = document.getElementById('boxRoot');
    const office = parseInt(root.getAttribute('data-office'),10) || 1;
    const window = parseInt(root.getAttribute('data-window'),10) || 1;
    bindUI(office, window);
    loadAndRender(office, window);
    // poll queue state every 6s to refresh list
    setInterval(() => loadAndRender(office, window), 6000);
  });

})();