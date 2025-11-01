<?php include '../linya_db/config.php'; ?>
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
          <div class="brand-name">LINYA</div>
          <div class="brand-sub">Queue Receipt</div>
        </div>
        <div class="r-meta" id="rMeta"></div>
      </header>

      <div class="r-body" id="rBody">
        <div class="r-row">
          <div class="label">Queue</div>
          <div class="value" id="rQueue">—</div>
        </div>

        <div class="r-row">
          <div class="label">Office</div>
          <div class="value" id="rOffice">—</div>
        </div>

        <div class="r-row">
          <div class="label">Services</div>
          <div class="value" id="rServices">—</div>
        </div>

        <div class="qr-block">
          <img id="qrImg" alt="Live queue QR" />
          <div class="qr-caption">Scan to view live queue for this office</div>
        </div>
      </div>

      <footer class="r-footer">
        <button id="confirmBtn" class="btn ghost">Back to menu</button>
        <button id="printBtn" class="btn primary">Print Receipt</button>
      </footer>
    </section>
  </main>

  <script src="../js/receipt.js"></script>
</body>
</html>