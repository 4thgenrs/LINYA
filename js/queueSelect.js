// Frontend logic for queue select page — externalized
(function(){
  const api = '../backend/queueSelectData.php';

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
      d.textContent = q.queue_code;
      container.appendChild(d);
    });
  }

  function updateOnAir(onAir) {
    const numberEl = $('number');
    const ongoingI = $('ongoingText');
    const finishedBtn = $('finished');
    const nextBtn = $('nextQueue');
    const breakBtn = $('onBreak');

    if (onAir && onAir.queue_code) {
      numberEl.textContent = onAir.queue_code;
      ongoingI.textContent = 'Ongoing';
      finishedBtn.disabled = false;
      nextBtn.disabled = true;
      breakBtn.disabled = true;
    } else {
      numberEl.textContent = 'Waiting';
      ongoingI.textContent = '';
      finishedBtn.disabled = true;
      nextBtn.disabled = false;
      breakBtn.disabled = false;
    }
  }

  // public actions
  window.callNext = function(windowNumber, officeNumber){
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
        // reflect break button state (if you want server-side state to carry break flag, extend API)
        const breakBtn = $('onBreak');
        if (breakBtn) {
          breakBtn.dataset.break = 'false';
          breakBtn.textContent = 'Break';
        }
        if (!suppressReload) {
          // optional auto-refresh after actions (short delay)
          // location.reload(); // not necessary — we update UI client-side
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
    // optional: poll queue state every 6s to refresh list
    setInterval(() => loadAndRender(office, window), 6000);
  });

})();