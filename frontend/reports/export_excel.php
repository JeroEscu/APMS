<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../backend/models/Report.php';

$type = $_GET['type'] ?? 'properties';
$reportModel = new Report($pdo);

switch ($type) {
    case 'reservations': $data = $reportModel->getReservations(); break;
    case 'payments': $data = $reportModel->getPayments(); break;
    case 'cleanings': $data = $reportModel->getCleanings(); break;
    default: $data = $reportModel->getProperties(); break;
}

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="' . $type . '_report.xls"');
header('Cache-Control: max-age=0');

echo "<table border='1'>";
if (!empty($data)) {
    echo "<tr>";
    foreach (array_keys($data[0]) as $col) {
        echo "<th>" . strtoupper($col) . "</th>";
    }
    echo "</tr>";

    foreach ($data as $row) {
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . htmlspecialchars($value) . "</td>";
        }
        echo "</tr>";
    }
}
echo "</table>";
