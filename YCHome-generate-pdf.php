<?php
// YCHome-generate-pdf.php
require_once __DIR__ . '/admin/fpdf.php';
require_once __DIR__ . '/YCdb_connection.php';


// Fetch band members (all fields for name/role)
$stmt = $pdo->query("SELECT name, role FROM band_members ORDER BY id");
$bandMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch latest 'What's New' (all fields for title/description)
$stmt2 = $pdo->query("SELECT title, description FROM whats_new WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
$whatsNew = $stmt2->fetch(PDO::FETCH_ASSOC);

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',18);
$pdf->Cell(0,12,'Yaka Crew Home Page Report',0,1,'C');
$pdf->SetFont('Arial','',11);
$pdf->Cell(0,8,'Date: ' . date('Y-m-d H:i:s'),0,1,'R');
$pdf->Ln(5);

$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,'1. Band Members',0,1);
$pdf->SetFont('Arial','',12);
if (count($bandMembers) > 0) {
    foreach ($bandMembers as $member) {
        $pdf->Cell(0,8,'Member name: ' . $member['name'],0,1);
        $pdf->Cell(0,8,'Role: ' . $member['role'],0,1);
        $pdf->Ln(2);
    }
} else {
    $pdf->Cell(0,10,'No band members found.',0,1);
}
$pdf->Ln(5);

$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,utf8_decode('2. What\'s new at the moment'),0,1);
$pdf->SetFont('Arial','',12);
if ($whatsNew && (!empty($whatsNew['title']) || !empty($whatsNew['description']))) {
    $pdf->Cell(0,8,'• Topic: ' . $whatsNew['title'],0,1);
    $pdf->MultiCell(0,8,'• Description: ' . $whatsNew['description']);
} else {
    $pdf->Cell(0,10,'No current updates.',0,1);
}
$pdf->Output('D', 'YakaCrew_Home_Report.pdf');
exit;
