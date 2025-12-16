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

      // visitor fields
      if (q.customer_name && q.customer_name.trim().length) {
        rName.textContent = q.customer_name;
        rRowName.style.display = '';
      } else rRowName.style.display = 'none';

      if (q.customer_phone && q.customer_phone.trim().length) {
        rPhone.textContent = q.customer_phone;
        rRowPhone.style.display = '';
      } else rRowPhone.style.display = 'none';

      if (q.customer_email && q.customer_email.trim().length) {
        rEmail.textContent = q.customer_email;
        rRowEmail.style.display = '';
      } else rRowEmail.style.display = 'none';

      /* -------------------------------------------------------
         FIX: Reliable app base detection using server-provided
         window.APP_BASE. (Fallback only if missing.)
      ---------------------------------------------------------*/

      // Use APP_BASE provided by PHP, fallback to /LINYA
      let base = (typeof window.APP_BASE === 'string' && window.APP_BASE.length)
        ? window.APP_BASE
        : '/LINYA';

      // Ensure base doesn't end with slash unless it's root
      if (base !== '/' && base.endsWith('/')) {
        base = base.slice(0, -1);
      }

      const hostOverride = (window.QR_HOST_OVERRIDE && window.QR_HOST_OVERRIDE.length)
        ? window.QR_HOST_OVERRIDE
        : window.location.hostname;

      const portPart = (window.QR_PORT_OVERRIDE && window.QR_PORT_OVERRIDE.length)
        ? window.QR_PORT_OVERRIDE
        : (window.location.port ? (':' + window.location.port) : '');

      const origin = window.location.protocol + '//' + hostOverride + portPart;

      // Link directly to the Live Office Queue page for this office
      const liveUrl = `${origin}${base}/includes/officeQueue.php?office=${encodeURIComponent(q.office_id)}`;
      console.log('Live queue URL:', liveUrl);

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

      // Add this CSS to hide it
      linkEl.style.display = 'none'; // Hide the link

      // Hide QR for guest users, show for face-logged users
      if (!window.FACE_LOGGED) {
        // Guest mode — hide QR block and caption entirely
        const qrBlock = document.querySelector('.qr-block');
        if (qrBlock) qrBlock.style.display = 'none';

        // Also remove any existing liveUrlLink if present
        if (linkEl) linkEl.remove();
      } else {
        // Face-logged user — show QR with existing behavior
        const googleQr = `https://chart.googleapis.com/chart?cht=qr&chs=300x300&chl=${encodeURIComponent(liveUrl)}&chld=L|1`;
        const qrServer = `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(liveUrl)}`;
        qrImg.alt = `Open live queue for ${q.office_description}`;
        qrImg.style.display = '';
        qrImg.width = 300;
        qrImg.height = 300;
        qrImg.src = googleQr;
        qrImg.onerror = () => {
          if (qrImg.src === googleQr) {
            // try backup provider
            qrImg.onerror = null;
            qrImg.src = qrServer;
            return;
          }
          qrImg.style.display = 'none';
          let msg = document.getElementById('qrErrorMsg');
          if (!msg) {
            msg = document.createElement('div');
            msg.id = 'qrErrorMsg';
            msg.className = 'small muted';
            msg.style.marginTop = '8px';
            msg.textContent = 'QR image failed to load.';
            qrImg.parentNode.appendChild(msg);
          }
        };
      }

      confirmBtn.addEventListener('click', () => {
        console.log('Back to menu clicked, FACE_LOGGED:', window.FACE_LOGGED);
        if (window.FACE_LOGGED) {
          fetch('../backend/face_logout.php', { method: 'POST' })
            .catch((err) => console.log('Logout error:', err))
            .finally(() => {
              console.log('Redirecting to: https://linya.ucc-bsit.orgLINYA/includes/kioskSelect.php');
              window.location.href = 'kioskSelect.php';
            });
        } else {
          console.log('Redirecting to: https://linya.ucc-bsit.orgLINYA/includes/kioskSelect.php');
          window.location.href = 'kioskSelect.php';
        }
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