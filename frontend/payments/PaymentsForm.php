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

$paymentModel = new Payment($pdo);
$payment = null;
$reservations = $paymentModel->getActiveReservationsWithCost();

if (isset($_GET['id'])) {
    $payment = $paymentModel->getById($_GET['id']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $payment ? 'Editar' : 'Nuevo' ?> Pago - HostTrack</title>
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
      max-width: 900px;
      margin: 0 auto;
    }
    .header-card {
      background: white;
      border-radius: 20px;
      padding: 30px;
      margin-bottom: 30px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
      animation: fadeIn 0.5s ease;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .header-card h1 {
      color: #1f2937;
      font-size: 28px;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .form-card {
      background: white;
      border-radius: 20px;
      padding: 35px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
      animation: fadeIn 0.7s ease;
    }
    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
      margin-bottom: 20px;
    }
    .form-group {
      margin-bottom: 20px;
    }
    .form-group.full-width {
      grid-column: 1 / -1;
    }
    label {
      display: block;
      color: #374151;
      font-weight: 600;
      margin-bottom: 8px;
      font-size: 14px;
    }
    input[type="text"], input[type="date"], input[type="number"], select {
      width: 100%;
      padding: 12px 15px;
      border: 2px solid #e5e7eb;
      border-radius: 10px;
      font-size: 15px;
      transition: all 0.3s ease;
      background-color: #f9fafb;
    }
    input[type="text"]:focus, input[type="date"]:focus, input[type="number"]:focus, select:focus {
      outline: none;
      border-color: #667eea;
      background-color: white;
      box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    input[readonly] {
      background-color: #e5e7eb;
      cursor: not-allowed;
    }
    select {
      cursor: pointer;
    }
    .button-group {
      display: flex;
      gap: 15px;
      margin-top: 30px;
    }
    button[type="submit"] {
      flex: 1;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 14px;
      border: none;
      border-radius: 10px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    button[type="submit"]:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
    }
    .back-link {
      flex: 1;
      display: inline-block;
      text-align: center;
      background: #f3f4f6;
      color: #374151;
      padding: 14px;
      border-radius: 10px;
      text-decoration: none;
      font-weight: 600;
      font-size: 16px;
      transition: all 0.3s ease;
    }
    .back-link:hover {
      background: #e5e7eb;
      transform: translateY(-2px);
    }
    @media (max-width: 768px) {
      .form-row {
        grid-template-columns: 1fr;
      }
      .button-group {
        flex-direction: column-reverse;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header-card">
      <h1><i class="bi bi-cash-coin"></i> <?= $payment ? 'Editar' : 'Nuevo' ?> Pago</h1>
    </div>

    <div class="form-card">
      <form method="POST" action="../../backend/controllers/PaymentController.php">
        <?php if ($payment): ?>
          <input type="hidden" name="id" value="<?= $payment['id'] ?>">
        <?php endif; ?>

        <div class="form-group">
          <label>Reserva *</label>
          <select name="reservation_id" id="reservation_id" required>
            <option value="">Seleccionar...</option>
            <?php foreach ($reservations as $r): ?>
              <option 
                value="<?= $r['id'] ?>" 
                data-amount="<?= htmlspecialchars($r['total_cost']) ?>"
                <?= ($payment && $payment['reservation_id'] == $r['id']) ? 'selected' : '' ?>>
                #<?= $r['id'] ?> — <?= htmlspecialchars($r['guest_name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Monto *</label>
            <input 
              type="number" 
              step="0.01" 
              id="amount" 
              name="amount" 
              value="<?= $payment['amount'] ?? '' ?>" 
              readonly
              required>
          </div>

          <div class="form-group">
            <label>Fecha del pago *</label>
            <input type="date" name="payment_date" value="<?= $payment['payment_date'] ?? '' ?>" required>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Método *</label>
            <select name="method" required>
              <?php
              $methods = ['cash', 'card', 'transfer', 'other'];
              foreach ($methods as $m) {
                  $selected = ($payment && $payment['method'] === $m) ? 'selected' : '';
                  echo "<option value='$m' $selected>" . ucfirst(str_replace('_', ' ', $m)) . "</option>";
              }
              ?>
            </select>
          </div>

          <div class="form-group">
            <label>Estado *</label>
            <select name="status" required>
              <?php
              $statuses = ['pending', 'completed', 'refunded'];
              foreach ($statuses as $s) {
                  $selected = ($payment && $payment['status'] === $s) ? 'selected' : '';
                  echo "<option value='$s' $selected>" . ucfirst($s) . "</option>";
              }
              ?>
            </select>
          </div>
        </div>

        <div class="button-group">
          <a href="PaymentsList.php" class="back-link">← Volver</a>
          <button type="submit"><i class="bi bi-floppy"></i> Guardar Pago</button>
        </div>
      </form>
    </div>
  </div>

  <script>
  document.addEventListener('DOMContentLoaded', () => {
    const reservationSelect = document.getElementById('reservation_id');
    const amountInput = document.getElementById('amount');

    function updateAmount() {
      const selectedOption = reservationSelect.options[reservationSelect.selectedIndex];
      if (selectedOption && selectedOption.dataset.amount) {
        amountInput.value = parseFloat(selectedOption.dataset.amount).toFixed(2);
      } else {
        amountInput.value = '';
      }
    }

    reservationSelect.addEventListener('change', updateAmount);

    // Si el formulario está en modo edición, cargar el valor inicial
    updateAmount();
  });
  </script>
</body>
</html>
