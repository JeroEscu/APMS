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

// Conexión y carga de modelo
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../backend/models/Property.php';
require_once __DIR__ . '/../../backend/models/Owner.php';
require_once __DIR__ . '/../../backend/models/City.php';
require_once __DIR__ . '/../../backend/models/PropertyType.php';

$propertyModel = new Property($pdo);
$property = null;
$ownerModel = new Owner($pdo);
$owners = $ownerModel->getAll();
$cityModel = new City($pdo);
$cities = $cityModel->getAll();
$typeModel = new PropertyType($pdo);
$types = $typeModel->getAll();

if (isset($_GET['id'])) {
    $property = $propertyModel->getById($_GET['id']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $property ? 'Editar' : 'Nueva' ?> Propiedad - HostTrack</title>
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

    input[type="text"],
    input[type="number"],
    textarea,
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
    input[type="number"]:focus,
    textarea:focus,
    select:focus {
      outline: none;
      border-color: #667eea;
      background-color: white;
      box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    textarea {
      min-height: 100px;
      resize: vertical;
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
      <h1><i class="bi bi-house-door"></i> <?= $property ? 'Editar' : 'Nueva' ?> Propiedad</h1>
    </div>

    <div class="form-card">
      <form method="POST" action="../../backend/controllers/PropertyController.php">
        <?php if ($property): ?>
          <input type="hidden" name="id" value="<?= $property['id'] ?>">
        <?php endif; ?>

        <div class="form-group full-width">
          <label>Título *</label>
          <input type="text" name="title" value="<?= htmlspecialchars($property['title'] ?? '') ?>" required>
        </div>

        <div class="form-group full-width">
          <label>Descripción</label>
          <textarea name="description"><?= htmlspecialchars($property['description'] ?? '') ?></textarea>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Propietario *</label>
            <select name="owner_id" required>
              <option value="">Seleccionar propietario...</option>
              <?php foreach ($owners as $o): ?>
                <option value="<?= $o['id'] ?>" <?= ($property && $property['owner_id'] == $o['id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($o['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label>Ciudad *</label>
            <select name="city_id" required>
              <option value="">Seleccionar ciudad...</option>
              <?php foreach ($cities as $c): ?>
                <option value="<?= $c['id'] ?>" <?= ($property && $property['city_id'] == $c['id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($c['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Tipo de Propiedad *</label>
            <select name="type_id" required>
              <option value="">Seleccionar tipo...</option>
              <?php foreach ($types as $t): ?>
                <option value="<?= $t['id'] ?>" <?= ($property && $property['type_id'] == $t['id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($t['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label>Dirección</label>
            <input type="text" name="address" value="<?= htmlspecialchars($property['address'] ?? '') ?>">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Precio por noche *</label>
            <input type="number" step="0.01" name="nightly_price" value="<?= htmlspecialchars($property['nightly_price'] ?? '') ?>" required>
          </div>

          <div class="form-group">
            <label>Costo de limpieza *</label>
            <input type="number" step="0.01" name="cleaning_cost" value="<?= htmlspecialchars($property['cleaning_cost'] ?? '') ?>" required>
          </div>
        </div>

        <div class="form-group full-width">
          <label>Capacidad máxima *</label>
          <input type="number" name="max_guests" value="<?= htmlspecialchars($property['max_guests'] ?? '') ?>" required>
        </div>

        <div class="button-group">
          <a href="PropertiesList.php" class="back-link">← Volver</a>
          <button type="submit"><i class="bi bi-floppy"></i> Guardar Propiedad</button>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
