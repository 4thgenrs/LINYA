<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Queue Window with List</title>
  <style>
    :root{--bg:#f6f8fb;--box:#ffffff;--accent:#2563eb;--muted:#6b7280}
    html,body{height:100%;margin:0;font-family:Inter,system-ui,Segoe UI,Roboto,Arial,sans-serif;background:var(--bg);color:#111}
    .wrap{
      min-height:100%;
      display:flex;
      align-items:center;
      justify-content:center;
      gap:24px;
      padding:24px;
      flex-wrap:wrap;
    }
    .box{
      background:var(--box);
      border-radius:12px;
      padding:18px 22px;
      box-shadow:0 6px 18px rgba(15,23,42,0.06);
      display:flex;flex-direction:column;gap:12px;align-items:center;min-width:220px;
      transition:all .2s ease-in-out;
    }
    .window{
      font-size:48px;font-weight:700;line-height:1;color:var(--accent);
      width:100%;text-align:center
    }
    .number{
      font-size:32px;font-weight:600;color:#333;text-align:center;
    }
    .controls{display:flex;gap:8px;flex-wrap:wrap;justify-content:center}
    button{
      appearance:none;
      border:0;
      padding:10px 14px;
      border-radius:8px;
      font-weight:600;
      cursor:pointer;
      transition:all 0.2s ease-in-out;
      box-shadow:0 2px 5px rgba(0,0,0,0.1);
    }
    button:hover{
      transform:translateY(-2px);
      box-shadow:0 4px 8px rgba(0,0,0,0.15);
      opacity:0.9;
    }
    #finished{background:#08CB00;color:#fff}
    #onBreak{background:#ED3500;color:#fff}
    #nextQueue{background:var(--accent);color:#fff}

    .queue-box{
      background:var(--box);
      border-radius:12px;
      padding:18px 22px;
      box-shadow:0 6px 18px rgba(15,23,42,0.06);
      display:flex;flex-direction:column;
      align-items:center;
      min-width:200px;
      max-height:400px;
      overflow-y:auto;
    }
    .queue-title{
      font-size:20px;font-weight:700;margin-bottom:12px;color:var(--accent);
    }
    .queue-item{
      width:100%;
      text-align:center;
      padding:8px 0;
      margin:4px 0;
      border-radius:6px;
      background:#eef2ff;
      color:#000;
      font-weight:600;
    }

    .queue-box::-webkit-scrollbar {
      display: none;
    }
    .queue-box {
      -ms-overflow-style: none;
      scrollbar-width: none;
    }

    @media (max-width:380px){.number{font-size:28px}.window{font-size:36px}}
  </style>
</head>
<body>
  <div class="wrap">
    <div class="box" role="group" aria-label="Window Control">
      <div id="window" class="window" aria-live="polite">Window 1</div>
      <div id="number" class="number">REG 001</div>
      <div><i>Ongoing</i></div>

      <div class="controls">
        <button id="finished" type="button">Finished</button>
        <button id="onBreak" type="button">Break</button>
        <button id="nextQueue" type="button">Call Next</button>
      </div>
    </div>

    <div class="queue-box" aria-label="Queue List">
      <div class="queue-title">Queue List</div>
      <div class="queue-item">REG 002</div>
      <div class="queue-item">REG 003</div>
      <div class="queue-item">REG 004</div>
      <div class="queue-item">REG 005</div>
      <div class="queue-item">REG 006</div>
      <div class="queue-item">REG 007</div>
      <div class="queue-item">REG 008</div>
      <div class="queue-item">REG 009</div>
    </div>
  </div>

  <script>
    // Future JavaScript logic for dynamic queue control can go here.
  </script>
</body>
</html>
