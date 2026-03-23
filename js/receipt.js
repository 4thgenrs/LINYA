document.addEventListener('DOMContentLoaded', () => {
  const params = new URLSearchParams(window.location.search);
  const queueId = params.get('queue_id');
  const receiptPrintUrl = `../backend/receiptPrint.php?queue_id=${encodeURIComponent(queueId || '')}`;

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

  let qzSecurityConfigured = false;

  function configureQzSecurity() {
    if (!window.qz || !qz.security || qzSecurityConfigured) {
      return;
    }

    qz.security.setCertificatePromise(function(resolve, reject) {
      fetch(window.QZ_CERTIFICATE_URL, {
        cache: 'no-store',
        headers: { 'Content-Type': 'text/plain' }
      })
        .then(response => response.ok
          ? response.text()
          : response.text().then(text => Promise.reject(new Error(text || 'Failed to load QZ certificate.'))))
        .then(resolve)
        .catch(reject);
    });

    qz.security.setSignaturePromise(function(toSign) {
      return function(resolve, reject) {
        fetch(window.QZ_SIGN_URL, {
          method: 'POST',
          cache: 'no-store',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
          },
          body: `request=${encodeURIComponent(toSign)}`
        })
          .then(response => response.ok
            ? response.text()
            : response.text().then(text => Promise.reject(new Error(text || 'Failed to sign QZ request.'))))
          .then(resolve)
          .catch(reject);
      };
    });

    qzSecurityConfigured = true;
  }

  function applyQzSignatureAlgorithm() {
    if (!window.qz || !qz.security || typeof qz.security.setSignatureAlgorithm !== 'function') {
      return;
    }

    try {
      qz.security.setSignatureAlgorithm(window.QZ_SIGNATURE_ALGORITHM || 'SHA256');
    } catch (error) {
      console.warn('QZ signature algorithm could not be applied yet:', error);
    }
  }

  function getStatusNode() {
    let statusNode = document.getElementById('printStatus');

    if (!statusNode) {
      statusNode = document.createElement('div');
      statusNode.id = 'printStatus';
      statusNode.style.cssText =
        'position:fixed;top:20px;left:50%;transform:translateX(-50%);' +
        'color:white;padding:12px 24px;border-radius:8px;z-index:9999;' +
        'font-weight:600;box-shadow:0 8px 24px rgba(0,0,0,0.18);';
      document.body.appendChild(statusNode);
    }

    return statusNode;
  }

  function setPrintStatus(message, type = 'info') {
    const statusNode = getStatusNode();
    const background = type === 'error'
      ? '#dc3545'
      : type === 'success'
        ? '#198754'
        : type === 'warning'
          ? '#fd7e14'
          : '#007bff';

    statusNode.style.background = background;
    statusNode.textContent = message;
  }

  function clearPrintStatus(delay = 2500) {
    const statusNode = document.getElementById('printStatus');
    if (!statusNode) {
      return;
    }

    window.setTimeout(() => {
      const activeStatusNode = document.getElementById('printStatus');
      if (activeStatusNode) {
        activeStatusNode.remove();
      }
    }, delay);
  }

  function arrayBufferToBase64(buffer) {
    let binary = '';
    const bytes = new Uint8Array(buffer);
    const chunkSize = 0x8000;

    for (let index = 0; index < bytes.length; index += chunkSize) {
      const chunk = bytes.subarray(index, index + chunkSize);
      binary += String.fromCharCode.apply(null, chunk);
    }

    return btoa(binary);
  }

  async function fetchReceiptPdfBase64() {
    const response = await fetch(receiptPrintUrl, { cache: 'no-store' });
    if (!response.ok) {
      throw new Error('Unable to generate receipt PDF.');
    }

    const pdfBuffer = await response.arrayBuffer();
    return arrayBufferToBase64(pdfBuffer);
  }

  async function ensureQzConnection() {
    if (!window.qz) {
      throw new Error('QZ Tray JavaScript library is not loaded.');
    }

    configureQzSecurity();

    if (typeof qz.websocket.isActive === 'function' && qz.websocket.isActive()) {
      applyQzSignatureAlgorithm();
      return;
    }

    await qz.websocket.connect({ retries: 2, delay: 1 });

    if (qz.api && typeof qz.api.getVersion === 'function') {
      try {
        await qz.api.getVersion();
      } catch (error) {
        console.warn('Unable to fetch QZ Tray version:', error);
      }
    }

    applyQzSignatureAlgorithm();
  }

  async function resolvePrinterName() {
    const configuredPrinter = String(window.QZ_PRINTER_NAME || '').trim();

    if (configuredPrinter) {
      return qz.printers.find(configuredPrinter);
    }

    if (window.QZ_USE_DEFAULT_PRINTER !== false) {
      return qz.printers.getDefault();
    }

    const printers = await qz.printers.find();
    if (Array.isArray(printers) && printers.length > 0) {
      return printers[0];
    }

    throw new Error('No printer is available through QZ Tray.');
  }

  async function printViaQz(queueCode) {
    setPrintStatus('Connecting to QZ Tray...');
    await ensureQzConnection();

    const printerName = await resolvePrinterName();
    setPrintStatus(`Sending receipt to ${printerName}...`);

    const pdfBase64 = await fetchReceiptPdfBase64();
    const config = qz.configs.create(printerName, {
      jobName: `LINYA Receipt ${queueCode}`,
      colorType: 'grayscale'
    });
    const data = [{
      type: 'pixel',
      format: 'pdf',
      flavor: 'base64',
      data: pdfBase64,
      options: {
        altFontRendering: true,
        ignoreTransparency: true
      }
    }];

    await qz.print(config, data);
    return printerName;
  }

  async function printViaBrowserFallback() {
    const response = await fetch(receiptPrintUrl, { cache: 'no-store' });

    if (!response.ok) {
      throw new Error('Print failed');
    }

    const blob = await response.blob();
    const url = URL.createObjectURL(blob);

    const iframe = document.createElement('iframe');
    iframe.style.display = 'none';
    iframe.src = url;
    document.body.appendChild(iframe);

    iframe.onload = () => {
      iframe.contentWindow.print();
    };
  }

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
      // PRINT BUTTON (QZ TRAY FIRST)
      // --------------------------------------------------
      printBtn.addEventListener('click', async () => {
        try {
          setPrintStatus('Preparing receipt for QZ Tray...');
          const printerName = await printViaQz(q.queue_code);
          setPrintStatus(`Receipt sent to ${printerName}.`, 'success');
          clearPrintStatus();
        } catch (err) {
          console.error(err);

          if (window.QZ_FALLBACK_TO_BROWSER_PRINT) {
            setPrintStatus('QZ Tray unavailable. Falling back to browser print...', 'warning');

            try {
              await printViaBrowserFallback();
              clearPrintStatus(4000);
            } catch (fallbackErr) {
              console.error(fallbackErr);
              setPrintStatus(`Printing failed: ${fallbackErr.message}`, 'error');
              clearPrintStatus(5000);
            }

            return;
          }

          setPrintStatus(`QZ Tray print failed: ${err.message}`, 'error');
          clearPrintStatus(5000);
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
