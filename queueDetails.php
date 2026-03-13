<?php
include __DIR__ . '/linya_db/config.php';

function h($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function format_display_datetime(?string $value): string {
    if (!$value) {
        return 'N/A';
    }

    $timestamp = strtotime($value);
    if ($timestamp === false) {
        return $value;
    }

    return date('F j, Y g:i A', $timestamp);
}

function parse_service_list(?string $servicesText): array {
    if (!$servicesText) {
        return [];
    }

    $items = array_filter(array_map('trim', explode(',', $servicesText)), static function ($item) {
        return $item !== '';
    });

    return array_values($items);
}

$queueId = isset($_GET['queue_id']) ? intval($_GET['queue_id']) : 0;
$error = '';
$queue = null;
$services = [];

if (!$queueId) {
    $error = 'Missing queue ID.';
} elseif (!isset($conn)) {
    $error = 'Database connection is not available.';
} else {
    $liveStmt = $conn->prepare("SELECT q.queue_id, q.queue_code, q.created_at, q.status, q.customer_name, q.customer_phone, q.customer_email, o.office_description
                                FROM queues q
                                JOIN offices o ON o.office_id = q.office_id
                                WHERE q.queue_id = ?
                                LIMIT 1");

    if ($liveStmt) {
        $liveStmt->bind_param('i', $queueId);
        $liveStmt->execute();
        $liveResult = $liveStmt->get_result();
        $liveRow = $liveResult ? $liveResult->fetch_assoc() : null;
        $liveStmt->close();

        if ($liveRow) {
            $queue = [
                'queue_code' => $liveRow['queue_code'],
                'office_description' => $liveRow['office_description'],
                'created_at' => $liveRow['created_at'],
                'status' => $liveRow['status'],
                'customer_name' => $liveRow['customer_name'],
                'customer_phone' => $liveRow['customer_phone'],
                'customer_email' => $liveRow['customer_email'],
            ];

            $servicesStmt = $conn->prepare("SELECT s.service_description, qs.soft_copy
                                            FROM queue_services qs
                                            JOIN services s ON s.service_id = qs.service_id
                                            WHERE qs.queue_id = ?
                                            ORDER BY qs.id ASC");
            if ($servicesStmt) {
                $servicesStmt->bind_param('i', $queueId);
                $servicesStmt->execute();
                $servicesResult = $servicesStmt->get_result();
                while ($servicesResult && ($serviceRow = $servicesResult->fetch_assoc())) {
                    $label = $serviceRow['service_description'];
                    if (!empty($serviceRow['soft_copy'])) {
                        $label .= ' (PDF)';
                    }
                    $services[] = $label;
                }
                $servicesStmt->close();
            }
        }
    }

    if ($queue === null) {
        $archiveQueries = [
                "SELECT COALESCE(q.queue_code, CONCAT(COALESCE(o_num.office_code, o_name.office_code, 'OFF'), '-', LPAD(a.original_queue_id, 3, '0'))) AS queue_code,
                    COALESCE(o_num.office_description, o_name.office_description, a.office_id) AS office_description,
                    a.created_at,
                    'voided' AS status,
                    a.customer_name,
                    a.customer_phone,
                    a.customer_email,
                    a.services
             FROM finished_queues a
             LEFT JOIN queues q ON q.queue_id = a.original_queue_id
             LEFT JOIN offices o_num ON o_num.office_id = a.office_id
             LEFT JOIN offices o_name ON o_name.office_description = a.office_id
             WHERE a.original_queue_id = ?
             LIMIT 1",
            "SELECT COALESCE(q.queue_code, CONCAT(COALESCE(o_num.office_code, o_name.office_code, 'OFF'), '-', LPAD(a.original_queue_id, 3, '0'))) AS queue_code,
                    COALESCE(o_num.office_description, o_name.office_description, a.office_id) AS office_description,
                    a.created_at,
                    a.status,
                    a.customer_name,
                    a.customer_phone,
                    a.customer_email,
                    a.services
             FROM voided_queues a
             LEFT JOIN queues q ON q.queue_id = a.original_queue_id
             LEFT JOIN offices o_num ON o_num.office_id = a.office_id
             LEFT JOIN offices o_name ON o_name.office_description = a.office_id
             WHERE a.original_queue_id = ?
             LIMIT 1",
        ];

        foreach ($archiveQueries as $archiveSql) {
            $archiveStmt = $conn->prepare($archiveSql);
            if (!$archiveStmt) {
                continue;
            }

            $archiveStmt->bind_param('i', $queueId);
            $archiveStmt->execute();
            $archiveResult = $archiveStmt->get_result();
            $archiveRow = $archiveResult ? $archiveResult->fetch_assoc() : null;
            $archiveStmt->close();

            if ($archiveRow) {
                $queue = [
                    'queue_code' => $archiveRow['queue_code'],
                    'office_description' => $archiveRow['office_description'],
                    'created_at' => $archiveRow['created_at'],
                    'status' => $archiveRow['status'],
                    'customer_name' => $archiveRow['customer_name'],
                    'customer_phone' => $archiveRow['customer_phone'],
                    'customer_email' => $archiveRow['customer_email'],
                ];
                $services = parse_service_list($archiveRow['services']);
                break;
            }
        }
    }

    if ($queue === null && $error === '') {
        $error = 'Queue not found.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Queue Details</title>
    <style>
        :root {
            --page-bg: linear-gradient(135deg, #f7f4ea 0%, #dce8de 100%);
            --card-bg: rgba(255, 255, 255, 0.94);
            --accent: #174a3a;
            --accent-soft: #2c6b56;
            --text-main: #16201d;
            --text-muted: #5a6963;
            --line: rgba(23, 74, 58, 0.12);
            --shadow: 0 24px 60px rgba(22, 32, 29, 0.12);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            padding: 24px;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: var(--page-bg);
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card {
            width: min(760px, 100%);
            background: var(--card-bg);
            border: 1px solid var(--line);
            border-radius: 24px;
            padding: 28px;
            box-shadow: var(--shadow);
            backdrop-filter: blur(8px);
        }

        .eyebrow {
            margin: 0 0 12px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: var(--accent-soft);
        }

        h1 {
            margin: 0;
            font-size: clamp(30px, 5vw, 42px);
            line-height: 1;
        }

        .subtitle {
            margin: 10px 0 0;
            color: var(--text-muted);
            font-size: 15px;
        }

        .hero {
            display: grid;
            gap: 18px;
            margin-bottom: 28px;
        }

        .queue-pill {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-top: 18px;
            padding: 12px 16px;
            border-radius: 999px;
            background: rgba(23, 74, 58, 0.08);
            color: var(--accent);
            font-weight: 700;
            font-size: 15px;
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .detail-box,
        .services-box,
        .error-box {
            border-radius: 18px;
            border: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.82);
            padding: 18px;
        }

        .detail-label {
            margin: 0 0 8px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--text-muted);
        }

        .detail-value {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            word-break: break-word;
        }

        .services-box {
            margin-top: 16px;
        }

        .services-box h2 {
            margin: 0 0 14px;
            font-size: 18px;
        }

        .services-list {
            list-style: none;
            margin: 0;
            padding: 0;
            display: grid;
            gap: 10px;
        }

        .services-list li {
            padding: 14px 16px;
            border-radius: 14px;
            background: rgba(23, 74, 58, 0.06);
            border: 1px solid rgba(23, 74, 58, 0.08);
            font-weight: 600;
        }

        .empty-services {
            margin: 0;
            color: var(--text-muted);
        }

        .error-box h1 {
            font-size: 28px;
        }

        .error-box p {
            margin: 12px 0 0;
            color: var(--text-muted);
        }

        @media (max-width: 640px) {
            body {
                padding: 16px;
            }

            .card {
                padding: 20px;
                border-radius: 20px;
            }

            .details-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <main class="card">
        <?php if ($error !== ''): ?>
            <section class="error-box">
                <p class="eyebrow">Queue Lookup</p>
                <h1>Receipt details unavailable</h1>
                <p><?php echo h($error); ?></p>
            </section>
        <?php else: ?>
            <section class="hero">
                <div>
                    <p class="eyebrow">Receipt Queue Details</p>
                    <h1><?php echo h($queue['office_description'] ?: 'Office not available'); ?></h1>
                    <p class="subtitle">Review the queue information tied to this receipt.</p>
                    <div class="queue-pill">Queue Number: <?php echo h($queue['queue_code'] ?: ('QUEUE-' . $queueId)); ?></div>
                </div>
            </section>

            <section class="details-grid">
                <article class="detail-box">
                    <p class="detail-label">Timestamp</p>
                    <p class="detail-value"><?php echo h(format_display_datetime($queue['created_at'])); ?></p>
                </article>
                <article class="detail-box">
                    <p class="detail-label">Status</p>
                    <p class="detail-value"><?php echo h(ucfirst((string)($queue['status'] ?: 'pending'))); ?></p>
                </article>
                <article class="detail-box">
                    <p class="detail-label">Name</p>
                    <p class="detail-value"><?php echo h($queue['customer_name'] ?: 'Not provided'); ?></p>
                </article>
                <article class="detail-box">
                    <p class="detail-label">Contact Number</p>
                    <p class="detail-value"><?php echo h($queue['customer_phone'] ?: 'Not provided'); ?></p>
                </article>
                <article class="detail-box">
                    <p class="detail-label">Email</p>
                    <p class="detail-value"><?php echo h($queue['customer_email'] ?: 'Not provided'); ?></p>
                </article>
                <article class="detail-box">
                    <p class="detail-label">Office</p>
                    <p class="detail-value"><?php echo h($queue['office_description'] ?: 'Not provided'); ?></p>
                </article>
            </section>

            <section class="services-box">
                <h2>Selected Services</h2>
                <?php if (!empty($services)): ?>
                    <ul class="services-list">
                        <?php foreach ($services as $service): ?>
                            <li><?php echo h($service); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="empty-services">No services were found for this queue.</p>
                <?php endif; ?>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>