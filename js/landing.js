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
      let max = 1;
      days.forEach(r => { if (r.cnt > max) max = r.cnt; });
      days.forEach(r => {
        const h = Math.round((r.cnt / max) * 100);
        const bar = document.createElement('div');
        bar.className = 'chart-bar';
        bar.style.height = (h === 0 ? 6 : (h + 20)) + '%';
        bar.title = `${r.d}: ${r.cnt}`;
        bar.innerHTML = `<div style="font-size:11px;margin-bottom:6px">${r.cnt}</div><div style="font-size:11px;transform:rotate(-90deg);display:none">${r.d}</div>`;
        chart.appendChild(bar);
      });

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