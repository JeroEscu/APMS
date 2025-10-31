<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}
if ($_SESSION['role_id'] == 3) {
    die("No tienes permiso para acceder a esta página.");
}

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../backend/models/Reservation.php';

$reservationModel = new Reservation($pdo);
$reservation = null;
$properties = $reservationModel->getActiveProperties();
$guests = $reservationModel->getActiveGuests();

if (isset($_GET['id'])) {
    $reservation = $reservationModel->getById($_GET['id']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $reservation ? 'Editar' : 'Nueva' ?> Reserva - HostTrack</title>
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
      from {
        opacity: 0;
        transform: translateY(-20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
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

    input[type="text"],
    input[type="date"],
    input[type="number"],
    select {
      width: 100%;
      padding: 12px 15px;
      border: 2px solid #e5e7eb;
      border-radius: 10px;
      font-size: 15px;
      transition: all 0.3s ease;
      background-color: #f9fafb;
    }

    input[type="text"]:focus,
    input[type="date"]:focus,
    input[type="number"]:focus,
    select:focus {
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

    .info-box {
      background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
      padding: 15px;
      border-radius: 10px;
      margin-bottom: 25px;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 15px;
    }

    .info-item {
      display: flex;
      flex-direction: column;
    }

    .info-label {
      font-size: 12px;
      color: #6b7280;
      font-weight: 600;
      margin-bottom: 5px;
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

      .info-box {
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
      <h1><i class="bi bi-calendar-week"></i> <?= $reservation ? 'Editar' : 'Nueva' ?> Reserva</h1>
    </div>

    <div class="form-card">
      <form method="POST" action="../../backend/controllers/ReservationController.php">
        <?php if ($reservation): ?>
          <input type="hidden" name="id" value="<?= $reservation['id'] ?>">
        <?php endif; ?>

        <div class="form-group">
          <label>Propiedad *</label>
          <select name="property_id" id="property_id" required>
            <option value="">Seleccionar propiedad...</option>
            <?php foreach ($properties as $p): 
                $price = isset($p['nightly_price']) ? (float)$p['nightly_price'] : 0;
            ?>
              <option 
                value="<?= $p['id'] ?>" 
                data-price="<?= htmlspecialchars($price) ?>"
                <?= ($reservation && $reservation['property_id'] == $p['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($p['title']) ?> — $<?= number_format($price, 2) ?>/noche
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label>Huésped *</label>
          <select name="guest_id" required>
            <option value="">Seleccionar huésped...</option>
            <?php foreach ($guests as $g): ?>
              <option value="<?= $g['id'] ?>" <?= ($reservation && $reservation['guest_id'] == $g['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($g['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Fecha de inicio *</label>
            <input type="date" name="start_date" value="<?= $reservation['start_date'] ?? '' ?>" required>
          </div>

          <div class="form-group">
            <label>Fecha de fin *</label>
            <input type="date" name="end_date" value="<?= $reservation['end_date'] ?? '' ?>" required>
          </div>
        </div>

        <div class="info-box">
          <div class="info-item">
            <span class="info-label">NOCHES</span>
            <span id="nights_display">0</span>
          </div>
          <div class="info-item">
            <span class="info-label">COSTO TOTAL</span>
            <span id="total_display">$0.00</span>
          </div>
        </div>

        <input type="hidden" id="nights" name="nights" value="<?= $reservation['nights'] ?? '' ?>">
        <input type="hidden" id="total_cost" name="total_cost" value="<?= $reservation['total_cost'] ?? '' ?>">

        <div class="form-group">
          <label>Estado *</label>
          <select name="status" required>
            <?php
            $statuses = [
              'confirmed' => 'Confirmada',
              'checked_in' => 'En curso',
              'completed' => 'Completada',
              'cancelled' => 'Cancelada'
            ];
            foreach ($statuses as $value => $label) {
              $selected = ($reservation && $reservation['status'] === $value) ? 'selected' : '';
              echo "<option value='$value' $selected>$label</option>";
            }
            ?>
          </select>
        </div>

        <div class="button-group">
          <a href="ReservationsList.php" class="back-link">← Volver</a>
          <button type="submit"><i class="bi bi-floppy"></i> Guardar Reserva</button>
        </div>
      </form>
    </div>
  </div>

  <script>
  document.addEventListener('DOMContentLoaded', () => {
    const startDateInput = document.querySelector('input[name="start_date"]');
    const endDateInput = document.querySelector('input[name="end_date"]');
    const propertySelect = document.getElementById('property_id');
    const nightsInput = document.getElementById('nights');
    const totalCostInput = document.getElementById('total_cost');
    const nightsDisplay = document.getElementById('nights_display');
    const totalDisplay = document.getElementById('total_display');

    function calculateValues() {
      const start = new Date(startDateInput.value);
      const end = new Date(endDateInput.value);

      if (isNaN(start) || isNaN(end) || end <= start) {
        nightsInput.value = '';
        totalCostInput.value = '';
        nightsDisplay.textContent = '0';
        totalDisplay.textContent = '$0.00';
        return;
      }

      // Calcular noches
      const diffTime = end - start;
      const nights = diffTime / (1000 * 60 * 60 * 24);
      nightsInput.value = nights;
      nightsDisplay.textContent = nights;

      // Obtener precio desde la propiedad seleccionada
      const selectedOption = propertySelect.options[propertySelect.selectedIndex];
      const price = parseFloat(selectedOption.dataset.price || 0);

      if (price > 0) {
        const total = (nights * price).toFixed(2);
        totalCostInput.value = total;
        totalDisplay.textContent = '$' + parseFloat(total).toLocaleString('es-CO', {
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
        });
      } else {
        totalCostInput.value = '';
        totalDisplay.textContent = '$0.00';
      }
    }

    startDateInput.addEventListener('change', calculateValues);
    endDateInput.addEventListener('change', calculateValues);
    propertySelect.addEventListener('change', calculateValues);

    // Calcular al cargar si hay datos
    if (startDateInput.value && endDateInput.value) {
      calculateValues();
    }
  });
  </script>
</body>
</html>