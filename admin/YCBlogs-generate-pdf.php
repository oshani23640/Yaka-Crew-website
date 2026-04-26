<?php
require_once __DIR__ . '/../YCdb_connection.php';
require_once __DIR__ . '/fpdf.php';

// Fetch all blogs
$blogs = $pdo->query("SELECT * FROM blogs ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
// Fetch all contact messages
$contacts = $pdo->query("SELECT * FROM messages ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

$pdf = new FPDF('L','mm','A4');
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'Yaka Crew Blogs Report',0,1,'C');
$pdf->SetFont('Arial','I',10);
$pdf->SetTextColor(80,80,80);
$pdf->Cell(0,6,'Generated: '.date('Y-m-d H:i:s'),0,1,'C');
$pdf->SetTextColor(0,0,0);
$pdf->Ln(2);

// Admin Reports Section
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,8,'Admin Reports',0,1);
$pdf->Ln(2);

// Blog Report Section
$pdf->SetFont('Arial','B',11);
$pdf->Cell(0,8,'Blog Report',0,1);
$pdf->SetFont('Arial','B',9);
$pdf->SetFillColor(0,0,0);
$pdf->SetTextColor(255,255,255);
$pdf->Cell(10,8,'ID',1,0,'C',true);
$pdf->Cell(60,8,'Title',1,0,'C',true);
$pdf->Cell(60,8,'Short Desc',1,0,'C',true);
$pdf->Cell(120,8,'Full Desc',1,1,'C',true);
$pdf->SetFont('Arial','',9);
$pdf->SetTextColor(0,0,0);
$pdf->SetFont('Arial','',9);
$pdf->SetTextColor(0,0,0);
foreach ($blogs as $row) {
    $pdf->Cell(10,7,$row['id'],1);
    $pdf->Cell(60,7,iconv('UTF-8','windows-1252',substr($row['title'],0,40)),1);
    $pdf->Cell(60,7,iconv('UTF-8','windows-1252',substr($row['short_description'],0,40)),1);
    $pdf->Cell(120,7,iconv('UTF-8','windows-1252',substr($row['full_description'],0,80)),1,1);
}
$pdf->Ln(4);

// Contact Report Section
$pdf->SetFont('Arial','B',11);
$pdf->Cell(0,8,'Contact Report',0,1);
$pdf->SetFont('Arial','B',9);
$pdf->SetFillColor(0,0,0);
$pdf->SetTextColor(255,255,255);
$pdf->Cell(10,8,'ID',1,0,'C',true);
$pdf->Cell(60,8,'Email',1,0,'C',true);
$pdf->Cell(120,8,'Message',1,0,'C',true);
$pdf->Cell(40,8,'Send Date',1,1,'C',true);
$pdf->SetFont('Arial','',9);
$pdf->SetTextColor(0,0,0);
foreach ($contacts as $row) {
    $pdf->Cell(10,7,$row['id'],1);
    $pdf->Cell(60,7,iconv('UTF-8','windows-1252',substr($row['email'],0,40)),1);
    $pdf->Cell(120,7,iconv('UTF-8','windows-1252',substr($row['message'],0,80)),1);
    $pdf->Cell(40,7,$row['created_at'],1,1);
}
$pdf->Output('D','YakaCrew-Blogs-Report.pdf');
exit;
