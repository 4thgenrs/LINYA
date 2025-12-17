<?php
include '../linya_db/config.php';
session_start();
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Receipt</title>
  <link rel="stylesheet" href="../css/receipt.css">
  <meta name="viewport" content="width=device-width,initial-scale=1">
</head>

<body>
  <main class="receipt-wrap" id="receiptWrap">
    <section class="receipt-card" id="receiptCard">
      <header class="r-header">
        <div class="brand">
          <div class="brand-logo">
            <img src="../resources/linya_logo.png" alt="LINYA Logo" class="logo-img">
            <div class="brand-text">
              <div class="brand-name">LINYA</div>
              <div class="brand-sub">Queue Receipt</div>
            </div>
          </div>
        </div>
        <div class="r-meta" id="rMeta"></div>
      </header>

      <div class="r-body" id="rBody">

        <div class="r-row">
          <div class="label">Office</div>
          <div class="value" id="rOffice">—</div>
        </div>

        <!-- visitor fields -->
        <div class="r-row" id="rRowName" style="display:none">
          <div class="label">Name</div>
          <div class="value" id="rName">—</div>
        </div>
        <div class="r-row" id="rRowPhone" style="display:none">
          <div class="label">Phone</div>
          <div class="value" id="rPhone">—</div>
        </div>
        <div class="r-row" id="rRowEmail" style="display:none">
          <div class="label">Email</div>
          <div class="value" id="rEmail">—</div>
        </div>

        <div class="r-row">
          <div class="label">Services</div>
          <div class="value" id="rServices">—</div>
        </div>

        <div class="qr-block">
          <img id="qrImg" alt="Live queue QR" />
          <div class="queue-row">
            <!-- <div class="label">Queue</div> -->
            <div class="queueValue" id="rQueue"></div>
          </div>
          <!-- <div class="qr-caption">Scan to view live queue for this office</div> -->
        </div>
      </div>

      <footer class="r-footer">
        <button id="confirmBtn" class="btn ghost">Back to menu</button>
        <button id="printBtn" class="btn primary">Print Receipt</button>
      </footer>
    </section>
  </main>

  <?php
  // Expose face login status
  if (isset($_SESSION['client']) && is_array($_SESSION['client'])) {
    $client_js = json_encode($_SESSION['client']);
    echo "<script>window.FACE_LOGGED = true; window.__CLIENT_SESSION = $client_js;</script>\n";
  } else {
    echo "<script>window.FACE_LOGGED = false; window.__CLIENT_SESSION = null;</script>\n";
  }

  /* ---- Provide APP_BASE to JS so receipt.js can redirect properly ---- */

  // Set explicit app base for LINYA project
  $app_base = '/LINYA';

  // Optional QR host overrides (leave blank to use hostname)
  $qr_host_override = '';
  $qr_port_override = '';
  ?>
  <script>
    window.APP_BASE = "<?php echo addslashes($app_base); ?>";
    <?php if (!empty($qr_host_override)): ?>
      window.QR_HOST_OVERRIDE = "<?php echo addslashes($qr_host_override); ?>";
    <?php endif; ?>
    <?php if (!empty($qr_port_override)): ?>
      window.QR_PORT_OVERRIDE = "<?php echo addslashes($qr_port_override); ?>";
    <?php endif; ?>

    // Debug information
    console.log('APP_BASE set to:', window.APP_BASE);
    console.log('Current URL:', window.location.href);
    console.log('FACE_LOGGED:', window.FACE_LOGGED);
  </script>

  <script src="../js/receipt.js?v=<?php echo time(); ?>"></script>
</body>

</html>