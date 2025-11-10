<?php
session_start();

if (!isset($_SESSION['user_id'])) {
        // User is not logged in, redirect to login page
        header('Location: ../login.php');
        exit();
}

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>My Activity Log — LINYA</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="../css/finishedQueue.css">
</head>
<body>
    <main class="fq-wrap">
        <header class="fq-header">
            <h1>My Activity Log</h1>
            <div class="controls">
                <label for="officeFilter">Office:</label>
                <select id="officeFilter"><option value="0">All</option></select>

                <label for="filterType">Date:</label>
                <select id="filterType">
                    <option value="all">All</option>
                    <option value="day">Day</option>
                    <option value="month">Month</option>
                    <option value="range">Range</option>
                </select>

                <input type="date" id="filterDate" style="display:none;" />
                <input type="month" id="filterMonth" style="display:none;" />
                <input type="date" id="startDate" style="display:none;" />
                <input type="date" id="endDate" style="display:none;" />
            </div>
        </header>

        <section class="fq-table-wrap">
            <table id="activityTable" class="fq-table" aria-label="My processed queues">
                <thead>
                    <tr>
                        <th>Office</th>
                        <th>Window</th>
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

    <script src="../js/activityLog.js"></script>
</body>
</html>