<?php
// Generates a PDF report of finished queues based on filters
require_once __DIR__ . '/fpdf.php';
include __DIR__ . '/../linya_db/config.php';

// Accept POST params from the modal form
$office_id = isset($_POST['office_id']) ? intval($_POST['office_id']) : 0;
$processed_by = isset($_POST['processed_by']) ? trim($_POST['processed_by']) : '';
$start_date = isset($_POST['start_date']) && $_POST['start_date'] !== '' ? $_POST['start_date'] : null;
$end_date = isset($_POST['end_date']) && $_POST['end_date'] !== '' ? $_POST['end_date'] : null;

// Build query
$params = [];
$where = [];
$sql = "SELECT f.finished_id,
               f.original_queue_id,
               f.office_id,
               COALESCE(o.office_description, '') AS office_description,
               f.window_id,
               f.window_id AS window_no,
               f.processed_by,
           f.created_at,
           f.finished_at,
           f.process_start,
           f.process_finished,
           f.processing_duration,
           f.remarks,
           f.services,
           f.customer_name,
           f.customer_phone,
           f.customer_email,
           f.student_number,
           f.course
        FROM finished_queues f
        LEFT JOIN offices o ON f.office_id = o.office_id";

if ($office_id > 0) $where[] = "f.office_id = " . intval($office_id);
if ($processed_by !== '') $where[] = "f.processed_by = '" . $conn->real_escape_string($processed_by) . "'";
if ($start_date && $end_date) {
    // include full day for end_date
    $sd = $conn->real_escape_string($start_date) . " 00:00:00";
    $ed = $conn->real_escape_string($end_date) . " 23:59:59";
    $where[] = "(f.finished_at >= '" . $sd . "' AND f.finished_at <= '" . $ed . "')";
} elseif ($start_date) {
    $sd = $conn->real_escape_string($start_date) . " 00:00:00";
    $where[] = "f.finished_at >= '" . $sd . "'";
} elseif ($end_date) {
    $ed = $conn->real_escape_string($end_date) . " 23:59:59";
    $where[] = "f.finished_at <= '" . $ed . "'";
}

if (!empty($where)) $sql .= " WHERE " . implode(' AND ', $where);

$sql .= " ORDER BY f.finished_at DESC, f.finished_id DESC LIMIT 5000";

$res = $conn->query($sql);

$rows = [];
if ($res) {
    while ($r = $res->fetch_assoc()) $rows[] = $r;
}

// Create PDF
$pdf = new FPDF('L', 'mm', 'Legal');
$pdf->SetAutoPageBreak(true,10);
$pdf->AddPage();
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,8, 'Finished Queues Report', 0, 1, 'C');
$pdf->SetFont('Arial','',10);
$meta = [];
if ($office_id>0) {
    $oRes = $conn->query("SELECT office_description FROM offices WHERE office_id = " . intval($office_id) . " LIMIT 1");
    if ($oRes && $or = $oRes->fetch_assoc()) $meta[] = 'Office: ' . $or['office_description'];
}
if ($processed_by !== '') $meta[] = 'Staff: ' . $processed_by;
if ($start_date || $end_date) $meta[] = 'Date: ' . ($start_date ?: 'Any') . ' - ' . ($end_date ?: 'Any');
if (!empty($meta)) $pdf->Cell(0,6, implode(' | ', $meta), 0, 1, 'C');
$pdf->Ln(4);

// Table header
$pdf->SetFont('Arial','B',9);
$headers = ['Office','Window','Processed By','Created At','Finished At','Process Start','Process Finished','Processing Duration','Remarks','Services','Name','Phone','Email','Student #','Course','Original Queue ID','Record ID'];
$widths = [25,18,30,28,28,28,28,20,60,80,40,30,60,30,30,22,18];
foreach ($headers as $i => $h) {
    $pdf->Cell($widths[$i],6,$h,1,0,'C');
}
$pdf->Ln();
$pdf->Ln();

$pdf->SetFont('Arial','',8);
// record left X for table start (can't access protected lMargin)
$tableStartX = $pdf->GetX();
$lineHeight = 5;
// Render rows with MultiCell so long text wraps and rows keep consistent height
foreach ($rows as $r) {
    // sanitize fields
    $cells = [
        $r['office_description'],
        $r['window_no'] ? $r['window_no'] : '—',
        $r['processed_by'],
        mb_substr($r['created_at'] ?? '',0,19),
        mb_substr($r['finished_at'] ?? '',0,19),
        mb_substr($r['process_start'] ?? '',0,19),
        mb_substr($r['process_finished'] ?? '',0,19),
        $r['processing_duration'] ?? '',
        $r['remarks'] ?? '',
        preg_replace('/\s+/',' ', $r['services'] ?? ''),
        $r['customer_name'] ?? '',
        $r['customer_phone'] ?? '',
        $r['customer_email'] ?? '',
        $r['student_number'] ?? '',
        $r['course'] ?? '',
        $r['original_queue_id'] ?? '',
        $r['finished_id'] ?? ''
    ];

    // calculate number of lines needed for each cell
    $nbLines = [];
    foreach ($cells as $i => $txt) {
        $w = $widths[$i] - 2; // small padding
        $txt = trim((string)$txt);
        if ($txt === '') { $nbLines[$i] = 1; continue; }
        $strWidth = $pdf->GetStringWidth($txt);
        $approxLines = (int)ceil($strWidth / $w);
        $nbLines[$i] = max(1, $approxLines);
    }

    $maxLines = max($nbLines);
    $rowHeight = $lineHeight * $maxLines;

    // starting position
    $x = $pdf->GetX();
    $y = $pdf->GetY();

    // draw each cell as a MultiCell and adjust X/Y to keep same row
    for ($i = 0; $i < count($cells); $i++) {
        $w = $widths[$i];
        $txt = $cells[$i];

        // Save current position
        $pdf->SetXY($x, $y);
        // Use MultiCell — this will output wrapped text within the cell width
        $pdf->MultiCell($w, $lineHeight, $txt, 1, 'L');

        // Move X to next cell start
        $x += $w;
        // Reset Y back to top of row for next cell
        $pdf->SetXY($x, $y);
    }

    // Move to next line after row height
    $pdf->SetXY($tableStartX, $y + $rowHeight);
}

// Output PDF inline
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="finished_report.pdf"');
echo $pdf->Output('S');
exit;

?>
