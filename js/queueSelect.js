// Frontend logic for queue select page — externalized
(function(){
  const api = '../backend/queueSelectData.php';
  const attachApi = '../backend/queueAttach.php';

  // track current on-air queue id for this window to prevent calling next while one is active
  let currentOnAirId = null;

  function $(id){ return document.getElementById(id); }
  function qs(sel, root){ return (root||document).querySelector(sel); }

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
        fetch('../backend/logout.php', { method: 'POST', credentials:'same-origin' })
          .then(()=> { window.location.href = 'login.php'; })
          .catch(()=> { window.location.href = 'login.php'; });
      });
    }

    if (attachBtn && attachInput) {
      attachBtn.addEventListener('click', () => {
        attachInput.value = '';
        attachInput.click();
      });

      attachInput.addEventListener('change', () => {
        const file = attachInput.files[0];
        if (!file) return;
        if (file.size > 10 * 1024 * 1024) {
          attachStatus.style.display = 'block';
          attachStatus.textContent = 'File too large (max 10 MB).';
          return;
        }
        attachStatus.style.display = 'block';
        attachStatus.textContent = 'Uploading...';

        const form = new FormData();
        const qid = $('attachQueueId').value;
        if (!qid) {
          attachStatus.textContent = 'No active queue to attach to.';
          return;
        }
        form.append('queue_id', qid);
        form.append('attachment', file);

        fetch(attachApi, {
          method: 'POST',
          credentials: 'same-origin',
          body: form
        }).then(r => r.json())
          .then(json => {
            if (json && json.success) {
              attachStatus.textContent = 'Uploaded: ' + (json.file || 'attached');
            } else {
              attachStatus.textContent = 'Upload failed: ' + (json && json.error ? json.error : 'unknown');
            }
          })
          .catch(err => {
            console.error(err);
            attachStatus.textContent = 'Upload error';
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