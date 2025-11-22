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
              console.log('Redirecting to: https://linya.ucc-bsit.org/LINYA/includes/kioskSelect.php');
              window.location.href = 'https://linya.ucc-bsit.org/LINYA/includes/kioskSelect.php';
            });
        } else {
          console.log('Redirecting to: https://linya.ucc-bsit.org/LINYA/includes/kioskSelect.php');
          window.location.href = 'https://linya.ucc-bsit.org/LINYA/includes/kioskSelect.php';
        }
      });

      printBtn.addEventListener('click', async () => {
        console.log('🖨️ Print Receipt clicked - starting auto-print process');
        
        // Show printing status message
        const statusMsg = document.createElement('div');
        statusMsg.id = 'printStatus';
        statusMsg.style.cssText = 'position:fixed;top:20px;left:50%;transform:translateX(-50%);background:#007bff;color:white;padding:10px 20px;border-radius:5px;z-index:9999;font-size:14px;';
        statusMsg.textContent = '🖨️ Preparing receipt for printing...';
        document.body.appendChild(statusMsg);
        
        try {
          // Fetch PDF as blob
          console.log('🖨️ Fetching PDF blob from receiptPrint.php');
          const response = await fetch(`../backend/receiptPrint.php?queue_id=${encodeURIComponent(queueId)}`);
          
          if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
          }
          
          const blob = await response.blob();
          const url = URL.createObjectURL(blob);
          console.log('🖨️ PDF blob created, setting up iframe for auto-print');
          
          // Update status
          statusMsg.textContent = '🖨️ Opening print dialog...';
          
          // Create hidden iframe for printing
          const iframe = document.createElement('iframe');
          iframe.style.cssText = 'position:absolute;width:0;height:0;border:none;visibility:hidden;';
          iframe.src = url;
          
          // Handle iframe load and trigger print
          iframe.onload = () => {
            try {
              console.log('🖨️ Iframe loaded, triggering print dialog');
              setTimeout(() => {
                try {
                  iframe.contentWindow.focus();
                  iframe.contentWindow.print();
                  console.log('🖨️ Print dialog triggered successfully');
                  
                  // Update status to show completion
                  statusMsg.textContent = '✅ Print dialog opened! Complete printing and click OK to continue.';
                  statusMsg.style.background = '#28a745';
                  
                  // Wait for print to complete, then cleanup and redirect
                  setTimeout(() => {
                    console.log('🖨️ Print process completed, cleaning up and redirecting');
                    URL.revokeObjectURL(url);
                    document.body.removeChild(iframe);
                    if (statusMsg.parentNode) statusMsg.parentNode.removeChild(statusMsg);
                    
                    // Redirect with face logout if needed (same as "Back to menu" button)
                    if (window.FACE_LOGGED) {
                      fetch('../backend/face_logout.php', { method: 'POST' })
                        .catch((err) => console.log('Logout error:', err))
                        .finally(() => {
                          console.log('🖨️ Face logout completed, redirecting to kioskSelect');
                          window.location.href = 'https://linya.ucc-bsit.org/LINYA/includes/kioskSelect.php';
                        });
                    } else {
                      console.log('🖨️ Guest user, redirecting to kioskSelect');
                      window.location.href = 'https://linya.ucc-bsit.org/LINYA/includes/kioskSelect.php';
                    }
                  }, 3000); // Give 3 seconds for print dialog interaction
                  
                } catch (printError) {
                  console.error('🖨️ ❌ Print error:', printError);
                  throw printError;
                }
              }, 100); // Small delay to ensure iframe is ready
            } catch (err) {
              console.error('🖨️ ❌ Auto-print failed:', err);
              // Fallback to opening in new tab
              console.log('🖨️ Falling back to new tab method');
              URL.revokeObjectURL(url);
              document.body.removeChild(iframe);
              window.open(`../backend/receiptPrint.php?queue_id=${encodeURIComponent(queueId)}`, '_blank');
              
              statusMsg.textContent = '⚠️ Auto-print failed - PDF opened in new tab';
              statusMsg.style.background = '#ffc107';
              setTimeout(() => {
                if (statusMsg.parentNode) statusMsg.parentNode.removeChild(statusMsg);
              }, 3000);
            }
          };
          
          // Handle iframe load error
          iframe.onerror = () => {
            console.error('🖨️ ❌ Iframe failed to load PDF');
            URL.revokeObjectURL(url);
            document.body.removeChild(iframe);
            // Fallback to new tab
            window.open(`../backend/receiptPrint.php?queue_id=${encodeURIComponent(queueId)}`, '_blank');
            
            statusMsg.textContent = '⚠️ Auto-print failed - PDF opened in new tab';
            statusMsg.style.background = '#ffc107';
            setTimeout(() => {
              if (statusMsg.parentNode) statusMsg.parentNode.removeChild(statusMsg);
            }, 3000);
          };
          
          document.body.appendChild(iframe);
          
        } catch (fetchError) {
          console.error('🖨️ ❌ Fetch error:', fetchError);
          // Fallback to new tab method
          console.log('🖨️ Falling back to new tab method due to fetch error');
          window.open(`../backend/receiptPrint.php?queue_id=${encodeURIComponent(queueId)}`, '_blank');
          
          statusMsg.textContent = '⚠️ Print request failed - PDF opened in new tab';
          statusMsg.style.background = '#dc3545';
          setTimeout(() => {
            if (statusMsg.parentNode) statusMsg.parentNode.removeChild(statusMsg);
          }, 3000);
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
