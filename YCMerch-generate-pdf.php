<?php
// YCMerch-generate-pdf.php: Generate PDF report for Merchandise Store
require_once __DIR__ . '/admin/fpdf.php';
require_once __DIR__ . '/YCdb_connection.php';

date_default_timezone_set('Asia/Colombo');

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 18);
$pdf->Cell(0, 12, 'Merchandise Store Report', 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 8, 'Generated: ' . date('Y-m-d H:i:s'), 0, 1, 'C');
$pdf->Ln(5);

$pdo = new PDO($dsn, $username, $password);

// Product types
$productTypes = ['tshirts', 'posters', 'hoodies', 'wristband'];

// Sales Overview
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Sales Overview', 0, 1);
$pdf->SetFont('Arial', '', 12);
$totalRevenue = 0;
$totalOrders = 0;
$totalItemsSold = 0;

foreach ($productTypes as $type) {
    $stmt = $pdo->query("SELECT SUM(price * sold) as revenue, SUM(sold) as items_sold, COUNT(*) as products FROM `$type`");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalRevenue += (float)$row['revenue'];
    $totalItemsSold += (int)$row['items_sold'];
    $totalOrders += (int)$row['products'];
}
$pdf->Cell(0, 8, "Total Revenue: LKR " . number_format($totalRevenue, 2), 0, 1);
$pdf->Cell(0, 8, "Total Products: $totalOrders", 0, 1);
$pdf->Cell(0, 8, "Total Items Sold: $totalItemsSold", 0, 1);
$pdf->Ln(5);

// Product List & Stock Status
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Product List & Stock Status', 0, 1);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(40, 8, 'Type', 1);
$pdf->Cell(50, 8, 'Name', 1);
$pdf->Cell(25, 8, 'Price', 1);
$pdf->Cell(25, 8, 'Stock', 1);
$pdf->Cell(25, 8, 'Sold', 1);
$pdf->Ln();
$pdf->SetFont('Arial', '', 11);

foreach ($productTypes as $type) {
    $stmt = $pdo->query("SELECT name, price, stock, sold FROM `$type`");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $pdf->Cell(40, 8, ucfirst($type), 1);
        $pdf->Cell(50, 8, $row['name'], 1);
        $pdf->Cell(25, 8, number_format($row['price'], 2), 1);
        $pdf->Cell(25, 8, $row['stock'], 1);
        $pdf->Cell(25, 8, $row['sold'], 1);
        $pdf->Ln();
    }
}
$pdf->Ln(5);

// Best/Least Selling Products
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Best & Least Selling Products', 0, 1);
$pdf->SetFont('Arial', '', 12);
foreach ($productTypes as $type) {
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 8, ucfirst($type), 0, 1);
    $pdf->SetFont('Arial', '', 12);
    // Best selling
    $stmt = $pdo->query("SELECT name, sold FROM `$type` ORDER BY sold DESC LIMIT 1");
    $best = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($best && $best['sold'] > 0) {
        $pdf->Cell(0, 8, '  Best Selling: ' . $best['name'] . ' (' . $best['sold'] . ' sold)', 0, 1);
    } else {
        $pdf->Cell(0, 8, '  Best Selling: N/A', 0, 1);
    }
    // Least selling
    $stmt = $pdo->query("SELECT name, sold FROM `$type` ORDER BY sold ASC LIMIT 1");
    $least = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($least && $least['sold'] > 0) {
        $pdf->Cell(0, 8, '  Least Selling: ' . $least['name'] . ' (' . $least['sold'] . ' sold)', 0, 1);
    } else {
        $pdf->Cell(0, 8, '  Least Selling: N/A', 0, 1);
    }
}

$pdf->Output('D', 'Merchandise_Store_Report_' . date('Ymd_His') . '.pdf');
exit;
