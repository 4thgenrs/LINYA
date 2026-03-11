<?php


?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>My Activity Log — LINYA</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../css/activityLog.css">
    <link rel="stylesheet" href="../css/table-unified.css">
</head>
<body>
    <main class="container-fluid activity-log-shell">
        <section class="activity-log-hero">
            <div class="activity-log-hero-copy">
                <span class="activity-log-hero-badge">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    Activity Tracking
                </span>
                <h1><i class="fa-solid fa-clock-rotate-left me-2"></i>My Activity Log</h1>
                <p class="activity-log-hero-subtitle">Review the queues you processed, filter by office or date range, and monitor completed service history from one clean activity view.</p>
            </div>
            <div class="activity-log-hero-side">
                <div class="activity-log-status-pill">
                    <span class="activity-log-status-dot"></span>
                    Personal activity feed
                </div>
                <span class="activity-log-status-copy">Use the filters below to narrow your completed queue history by office, day, month, or custom range.</span>
            </div>
        </section>

        <section class="activity-log-toolbar card shadow-sm border-0">
            <div class="card-body">
                <div class="activity-log-toolbar-row">
                    <div class="activity-log-toolbar-copy">
                        <span class="activity-log-section-eyebrow">Filters</span>
                        <h2>Activity Filters</h2>
                        <p>Adjust office and date conditions to review only the activity records relevant to your current audit or follow-up.</p>
                    </div>

                    <div class="activity-log-controls">
                        <div class="activity-log-filter-group">
                            <label for="officeFilter">Office</label>
                            <select id="officeFilter" class="form-select"><option value="0">All</option></select>
                        </div>

                        <div class="activity-log-filter-group">
                            <label for="filterType">Date Filter</label>
                            <select id="filterType" class="form-select">
                                <option value="all">All</option>
                                <option value="day">Day</option>
                                <option value="month">Month</option>
                                <option value="range">Range</option>
                            </select>
                        </div>

                        <div class="activity-log-filter-group is-conditional">
                            <label for="filterDate">Specific Day</label>
                            <input type="date" id="filterDate" class="form-control" style="display:none;" />
                        </div>

                        <div class="activity-log-filter-group is-conditional">
                            <label for="filterMonth">Specific Month</label>
                            <input type="month" id="filterMonth" class="form-control" style="display:none;" />
                        </div>

                        <div class="activity-log-range-group">
                            <div class="activity-log-filter-group is-conditional">
                                <label for="startDate">Start Date</label>
                                <input type="date" id="startDate" class="form-control" style="display:none;" />
                            </div>

                            <div class="activity-log-filter-group is-conditional">
                                <label for="endDate">End Date</label>
                                <input type="date" id="endDate" class="form-control" style="display:none;" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="activity-log-table-card card shadow-sm border-0">
            <div class="card-header text-white activity-log-table-header">
                <div>
                    <span class="activity-log-section-eyebrow is-light">Completed Queues</span>
                    <h5 class="mb-0"><i class="fa-solid fa-table-list me-2"></i>Processed Queue History</h5>
                </div>
                <div class="activity-log-table-icon">
                    <i class="fa-solid fa-table-list"></i>
                </div>
            </div>
            <div class="card-body">
                <div class="d-none d-md-block">
                    <section class="fq-table-wrap activity-log-table-wrap">
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
                </div>
                <div class="d-block d-md-none">
                    <div class="mobile-table-search">
                        <input type="text" class="form-control" id="activityMobileSearch" placeholder="Search activity...">
                    </div>
                    <div id="activityMobileEmpty" class="mobile-table-empty" style="display:none;">No activity records found.</div>
                    <div id="activityMobileList" class="mobile-table-list"></div>
                </div>
            </div>
        </section>
    </main>

    <script src="../js/tableMobileCards.js"></script>
    <script src="../js/activityLog.js"></script>
</body>
</html>