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
  </main>

  <script src="../js/finishedQueue.js"></script>
</body>
</html>