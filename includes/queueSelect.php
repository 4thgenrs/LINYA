<?php
// Frontend: minimal PHP that sets defaults (can be overridden with ?office=ID&window=ID)
$office = isset($_GET['office']) ? intval($_GET['office']) : 1;
$window = isset($_GET['window']) ? intval($_GET['window']) : 1;
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Queue Window</title>
  <link rel="stylesheet" href="../css/queueSelect.css" />
</head>
<body>
  <div class="wrap">
    <div class="box" role="group" aria-label="Window Control" id="boxRoot" data-office="<?php echo $office; ?>" data-window="<?php echo $window; ?>">
      <div id="window" class="window">Window <?php echo $window; ?></div>
      <div id="number" class="number">Loading…</div>
      <div><i id="ongoingText"></i></div>

      <div class="controls">
        <button id="finished" type="button" disabled>Finished</button>
        <button id="onBreak" type="button" data-break="false">Break</button>
        <button id="nextQueue" type="button">Call Next</button>
      </div>
    </div>

    <div class="queue-box" aria-label="Queue List">
      <div class="queue-title">Queue List</div>
      <div id="queueListContainer"></div>
    </div>
  </div>

  <script src="../js/queueSelect.js"></script>
</body>
</html>