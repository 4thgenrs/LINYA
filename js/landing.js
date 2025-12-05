document.addEventListener('DOMContentLoaded', () => {
  const api = '../backend/landingData.php';
  const cards = document.getElementById('statsCards');
  const chart = document.getElementById('chartBars');
  const topServices = document.getElementById('topServices');

  async function load() {
    try {
      const res = await fetch(api, { credentials: 'same-origin' });
      const json = await res.json();
      if (!json || !json.success) return;
      const t = json.totals || {};

      const defs = [
        { key: 'total_staff_accounts', label: 'Staff Accounts' },
        { key: 'total_active_windows', label: 'Active Windows' },
        { key: 'total_active_offices', label: 'Active Offices' },
        { key: 'total_pending_queues', label: 'Pending Queues' },
        { key: 'total_ongoing_queues', label: 'Ongoing Queues' },
        { key: 'total_finished_queues', label: 'Finished Queues' }
      ];

      cards.innerHTML = '';
      defs.forEach(d => {
        const c = document.createElement('div');
        c.className = 'card';
        c.innerHTML = `<div class="num">${(t[d.key] !== undefined) ? t[d.key] : '—'}</div><div class="label">${d.label}</div>`;
        cards.appendChild(c);
      });

      // chart
      chart.innerHTML = '';
      const days = json.queues_per_day || [];
      if (!days.length) {
        chart.innerHTML = '<div class="muted">No data</div>';
      } else {
        // compute max
        let max = Math.max(1, ...days.map(r => Number(r.cnt || 0)));
        // compute available pixel height based on container
        const containerHeight = Math.max(120, (chart.clientHeight || 220) - 20);

        days.forEach(r => {
          const cnt = Number(r.cnt || 0);
          const barPx = Math.round((cnt / max) * containerHeight);
          const col = document.createElement('div');
          col.className = 'chart-col';
          const bar = document.createElement('div');
          bar.className = 'chart-bar';
          bar.style.height = (barPx === 0 ? 6 : barPx) + 'px';
          // show count inside bar (hide if zero)
          const val = document.createElement('div');
          val.className = 'bar-val';
          val.textContent = cnt > 0 ? String(cnt) : '';
          bar.appendChild(val);

          const label = document.createElement('div');
          label.className = 'bar-label';
          // show MM-DD for readability
          label.textContent = (r.d && r.d.length >= 7) ? r.d.slice(5) : r.d;

          col.appendChild(bar);
          col.appendChild(label);
          chart.appendChild(col);
        });
      }

      // top services
      topServices.innerHTML = '';
      const top = json.top_services || [];
      if (!top.length) {
        topServices.innerHTML = '<li class="muted">No data</li>';
      } else {
        top.forEach(s => {
          const li = document.createElement('li');
          li.textContent = `${s.service_description} — ${s.cnt}`;
          topServices.appendChild(li);
        });
      }

    } catch (e) {
      console.error('landing load', e);
    }
  }

  load();
  setInterval(load, 5000);
});