<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../backend/models/Report.php';
require_once __DIR__ . '/../../vendor/fpdf.php'; 

$type = $_GET['type'] ?? 'properties';
$reportModel = new Report($pdo);

switch ($type) {
    case 'reservations': $data = $reportModel->getReservations(); break;
    case 'payments': $data = $reportModel->getPayments(); break;
    case 'cleanings': $data = $reportModel->getCleanings(); break;
    default: $data = $reportModel->getProperties(); break;
}

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, strtoupper($type) . ' REPORT', 0, 1, 'C');
$pdf->Ln(5);
$pdf->SetFont('Arial', '', 10);

if (!empty($data)) {
    $cols = array_keys($data[0]);
    foreach ($cols as $col) {
        $pdf->Cell(40, 10, strtoupper($col), 1);
    }
    $pdf->Ln();

    foreach ($data as $row) {
        foreach ($row as $value) {
            $pdf->Cell(40, 10, substr($value, 0, 20), 1);
        }
        $pdf->Ln();
    }
}

$pdf->Output();
