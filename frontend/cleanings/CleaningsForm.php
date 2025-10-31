<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../backend/models/Cleaning.php';
require_once __DIR__ . '/../../backend/models/CleaningResponsible.php';

$cleaningModel = new Cleaning($pdo);
$cleaning = null;
$reservations = $cleaningModel->getActiveReservationsWithProperty();
$properties = $cleaningModel->getActiveProperties();
$responsibleModel = new CleaningResponsible($pdo);
$responsibles = $responsibleModel->getAll();

if (isset($_GET['id'])) {
    $cleaning = $cleaningModel->getById($_GET['id']);
}

$user_id = $_SESSION['user_id'];
$role_id = $_SESSION['role_id'];
$assigned_responsible = null;

if ($role_id == 3) {
    $assigned_responsible = $responsibleModel->getByUserId($user_id);
    if ($cleaning && $cleaning['responsible_id'] != $assigned_responsible['id']) {
        die("No tienes permiso para editar esta limpieza.");
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $cleaning ? 'Editar' : 'Nueva' ?> Limpieza - HostTrack</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <style>
    * {
      margin: 0; padding: 0; box-sizing: border-box;
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
    input[type="text"], input[type="date"], input[type="number"], select, textarea {
      width: 100%;
      padding: 12px 15px;
      border: 2px solid #e5e7eb;
      border-radius: 10px;
      font-size: 15px;
      transition: all 0.3s ease;
      background-color: #f9fafb;
      resize: none;
    }
    input[type="text"]:focus, input[type="date"]:focus, input[type="number"]:focus, select:focus, textarea:focus {
      outline: none;
      border-color: #667eea;
      background-color: white;
      box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    input[readonly] {
      background-color: #e5e7eb;
      cursor: not-allowed;
    }
    select { cursor: pointer; }
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
      .form-row { grid-template-columns: 1fr; }
      .button-group { flex-direction: column-reverse; }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header-card">
      <h1><i class="bi bi-bucket"></i> <?= $cleaning ? 'Editar' : 'Nueva' ?> Limpieza</h1>
    </div>

    <div class="form-card">
      <form method="POST" action="../../backend/controllers/CleaningController.php">
        <?php if ($cleaning): ?>
          <input type="hidden" name="id" value="<?= $cleaning['id'] ?>">
        <?php endif; ?>

        <div class="form-group">
          <label>Reserva *</label>
          <select name="reservation_id" id="reservation_id" required>
            <option value="">Seleccionar...</option>
            <?php foreach ($reservations as $r): ?>
              <option 
                value="<?= $r['id'] ?>"
                data-property="<?= htmlspecialchars($r['property_name']) ?>"
                data-cost="<?= htmlspecialchars($r['cleaning_cost']) ?>"
                <?= ($cleaning && $cleaning['reservation_id'] == $r['id']) ? 'selected' : '' ?>>
                #<?= $r['id'] ?> — Check out: <?= htmlspecialchars($r['end_date']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label>Propiedad</label>
          <input 
            type="text"
            id="property_name"
            name="property_name"
            value="<?= isset($cleaning) ? htmlspecialchars($cleaning['property_name'] ?? '') : '' ?>"
            readonly
            required>
        </div>

        <div class="form-group">
          <label>Fecha de limpieza *</label>
          <input type="date" name="cleaning_date" value="<?= $cleaning['cleaning_date'] ?? '' ?>" required>
        </div>

        <?php if ($_SESSION['role_id'] != 3): ?>
          <div class="form-group">
            <label>Responsable *</label>
            <select name="responsible_id" required>
              <option value="">Seleccionar...</option>
              <?php foreach ($responsibles as $r): ?>
                <option value="<?= $r['id'] ?>" <?= ($cleaning && $cleaning['responsible_id'] == $r['id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($r['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        <?php else: ?>
          <div class="form-group">
            <label>Responsable</label>
            <input type="hidden" name="responsible_id" value="<?= htmlspecialchars($assigned_responsible['id'] ?? '') ?>">
            <input type="text" value="<?= htmlspecialchars($assigned_responsible['name'] ?? 'No asignado') ?>" readonly>
          </div>
        <?php endif; ?>

        <div class="form-group full-width">
          <label>Observaciones</label>
          <textarea name="observations" rows="3"><?= $cleaning['observations'] ?? '' ?></textarea>
        </div>

        <div class="form-group">
          <label>Costo</label>
          <input 
            type="number"
            step="0.01"
            id="cleaning_cost"
            name="cleaning_cost"
            value="<?= $cleaning['cost'] ?? '' ?>"
            readonly
            required>
        </div>

        <div class="button-group">
          <a href="CleaningsList.php" class="back-link">← Volver</a>
          <button type="submit"><i class="bi bi-floppy"></i> Guardar Limpieza</button>
        </div>
      </form>
    </div>
  </div>

  <script>
  document.addEventListener('DOMContentLoaded', () => {
    const reservationSelect = document.getElementById('reservation_id');
    const propertyInput = document.getElementById('property_name');
    const costInput = document.getElementById('cleaning_cost');

    function updateProperty() {
      const selectedOption = reservationSelect.options[reservationSelect.selectedIndex];
      propertyInput.value = selectedOption?.dataset.property || '';
    }

    function updateCost() {
      const selectedOption = reservationSelect.options[reservationSelect.selectedIndex];
      costInput.value = selectedOption?.dataset.cost || '';
    }

    reservationSelect.addEventListener('change', () => {
      updateProperty();
      updateCost();
    });

    updateProperty();
  });
  </script>
</body>
</html>
