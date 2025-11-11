document.addEventListener('DOMContentLoaded', () => {
  const params = new URLSearchParams(window.location.search);
  const queueId = params.get('queue_id');
  const rQueue = document.getElementById('rQueue');
  const rOffice = document.getElementById('rOffice');
  const rServices = document.getElementById('rServices');
  const rMeta = document.getElementById('rMeta');
  const qrImg = document.getElementById('qrImg');
  const confirmBtn = document.getElementById('confirmBtn');
  const printBtn = document.getElementById('printBtn');

  // new visitor elements
  const rName = document.getElementById('rName');
  const rPhone = document.getElementById('rPhone');
  const rEmail = document.getElementById('rEmail');
  const rRowName = document.getElementById('rRowName');
  const rRowPhone = document.getElementById('rRowPhone');
  const rRowEmail = document.getElementById('rRowEmail');

  if (!queueId) {
    rQueue.textContent = 'Missing queue id';
    return;
  }

  fetch(`../backend/receiptData.php?queue_id=${encodeURIComponent(queueId)}`)
    .then(r => r.json())
    .then(json => {
      if (json.error) { rQueue.textContent = json.error; return; }

      const q = json.queue;
      const services = json.services || [];

      rMeta.innerHTML = `<div class="small muted">${formatDateTime(q.created_at)}</div>`;
      rQueue.textContent = q.queue_code;
      rOffice.textContent = q.office_description;
      rServices.textContent = services.map(s => s.description + (s.soft_copy ? ' (PDF)' : '') ).join(', ') || '—';

      // populate visitor fields if present
      if (q.customer_name && q.customer_name.trim().length) {
        rName.textContent = q.customer_name;
        rRowName.style.display = '';
      } else {
        rRowName.style.display = 'none';
      }
      if (q.customer_phone && q.customer_phone.trim().length) {
        rPhone.textContent = q.customer_phone;
        rRowPhone.style.display = '';
      } else {
        rRowPhone.style.display = 'none';
      }
      if (q.customer_email && q.customer_email.trim().length) {
        rEmail.textContent = q.customer_email;
        rRowEmail.style.display = '';
      } else {
        rRowEmail.style.display = 'none';
      }

      // compute app base dynamically (if app sits in /LINYA this will detect it)
      const path = window.location.pathname || '/';
      const includesIndex = path.indexOf('/includes/');
      const base = includesIndex !== -1 ? path.substring(0, includesIndex) : '';

      // optional host override (set window.QR_HOST_OVERRIDE in includes/receipt.php if you need LAN IP)
      const hostOverride = (window.QR_HOST_OVERRIDE && window.QR_HOST_OVERRIDE.length) ? window.QR_HOST_OVERRIDE : window.location.hostname;
      const portPart = (window.QR_PORT_OVERRIDE && window.QR_PORT_OVERRIDE.length) ? window.QR_PORT_OVERRIDE : (window.location.port ? (':' + window.location.port) : '');
      const origin = window.location.protocol + '//' + hostOverride + portPart;

      const liveUrl = `${origin}${base}/includes/queueDisplay.php?office_id=${encodeURIComponent(q.office_id)}`;

      // Debug log and also show the URL below the QR (helps test from same device)
      console.log('Live queue URL for QR:', liveUrl);

      // show clickable link for testing
      let linkEl = document.getElementById('liveUrlLink');
      if (!linkEl) {
        linkEl = document.createElement('a');
        linkEl.id = 'liveUrlLink';
        linkEl.style.display = 'block';
        linkEl.style.marginTop = '8px';
        linkEl.style.wordBreak = 'break-all';
        qrImg.parentNode.appendChild(linkEl);
      }
      linkEl.href = liveUrl;
      linkEl.target = '_blank';
      linkEl.textContent = liveUrl;

      // build QR image URL (Google Charts)
      const qrUrl = `https://chart.googleapis.com/chart?cht=qr&chs=300x300&chl=${encodeURIComponent(liveUrl)}&chld=L|1`;
      qrImg.src = qrUrl;
      qrImg.alt = `Open live queue for ${q.office_description}`;

      // handle image load error
      qrImg.onerror = () => {
        qrImg.style.display = 'none';
        let msg = document.getElementById('qrErrorMsg');
        if (!msg) {
          msg = document.createElement('div');
          msg.id = 'qrErrorMsg';
          msg.className = 'small muted';
          msg.style.marginTop = '8px';
          msg.textContent = 'QR image failed to load. Open the link above to view the live queue.';
          qrImg.parentNode.appendChild(msg);
        }
        console.warn('QR image failed to load. QR URL:', qrUrl);
      };

      // host reachability warning
      const host = window.location.hostname;
      if (host === 'localhost' || host === '127.0.0.1') {
        console.warn('QR points to localhost. Scanning from another device will NOT reach this machine. Use LAN IP or ngrok.');
        // show small visible note
        let note = document.getElementById('qrLocalNote');
        if (!note) {
          note = document.createElement('div');
          note.id = 'qrLocalNote';
          note.className = 'small muted';
          note.style.marginTop = '6px';
          note.textContent = 'Note: This server is "localhost". QR will only work on this machine. Use LAN IP or ngrok to scan from another device.';
          qrImg.parentNode.appendChild(note);
        }
      }

      confirmBtn.addEventListener('click', () => {
        window.location.href = `${origin}${base}/includes/?page=kioskMenu`;
      });

      printBtn.addEventListener('click', () => {
        window.open(`../backend/receiptPrint.php?queue_id=${encodeURIComponent(queueId)}`, '_blank');
      });
    })
    .catch(err => {
      console.error(err);
      rQueue.textContent = 'Error loading receipt';
    });

  function formatDateTime(dt) {
    const d = new Date(dt);
    if (isNaN(d)) return dt;
    return d.toLocaleString();
  }
});