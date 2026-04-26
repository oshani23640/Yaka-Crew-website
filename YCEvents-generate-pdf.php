<?php
require_once __DIR__ . '/YCdb_connection.php';
require_once __DIR__ . '/admin/fpdf.php';

// Get filter parameters (if any)
$dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : null;
$dateTo = isset($_GET['date_to']) ? $_GET['date_to'] : null;
$eventId = isset($_GET['event_id']) ? $_GET['event_id'] : null;

// Build WHERE clause for date range and event filter
$where = [];
$params = [];
if ($dateFrom) {
    $where[] = 'e.event_date >= ?';
    $params[] = $dateFrom;
}
if ($dateTo) {
    $where[] = 'e.event_date <= ?';
    $params[] = $dateTo;
}
if ($eventId) {
    $where[] = 'e.id = ?';
    $params[] = $eventId;
}
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// Query all events with sales summary
$sql = "
    SELECT e.id, e.title, e.event_date, e.location, e.price,
           IFNULL(SUM(es.quantity), 0) AS tickets_sold,
           IFNULL(SUM(es.total_price), 0) AS revenue
    FROM events e
    LEFT JOIN event_sales es ON e.id = es.event_id
    $whereSql
    GROUP BY e.id, e.title, e.event_date, e.location, e.price
    ORDER BY e.event_date DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$events = $stmt->fetchAll();

$pdf = new FPDF('L','mm','A4');
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'Yaka Crew Events Report',0,1,'C');
$pdf->SetFont('Arial','I',10);
$pdf->SetTextColor(80,80,80);
$pdf->Cell(0,6,'Generated: '.date('Y-m-d H:i:s'),0,1,'C');
$pdf->SetTextColor(0,0,0);
$pdf->Ln(2);

// Admin Reports Section
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,8,'Admin Reports',0,1);
$pdf->Ln(2);

// Event Report Section
$pdf->SetFont('Arial','B',11);
$pdf->Cell(0,8,'Event Report',0,1);
$pdf->SetFont('Arial','B',9);
$pdf->SetFillColor(0,0,0);
$pdf->SetTextColor(255,255,255);
$pdf->Cell(10,8,'ID',1,0,'C',true);
$pdf->Cell(60,8,'Title',1,0,'C',true);
$pdf->Cell(25,8,'Date',1,0,'C',true);
$pdf->Cell(50,8,'Venue',1,0,'C',true);
$pdf->Cell(25,8,'Ticket Price',1,0,'C',true);
$pdf->Cell(25,8,'Tickets Sold',1,0,'C',true);
$pdf->Cell(30,8,'Revenue',1,1,'C',true);
$pdf->SetFont('Arial','',9);
$pdf->SetTextColor(0,0,0);
foreach ($events as $row) {
    $pdf->Cell(10,7,$row['id'],1);
    $pdf->Cell(60,7,iconv('UTF-8','windows-1252',substr($row['title'],0,40)),1);
    $pdf->Cell(25,7,$row['event_date'],1);
    $pdf->Cell(50,7,iconv('UTF-8','windows-1252',substr($row['location'],0,30)),1);
    $pdf->Cell(25,7,number_format($row['price'],2),1);
    $pdf->Cell(25,7,$row['tickets_sold'],1);
    $pdf->Cell(30,7,number_format($row['revenue'],2),1,1);
}
$pdf->Ln(4);

// Sales Report Section
$pdf->SetFont('Arial','B',11);
$pdf->Cell(0,8,'Sales Report',0,1);
$pdf->SetFont('Arial','',9);
$pdf->Cell(0,7,'Total tickets sold and revenue per event are shown above. Use filters for date range or event.',0,1);

$pdf->Output('D','YakaCrew-Events-Report.pdf');
exit;
