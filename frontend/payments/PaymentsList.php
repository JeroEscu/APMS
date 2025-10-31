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
require_once __DIR__ . '/../../backend/models/Payment.php';
require_once __DIR__ . '/../../backend/models/Property.php';

$paymentModel = new Payment($pdo);
$propertyModel = new Property($pdo);
$properties = $propertyModel->getAll();

$filters = [
    'property_id' => $_GET['property_id'] ?? null,
    'method' => $_GET['method'] ?? null,
    'status' => $_GET['status'] ?? null,
    'date_from' => $_GET['date_from'] ?? null,
    'date_to' => $_GET['date_to'] ?? null,
    'order' => $_GET['order'] ?? null
];

$payments = $paymentModel->getFiltered($filters);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Listado de Pagos - HostTrack</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #667eea;
      min-height: 100vh;
      padding: 40px 20px;
    }
    .container { max-width: 1400px; margin: 0 auto; }

    /* HEADER */
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

    /* FILTROS */
    .filter-card {
      background: white;
      border-radius: 20px;
      padding: 25px;
      margin-bottom: 25px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
      animation: fadeIn 0.6s ease;
    }
    .filter-form {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap: 15px;
      align-items: end;
    }
    .filter-group {
      display: flex;
      flex-direction: column;
    }
    .filter-group label {
      color: #374151;
      font-weight: 600;
      margin-bottom: 8px;
      font-size: 14px;
    }
    .filter-group select,
    .filter-group input[type="date"] {
      padding: 10px 12px;
      border: 2px solid #e5e7eb;
      border-radius: 8px;
      font-size: 14px;
      background-color: #f9fafb;
      transition: all 0.3s ease;
    }
    .filter-group select:focus,
    .filter-group input[type="date"]:focus {
      outline: none;
      border-color: #667eea;
      background-color: white;
    }
    .filter-actions {
      display: flex;
      gap: 10px;
    }

    /* TABLA */
    .table-card {
      background: white;
      border-radius: 20px;
      padding: 25px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
      animation: fadeIn 0.7s ease;
      overflow-x: auto;
    }
    table { width: 100%; border-collapse: collapse; }
    thead {
      background: #667eea;
    }
    th {
      padding: 15px 10px;
      text-align: left;
      color: white;
      font-weight: 600;
      font-size: 14px;
      white-space: nowrap;
    }
    th:first-child { border-top-left-radius: 10px; }
    th:last-child { border-top-right-radius: 10px; }
    td {
      padding: 15px 10px;
      border-bottom: 1px solid #e5e7eb;
      color: #374151;
      font-size: 14px;
      text-align: left;
    }
    tr:last-child td { border-bottom: none; }
    tbody tr { transition: background-color 0.2s ease; }
    tbody tr:hover { background-color: #f9fafb; }

    .action-links {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }
    .action-link {
      color: #667eea;
      text-decoration: none;
      font-weight: 600;
      font-size: 13px;
      transition: color 0.3s ease;
      white-space: nowrap;
    }
    .action-link:hover {
      color: #764ba2;
      text-decoration: underline;
    }
    .action-link.delete { color: #ef4444; }
    .action-link.delete:hover { color: #dc2626; }
    .no-data {
      text-align: center;
      padding: 40px;
      color: #6b7280;
      font-size: 16px;
    }

    @media (max-width: 1024px) {
      .filter-form { grid-template-columns: 1fr 1fr; }
      .filter-actions { grid-column: 1 / -1; }
    }
    @media (max-width: 768px) {
      .filter-form { grid-template-columns: 1fr; }
      .table-card { padding: 15px; }
      th, td { padding: 10px 8px; font-size: 12px; }
    }
  </style>
</head>
<body>
  <div class="container">
    <!-- ENCABEZADO -->
    <div class="header-section">
      <h1><i class="bi bi-cash-coin"></i> Listado de Pagos</h1>
      <div class="action-bar">
        <a href="PaymentsForm.php" class="btn btn-primary">+ Nuevo Pago</a>
        <a href="../dashboard.php" class="btn btn-secondary">← Volver al Dashboard</a>
      </div>
    </div>

    <!-- FILTROS -->
    <div class="filter-card">
      <form method="GET" action="" class="filter-form">
        <div class="filter-group">
          <label>Propiedad</label>
          <select name="property_id">
            <option value="">Todas</option>
            <?php foreach ($properties as $p): ?>
              <option value="<?= $p['id'] ?>" <?= ($_GET['property_id'] ?? '') == $p['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($p['title']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="filter-group">
          <label>Método</label>
          <select name="method">
            <option value="">Todos</option>
            <option value="cash" <?= ($_GET['method'] ?? '') == 'cash' ? 'selected' : '' ?>>Efectivo</option>
            <option value="card" <?= ($_GET['method'] ?? '') == 'card' ? 'selected' : '' ?>>Tarjeta</option>
            <option value="transfer" <?= ($_GET['method'] ?? '') == 'transfer' ? 'selected' : '' ?>>Transferencia</option>
          </select>
        </div>

        <div class="filter-group">
          <label>Estado</label>
          <select name="status">
            <option value="">Todos</option>
            <option value="pending" <?= ($_GET['status'] ?? '') == 'pending' ? 'selected' : '' ?>>Pendiente</option>
            <option value="completed" <?= ($_GET['status'] ?? '') == 'completed' ? 'selected' : '' ?>>Completado</option>
            <option value="refunded" <?= ($_GET['status'] ?? '') == 'refunded' ? 'selected' : '' ?>>Reembolso</option>
          </select>
        </div>

        <div class="filter-group">
          <label>Desde</label>
          <input type="date" name="date_from" value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>">
        </div>

        <div class="filter-group">
          <label>Hasta</label>
          <input type="date" name="date_to" value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>">
        </div>

        <div class="filter-group">
          <label>Ordenar por</label>
          <select name="order">
            <option value="id" <?= ($_GET['order'] ?? '') == 'id' ? 'selected' : '' ?>>ID</option>
            <option value="reservation_code" <?= ($_GET['order'] ?? '') == 'reservation_code' ? 'selected' : '' ?>>Reserva</option>
            <option value="amount" <?= ($_GET['order'] ?? '') == 'amount' ? 'selected' : '' ?>>Monto</option>
            <option value="payment_date" <?= ($_GET['order'] ?? '') == 'payment_date' ? 'selected' : '' ?>>Fecha</option>
          </select>
        </div>

        <div class="filter-actions">
          <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Filtrar</button>
          <a href="PaymentsList.php" class="btn btn-secondary">Limpiar</a>
        </div>
      </form>
    </div>

    <!-- TABLA -->
    <div class="table-card">
      <?php if (count($payments) > 0): ?>
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Reserva</th>
              <th>Propiedad</th>
              <th>Huésped</th>
              <th>Monto</th>
              <th>Fecha</th>
              <th>Método</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($payments as $p): ?>
            <tr>
              <td><?= $p['id'] ?></td>
              <td>#<?= htmlspecialchars($p['reservation_code']) ?></td>
              <td><?= htmlspecialchars($p['property_name']) ?></td>
              <td><?= htmlspecialchars($p['guest_name']) ?></td>
              <td>$<?= number_format($p['amount'], 2) ?></td>
              <td><?= date('d/m/Y', strtotime($p['payment_date'])) ?></td>
              <td><?= ucfirst($p['method']) ?></td>
              <td><?= ucfirst($p['status']) ?></td>
              <td>
                <div class="action-links">
                  <a href="PaymentsForm.php?id=<?= $p['id'] ?>" class="action-link"><i class="bi bi-pencil"></i> Editar</a>
                  <a href="../../backend/controllers/PaymentController.php?delete=<?= $p['id'] ?>" class="action-link delete" onclick="return confirm('¿Eliminar este pago?')"><i class="bi bi-trash"></i> Eliminar</a>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <div class="no-data">📭 No se encontraron pagos</div>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
