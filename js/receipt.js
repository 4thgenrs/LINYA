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
      if (json.error) {
        rQueue.textContent = json.error;
        return;
      }

      const q = json.queue;
      const services = json.services || [];

      rMeta.innerHTML = `<div class="small muted">${formatDateTime(q.created_at)}</div>`;
      rQueue.textContent = q.queue_code;
      rOffice.textContent = q.office_description;
      rServices.textContent =
        services.map(s => s.description + (s.soft_copy ? ' (PDF)' : '')).join(', ') || '—';

      // Visitor fields
      if (q.customer_name && q.customer_name.trim()) {
        rName.textContent = q.customer_name;
        rRowName.style.display = '';
      } else rRowName.style.display = 'none';

      if (q.customer_phone && q.customer_phone.trim()) {
        rPhone.textContent = q.customer_phone;
        rRowPhone.style.display = '';
      } else rRowPhone.style.display = 'none';

      if (q.customer_email && q.customer_email.trim()) {
        rEmail.textContent = q.customer_email;
        rRowEmail.style.display = '';
      } else rRowEmail.style.display = 'none';

      // --------------------------------------------------
      // ✅ FIXED QR: BASE64URL(JSON) FOR MOBILE APP
      // --------------------------------------------------

      const payload = {
        queue_id: Number(q.queue_id),
        queue_code: q.queue_code,
        office_id: Number(q.office_id),
        office_description: q.office_description,
        created_at: q.created_at,
        customer_name: q.customer_name || null,
        customer_phone: q.customer_phone || null,
        customer_email: q.customer_email || null
      };

      function toBase64Url(str) {
        const utf8 = new TextEncoder().encode(str);
        let binary = '';
        utf8.forEach(b => binary += String.fromCharCode(b));
        const b64 = btoa(binary);
        return b64.replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/g, '');
      }

      const payloadJson = JSON.stringify(payload);
      const payloadB64Url = toBase64Url(payloadJson);

      console.log("QR_PAYLOAD_JSON:", payloadJson);
      console.log("QR_PAYLOAD_B64URL:", payloadB64Url);

      if (!window.FACE_LOGGED) {
        const qrBlock = document.querySelector('.qr-block');
        if (qrBlock) qrBlock.style.display = 'none';
      } else {
        const googleQr =
          `https://chart.googleapis.com/chart?cht=qr&chs=300x300&chl=${encodeURIComponent(payloadB64Url)}&chld=L|1`;

        const qrServer =
          `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(payloadB64Url)}`;

        qrImg.alt = `Receipt QR for ${q.queue_code}`;
        qrImg.style.display = '';
        qrImg.width = 300;
        qrImg.height = 300;
        qrImg.src = googleQr;

        qrImg.onerror = () => {
          if (qrImg.src === googleQr) {
            qrImg.onerror = null;
            qrImg.src = qrServer;
          }
        };
      }

      // --------------------------------------------------
      // BACK TO MENU BUTTON
      // --------------------------------------------------
      confirmBtn.addEventListener('click', () => {
        if (window.FACE_LOGGED) {
          fetch('../backend/face_logout.php', { method: 'POST' })
            .finally(() => {
              window.location.href = 'kioskSelect.php';
            });
        } else {
          window.location.href = 'kioskSelect.php';
        }
      });

      // --------------------------------------------------
      // PRINT BUTTON (UNCHANGED)
      // --------------------------------------------------
      printBtn.addEventListener('click', async () => {
        const statusMsg = document.createElement('div');
        statusMsg.id = 'printStatus';
        statusMsg.style.cssText =
          'position:fixed;top:20px;left:50%;transform:translateX(-50%);' +
          'background:#007bff;color:white;padding:12px 24px;border-radius:8px;z-index:9999;';
        statusMsg.textContent = '🖨️ Preparing receipt for printing...';
        document.body.appendChild(statusMsg);

        try {
          const response =
            await fetch(`../backend/receiptPrint.php?queue_id=${encodeURIComponent(queueId)}`);

          if (!response.ok) throw new Error("Print failed");

          const blob = await response.blob();
          const url = URL.createObjectURL(blob);

          const iframe = document.createElement('iframe');
          iframe.style.display = 'none';
          iframe.src = url;
          document.body.appendChild(iframe);

          iframe.onload = () => {
            iframe.contentWindow.print();
          };

        } catch (err) {
          console.error(err);
          window.open(`../backend/receiptPrint.php?queue_id=${encodeURIComponent(queueId)}`, '_blank');
        }
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
