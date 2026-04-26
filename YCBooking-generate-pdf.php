<?php
// Booking PDF Generator for Admin Panel
require_once __DIR__ . '/YCdb_connection.php';
require_once __DIR__ . '/admin/fpdf.php';

// Filters
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$event_id = $_GET['event_id'] ?? '';
$payment_status = $_GET['payment_status'] ?? '';

// Build query
$where = [];
$params = [];
if ($date_from && $date_to) {
    $where[] = 'booking_date BETWEEN ? AND ?';
    $params[] = $date_from;
    $params[] = $date_to;
}
if ($event_id) {
    $where[] = 'event_id = ?';
    $params[] = $event_id;
}
if ($payment_status) {
    $where[] = 'payment_status = ?';
    $params[] = $payment_status;
}
$where_sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$sql = "SELECT id, first_name, last_name, email, mobile_number, booking_date, booking_time, other_info, booking_status, payment_status, admin_notes
    FROM bookings
    $where_sql
    ORDER BY booking_date DESC, id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll();


$pdf = new FPDF('L','mm','A4');
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'Yaka Crew Booking Report',0,1,'C');
$pdf->SetFont('Arial','I',10);
$pdf->SetTextColor(80,80,80);
$pdf->Cell(0,6,'Generated: '.date('Y-m-d H:i:s'),0,1,'C');
$pdf->SetTextColor(0,0,0);
$pdf->Ln(2);

// Table header
$pdf->SetFillColor(101,73,34);
$pdf->SetTextColor(255);
$pdf->SetFont('Arial','B',11);


$pdf->SetFillColor(0,0,0);
$pdf->SetDrawColor(0,0,0);
$pdf->SetLineWidth(0.3);
$pdf->SetFont('Arial','B',9);
$pdf->Cell(10,8,'ID',1,0,'C',true);
$pdf->Cell(22,8,'First Name',1,0,'C',true);
$pdf->Cell(22,8,'Last Name',1,0,'C',true);
$pdf->Cell(60,8,'Email',1,0,'C',true);
$pdf->Cell(26,8,'Mobile',1,0,'C',true);
$pdf->Cell(18,8,'Date',1,0,'C',true);
$pdf->Cell(14,8,'Time',1,0,'C',true);
$pdf->Cell(44,8,'Status',1,0,'C',true);
$pdf->Cell(22,8,'Payment',1,1,'C',true);

$pdf->SetFont('Arial','',10);


$pdf->SetFont('Arial','',9);
$pdf->SetDrawColor(0,0,0);
$pdf->SetLineWidth(0.2);
$pdf->SetTextColor(0);
foreach ($bookings as $row) {
    $pdf->Cell(10,7,$row['id'],1);
    $pdf->Cell(22,7,iconv('UTF-8','windows-1252',$row['first_name']),1);
    $pdf->Cell(22,7,iconv('UTF-8','windows-1252',$row['last_name']),1);
    $pdf->Cell(60,7,iconv('UTF-8','windows-1252',substr($row['email'],0,80)),1);
    $pdf->Cell(26,7,iconv('UTF-8','windows-1252',$row['mobile_number']),1);
    $pdf->Cell(18,7,$row['booking_date'],1);
    $pdf->Cell(14,7,$row['booking_time'],1);
    $pdf->Cell(44,7,iconv('UTF-8','windows-1252',substr($row['booking_status'],0,60)),1);
    $pdf->Cell(22,7,iconv('UTF-8','windows-1252',substr($row['payment_status'],0,30)),1,1);
    // Admin Notes (if present)
    if (!empty($row['admin_notes'])) {
        $pdf->SetFont('Arial','I',8);
        $pdf->SetFillColor(240,240,240);
        $pdf->Cell(10+22+22+60+26+18+14+44+22,6,'Admin Notes: '.iconv('UTF-8','windows-1252',substr($row['admin_notes'],0,200)),1,1,'L',true);
        $pdf->SetFont('Arial','',9);
        $pdf->SetFillColor(255,255,255);
    }
}

$pdf->Output('D','booking_report.pdf');
exit;
