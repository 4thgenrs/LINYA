<?php include '../linya_db/config.php'; ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Assign Staff to Window</title>
  <link rel="stylesheet" href="../css/assignStaff.css">
</head>
<body>
  <div class="assign-container">
    <h2>Create Window & Assign Staff</h2>

    <form id="createForm" class="form">
      <label>Office
        <select id="officeSelect" name="office_id" required>
          <option value="">Loading offices...</option>
        </select>
      </label>

      <label>Window No. <small style="font-weight:400;display:block">leave empty to auto-assign</small>
        <input id="windowNo" name="window_no" type="number" min="1" />
      </label>

      <label>Assign Staff
        <select id="staffSelect" name="staff_id" required disabled>
          <option value="">Select office first</option>
        </select>
      </label>

      <div class="actions">
        <button id="createBtn" type="submit">Create Window</button>
        <button id="refreshBtn" type="button">Refresh List</button>
      </div>
    </form>

    <h3>Existing Windows</h3>
    <div id="listWrap">
      <table id="windowsTable">
        <thead>
          <tr><th>Office</th><th>Window No.</th><th>Assigned Staff</th><th>Enabled</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <tr><td colspan="5">Loading...</td></tr>
        </tbody>
      </table>
    </div>
  </div>

  <script src="../js/assignStaff.js"></script>
</body>
</html>