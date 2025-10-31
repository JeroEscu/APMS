<?php
session_start();
// Si no hay sesión, redirigir
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}
// Restricción de acceso según rol
if ($_SESSION['role_id'] == 3) {
    die("No tienes permiso para acceder a esta página.");
}

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../backend/models/Report.php';

$reportModel = new Report($pdo);
$type = $_GET['type'] ?? 'properties';
$isAdmin = ($_SESSION['role_id'] ?? 0) == 1;

switch ($type) {
    case 'reservations': $data = $reportModel->getReservations(); break;
    case 'payments': $data = $reportModel->getPayments(); break;
    case 'cleanings': $data = $reportModel->getCleanings(); break;
    case 'activity_log': $data = $reportModel->getActivityLog(); break;
    default: $data = $reportModel->getProperties(); break;
}

if ($type === 'activity_log' && !$isAdmin) {
    header('Location: ReportsList.php?type=properties');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reportes del Sistema - HostTrack</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #667eea;
      min-height: 100vh;
      padding: 40px 20px;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
    }

    .header-section {
      background: white;
      border-radius: 20px;
      padding: 30px;
      margin-bottom: 25px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
      animation: fadeIn 0.5s ease;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .header-section h1 {
      color: #1f2937;
      font-size: 28px;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .action-bar {
      display: flex;
      gap: 15px;
      flex-wrap: wrap;
    }

    .btn {
      display: inline-block;
      padding: 10px 20px;
      border-radius: 10px;
      text-decoration: none;
      font-weight: 600;
      font-size: 14px;
      transition: all 0.3s ease;
      border: none;
      cursor: pointer;
    }

    .btn-primary {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
    }

    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 16px rgba(102, 126, 234, 0.3);
    }

    .btn-secondary {
      background: #f3f4f6;
      color: #374151;
    }

    .btn-secondary:hover {
      background: #e5e7eb;
    }

    .filter-card {
      background: white;
      border-radius: 20px;
      padding: 25px;
      margin-bottom: 25px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
      animation: fadeIn 0.6s ease;
    }

    .filter-form {
      display: flex;
      gap: 15px;
      flex-wrap: wrap;
      align-items: end;
    }

    .filter-group {
      flex: 1;
      min-width: 220px;
    }

    .filter-group label {
      display: block;
      color: #374151;
      font-weight: 600;
      margin-bottom: 8px;
      font-size: 14px;
    }

    .filter-group select {
      width: 100%;
      padding: 10px 12px;
      border: 2px solid #e5e7eb;
      border-radius: 8px;
      font-size: 14px;
      background-color: #f9fafb;
      transition: all 0.3s ease;
    }

    .filter-group select:focus {
      outline: none;
      border-color: #667eea;
      background-color: white;
    }

    .table-card {
      background: white;
      border-radius: 20px;
      padding: 25px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
      animation: fadeIn 0.7s ease;
      overflow-x: auto;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    thead {
      background: #667eea;
    }

    th {
      padding: 15px;
      text-align: left;
      color: white;
      font-weight: 600;
      font-size: 14px;
      white-space: nowrap;
    }

    th:first-child { border-top-left-radius: 10px; }
    th:last-child { border-top-right-radius: 10px; }

    td {
      padding: 15px;
      border-bottom: 1px solid #e5e7eb;
      color: #374151;
      font-size: 14px;
    }

    tbody tr:hover {
      background-color: #f9fafb;
      transition: background-color 0.2s ease;
    }

    .no-data {
      text-align: center;
      padding: 40px;
      color: #6b7280;
      font-size: 16px;
    }

    @media (max-width: 768px) {
      .filter-form { flex-direction: column; }
      .filter-group { width: 100%; }
      th, td { padding: 10px 8px; font-size: 13px; }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header-section">
      <h1><i class="bi bi-graph-up"></i> Reportes del Sistema</h1>
      <div class="action-bar">
        <a href="export_pdf.php?type=<?= $type ?>" class="btn btn-primary"><i class="bi bi-filetype-pdf"></i> Exportar a PDF</a>
        <a href="export_excel.php?type=<?= $type ?>" class="btn btn-primary"><i class="bi bi-filetype-xls"></i> Exportar a Excel</a>
        <a href="../dashboard.php" class="btn btn-secondary">← Volver al Dashboard</a>
      </div>
    </div>

    <div class="filter-card">
      <form method="GET" class="filter-form">
        <div class="filter-group">
          <label>Selecciona el tipo de reporte</label>
          <select name="type" onchange="this.form.submit()">
            <option value="properties" <?= $type === 'properties' ? 'selected' : '' ?>>Propiedades</option>
            <option value="reservations" <?= $type === 'reservations' ? 'selected' : '' ?>>Reservas</option>
            <option value="payments" <?= $type === 'payments' ? 'selected' : '' ?>>Pagos</option>
            <option value="cleanings" <?= $type === 'cleanings' ? 'selected' : '' ?>>Limpiezas</option>
            <?php if ($isAdmin): ?>
              <option value="activity_log" <?= $type === 'activity_log' ? 'selected' : '' ?>>Registro de Actividades</option>
            <?php endif; ?>
          </select>
        </div>
      </form>
    </div>

    <div class="table-card">
      <?php if (count($data) > 0): ?>
        <table>
          <thead>
            <tr>
              <?php foreach (array_keys($data[0] ?? []) as $col): ?>
                <th><?= htmlspecialchars(strtoupper($col)) ?></th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($data as $row): ?>
              <tr>
                <?php foreach ($row as $value): ?>
                  <td><?= htmlspecialchars($value) ?></td>
                <?php endforeach; ?>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <div class="no-data">📭 No se encontraron datos para este reporte</div>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
