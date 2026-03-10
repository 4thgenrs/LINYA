// Frontend logic for queue select page — externalized
(function(){
  const api = '../backend/queueSelectData.php';
  const attachApi = '../backend/queueAttach.php';

  // track current on-air queue id for this window to prevent calling next while one is active
  let currentOnAirId = null;

  function $(id){ return document.getElementById(id); }
  function qs(sel, root){ return (root||document).querySelector(sel); }

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

  function fetchState(office, window) {
    return fetch(`${api}?action=state&office=${encodeURIComponent(office)}&window=${encodeURIComponent(window)}`, {credentials:'same-origin'})
      .then(r => r.json());
  }

  function postAction(action, office, window, extraParams = {}) {
    const params = new URLSearchParams({ action, office, window, ...extraParams });
    return fetch(`${api}?${params.toString()}`, { credentials:'same-origin' })
      .then(r => r.json());
  }

  function renderQueueList(list) {
    const container = $('queueListContainer');
    container.innerHTML = '';
    if (!list || !list.length) {
      container.innerHTML = '<div class="empty-state"><i class="fa-solid fa-inbox" style="display:block;font-size:22px;margin-bottom:8px;color:#94a3b8"></i>No pending queues</div>';
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

      container.appendChild(d);
    });
  }

  function updateOnAir(onAir) {
    const numberEl = $('number');
    const ongoingI = $('ongoingText');
    const finishedBtn = $('finished');
    const nextBtn = $('nextQueue');
    const breakBtn = $('onBreak');
    const svcContainer = $('currentServices');
    const attachQueue = $('attachQueueId');
    const attachStatus = $('attachStatus');
    attachStatus.style.display = 'none';

    if (onAir && onAir.queue_code) {
      currentOnAirId = onAir.queue_id || null; // set current on-air id
      numberEl.textContent = onAir.queue_code;
      ongoingI.textContent = 'Ongoing';
      finishedBtn.disabled = false;
      nextBtn.disabled = true; // prevent next while on-air
      breakBtn.disabled = true;

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
      attachQueue.value = onAir.queue_id;
    } else {
      currentOnAirId = null; // no on-air
      numberEl.textContent = 'Waiting';
      ongoingI.textContent = '';
      finishedBtn.disabled = true;
      nextBtn.disabled = false;
      breakBtn.disabled = false;
      svcContainer.innerHTML = '';
      attachQueue.value = '';
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

  window.finishCurrent = function(windowNumber){
    disableButtons(true);
    postAction('finish', 0, windowNumber)
      .then(json => {
        if (json && json.success) {
          loadAndRenderFromDom();
        } else {
          alert((json && json.message) ? json.message : 'Failed to finish the current queue');
          disableButtons(false);
        }
      })
      .catch(err => {
        console.error(err);
        alert('Error finishing queue. See console.');
        disableButtons(false);
      });
  };

  window.toggleBreak = function(windowNumber){
    const breakBtn = $('onBreak');
    const isBreak = breakBtn.dataset.break !== 'true';
    disableButtons(true);
    postAction('break', 0, windowNumber, { isBreak })
      .then(json => {
        if (json && json.success) {
          // reload to reflect changes
          loadAndRenderFromDom();
        } else {
          alert((json && json.message) ? json.message : 'Failed to update break status');
          disableButtons(false);
        }
      })
      .catch(err => {
        console.error(err);
        alert('Error updating break status. See console.');
        disableButtons(false);
      });
  };

  function disableButtons(state){
    ['finished','onBreak','nextQueue'].forEach(id => {
      const b = $(id);
      if (b) b.disabled = !!state;
    });
  }

  function bindUI(office, window) {
    const finishedBtn = $('finished');
    const nextBtn = $('nextQueue');
    const breakBtn = $('onBreak');
    const logoutBtn = $('logoutBtn');
    const attachBtn = $('attachBtn');
    const notifyBtn = $('notifyBtn');
    const attachInput = $('attachmentInput');
    const attachForm = $('attachForm');
    const attachStatus = $('attachStatus');

    finishedBtn.addEventListener('click', function(){ finishCurrent(window); });
    nextBtn.addEventListener('click', function(){ callNext(window, office); });
    breakBtn.addEventListener('click', function(){ toggleBreak(window); });

    if (logoutBtn) {
      logoutBtn.addEventListener('click', function(){
        showConfirm('Are you sure you want to log out?', 'Log out')
          .then(confirmed => {
            if (!confirmed) return;
            disableButtons(true);
            fetch('../backend/logout.php', { method: 'POST', credentials:'same-origin' })
              .then(r => r.json())
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
      attachBtn.addEventListener('click', () => {
        attachStatus.style.display = 'none';
        showAttachModal();
      });

      // modal elements
      const attachModal = document.getElementById('attachModal');
      const attachPreviewList = document.getElementById('attachPreviewList');
      const attachCloseBtn = document.getElementById('attachCloseBtn');
      const attachSendBtn = document.getElementById('attachSendBtn');
      const attachCancelBtn = document.getElementById('attachCancelBtn');
      const attachAddBtn = document.getElementById('attachAddBtn');
      const attachModalStatus = document.getElementById('attachModalStatus');

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
          row.style.padding = '10px 12px';
          row.style.border = '1px solid #e5e7eb';
          row.style.borderRadius = '12px';
          row.style.background = '#fff';
          row.style.boxShadow = '0 8px 18px rgba(15,23,42,0.05)';
          const left = document.createElement('div');
          left.style.display = 'flex';
          left.style.alignItems = 'center';
          left.style.gap = '10px';
          const info = document.createElement('div');
          info.innerHTML = `<strong style="font-size:13px;color:#0f172a">${f.name}</strong><div style="font-size:12px;color:#64748b">${(f.size/1024/1024).toFixed(2)} MB • ${f.type || 'unknown'}</div>`;
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
          removeBtn.innerHTML = '<i class="fa-solid fa-trash-can"></i> Remove';
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
        .then(r => r.json())
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
        // reset break button text/state (server-side break persistence not implemented)
        const breakBtn = $('onBreak');
        if (breakBtn) {
          breakBtn.dataset.break = 'false';
          breakBtn.textContent = 'Break';
        }
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