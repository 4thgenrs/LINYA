document.addEventListener('DOMContentLoaded', () => {
  const api = '../backend/landingData.php';
  const cards = document.getElementById('statsCards');
  const chart = document.getElementById('chartBars');
  const topServices = document.getElementById('topServices');
  const refreshStamp = document.getElementById('landingRefreshStamp');
  let latestDays = [];
  let resizeTimer = null;

  renderLoadingState();

  async function load() {
    try {
      const res = await fetch(api, { credentials: 'same-origin' });
      const json = await res.json();
      if (!json || !json.success) return;
      const t = json.totals || {};

      const defs = [
        { key: 'total_staff_accounts', label: 'Staff Accounts', icon: 'fa-users', note: 'Registered staff profiles' },
        { key: 'total_active_windows', label: 'Active Windows', icon: 'fa-desktop', note: 'Enabled service windows' },
        { key: 'total_active_offices', label: 'Active Offices', icon: 'fa-building', note: 'Offices currently available' },
        { key: 'total_pending_queues', label: 'Pending Queues', icon: 'fa-hourglass-half', note: 'Customers still waiting' },
        { key: 'total_ongoing_queues', label: 'Ongoing Queues', icon: 'fa-wave-square', note: 'Queues being served now' },
        { key: 'total_finished_queues', label: 'Finished Queues', icon: 'fa-check-double', note: 'Completed transactions logged' }
      ];

      cards.innerHTML = '';
      defs.forEach(d => {
        const c = document.createElement('div');
        c.className = 'card';
        c.innerHTML = `
          <div class="card-top">
            <div>
              <div class="label">${d.label}</div>
              <div class="num">${(t[d.key] !== undefined) ? t[d.key] : '—'}</div>
            </div>
            <span class="card-icon"><i class="fa-solid ${d.icon}"></i></span>
          </div>
          <div class="card-sub">${d.note}</div>
        `;
        cards.appendChild(c);
      });

      // chart
      latestDays = Array.isArray(json.queues_per_day) ? json.queues_per_day : [];
      renderChart(latestDays);

      // top services
      topServices.innerHTML = '';
      const top = json.top_services || [];
      if (!top.length) {
        topServices.innerHTML = '<li class="service-empty">No service data available yet.</li>';
      } else {
        top.forEach(s => {
          const li = document.createElement('li');
          li.className = 'service-item';
          li.innerHTML = `
            <div class="service-info">
              <div class="service-name">${escapeHtml(s.service_description || 'Unnamed service')}</div>
              <div class="service-meta">Completed queue requests</div>
            </div>
            <span class="service-count">${escapeHtml(String(s.cnt || 0))}</span>
          `;
          topServices.appendChild(li);
        });
      }

      if (refreshStamp) {
        const now = new Date();
        refreshStamp.textContent = `Last synced at ${now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' })}`;
      }

    } catch (e) {
      console.error('landing load', e);
      if (refreshStamp) {
        refreshStamp.textContent = 'Unable to refresh dashboard right now';
      }
    }
  }

  load();
  setInterval(load, 5000);
  window.addEventListener('resize', () => {
    if (!latestDays.length) return;
    clearTimeout(resizeTimer);
    resizeTimer = window.setTimeout(() => renderChart(latestDays), 120);
  });

  function renderLoadingState() {
    if (cards) {
      cards.innerHTML = '';
      for (let index = 0; index < 6; index += 1) {
        const card = document.createElement('div');
        card.className = 'card is-skeleton';
        card.innerHTML = `
          <div class="card-top">
            <div style="width: 100%; display:flex; flex-direction:column; gap:10px;">
              <div class="skeleton-line w-55"></div>
              <div class="skeleton-line w-40" style="height:28px;"></div>
            </div>
            <span class="skeleton-icon"></span>
          </div>
          <div class="skeleton-line w-85"></div>
        `;
        cards.appendChild(card);
      }
    }

    if (chart) {
      chart.innerHTML = '';
      for (let index = 0; index < 10; index += 1) {
        const col = document.createElement('div');
        col.className = 'chart-col is-skeleton';
        const bar = document.createElement('div');
        bar.className = 'skeleton-bar';
        bar.style.height = `${50 + (index % 5) * 22}px`;
        const label = document.createElement('div');
        label.className = 'skeleton-label';
        col.appendChild(bar);
        col.appendChild(label);
        chart.appendChild(col);
      }
    }

    if (topServices) {
      topServices.innerHTML = '';
      for (let index = 0; index < 4; index += 1) {
        const item = document.createElement('li');
        item.className = 'service-item is-skeleton';
        item.innerHTML = `
          <div class="service-info">
            <div class="skeleton-line w-70"></div>
            <div class="skeleton-line w-40"></div>
          </div>
          <span class="skeleton-count"></span>
        `;
        topServices.appendChild(item);
      }
    }

  }

  function renderChart(days) {
    if (!chart) return;

    chart.innerHTML = '';
    if (!days.length) {
      chart.innerHTML = '<div class="chart-empty">No chart data available yet.</div>';
      return;
    }

    const values = days.map(row => Number(row.cnt || 0));
    const max = Math.max(1, ...values);
    const total = values.reduce((sum, value) => sum + value, 0);
    const average = days.length ? total / days.length : 0;
    const peakValue = Math.max(...values);
    const isCompact = window.matchMedia('(max-width: 480px)').matches;

    chart.classList.toggle('is-compact-chart', isCompact);

    if (isCompact) {
      const averageBadge = document.createElement('div');
      averageBadge.className = 'chart-compact-average';
      averageBadge.textContent = `Average ${average.toFixed(1)} / day`;
      chart.appendChild(averageBadge);

      days.forEach(row => {
        const count = Number(row.cnt || 0);
        const percent = Math.max(6, Math.round((count / max) * 100));
        const isPeak = count === peakValue && peakValue > 0;
        const item = document.createElement('div');
        item.className = `chart-row${isPeak ? ' is-peak' : ''}`;
        item.innerHTML = `
          <div class="chart-row-head">
            <span class="chart-row-date">${escapeHtml((row.d && row.d.length >= 7) ? row.d.slice(5) : row.d || '—')}</span>
            <span class="chart-row-count">${escapeHtml(String(count))}</span>
          </div>
          <div class="chart-row-track">
            <div class="chart-row-fill" style="width:${percent}%"></div>
          </div>
        `;

        if (isPeak) {
          const tag = document.createElement('span');
          tag.className = 'chart-row-tag';
          tag.textContent = 'Peak';
          item.querySelector('.chart-row-head')?.appendChild(tag);
        }

        chart.appendChild(item);
      });
      return;
    }

    const containerHeight = Math.max(120, (chart.clientHeight || 240) - 28);
    const averageLine = document.createElement('div');
    averageLine.className = 'chart-average-line';
    averageLine.style.bottom = `${Math.max(0, Math.round((average / max) * containerHeight)) + 14}px`;
    averageLine.innerHTML = `<span class="chart-average-label">Avg ${average.toFixed(1)}</span>`;
    chart.appendChild(averageLine);

    days.forEach(row => {
      const count = Number(row.cnt || 0);
      const barPx = Math.round((count / max) * containerHeight);
      const col = document.createElement('div');
      const isPeak = count === peakValue && peakValue > 0;
      col.className = `chart-col${isPeak ? ' is-peak' : ''}`;
      const bar = document.createElement('div');
      bar.className = 'chart-bar';
      bar.style.height = (barPx === 0 ? 6 : barPx) + 'px';
      bar.title = `${row.d}: ${count} finished queue${count === 1 ? '' : 's'}`;
      const val = document.createElement('div');
      val.className = 'bar-val';
      val.textContent = count > 0 ? String(count) : '';
      bar.appendChild(val);

      if (isPeak) {
        const peakTag = document.createElement('div');
        peakTag.className = 'chart-peak-tag';
        peakTag.textContent = 'Peak';
        col.appendChild(peakTag);
      }

      const label = document.createElement('div');
      label.className = 'bar-label';
      label.textContent = (row.d && row.d.length >= 7) ? row.d.slice(5) : row.d;

      col.appendChild(bar);
      col.appendChild(label);
      chart.appendChild(col);
    });
  }

  function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, char => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char]));
  }
});