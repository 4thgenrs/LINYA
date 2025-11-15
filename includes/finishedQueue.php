<?php include '../linya_db/config.php';


?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Finished Queues — LINYA</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="../css/finishedQueue.css">
</head>
<body>
  <main class="fq-wrap">
    <header class="fq-header">
      <h1>Finished Queues</h1>
      <div class="controls">
        <label for="officeFilter">Office:</label>
        <select id="officeFilter"><option value="0">All</option></select>
        <button id="refreshBtn" type="button">Refresh</button>
      </div>
    </header>

    <section class="fq-table-wrap">
      <table id="finishedTable" class="fq-table" aria-label="Finished queues">
        <thead>
          <tr>
            <th>Office</th>
            <th>Window</th>
            <th>Processed By</th>
            <th>Created At</th>
            <th>Finished At</th>
            <th>Services</th>
            <th>Name</th>
            <th>Phone</th>
            <th>Email</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </section>
    <footer class="fq-footer" style="margin-top:12px; text-align:right;">
      <button id="openReportBtn" type="button">Generate Report</button>
    </footer>
  </main>

  <!-- Generate Report Modal -->
  <div id="reportModal" class="modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); align-items:center; justify-content:center;">
    <div class="modal-content" style="background:#fff; padding:18px; width:520px; max-width:95%; border-radius:6px;">
      <h2 style="margin-top:0;">Generate Report</h2>
      <form id="reportForm" method="POST" target="_blank" action="../backend/generateFinishedReport.php">
        <div style="display:flex; gap:8px; margin-bottom:8px; align-items:center;">
          <label for="r_office">Office:</label>
          <select id="r_office" name="office_id"><option value="0">All</option></select>
          <label for="r_staff" style="margin-left:8px;">Staff:</label>
          <select id="r_staff" name="processed_by"><option value="">Any</option></select>
        </div>
        <div style="display:flex; gap:8px; margin-bottom:8px; align-items:center;">
          <label for="r_start">Start Date:</label>
          <input id="r_start" name="start_date" type="date">
          <label for="r_end" style="margin-left:8px;">End Date:</label>
          <input id="r_end" name="end_date" type="date">
        </div>
        <div style="text-align:right; margin-top:12px;">
          <button type="button" id="closeReportBtn">Close</button>
          <button type="submit" id="generateReportBtn">Generate</button>
        </div>
      </form>
    </div>
  </div>

  <script src="../js/finishedQueue.js"></script>
</body>
</html>