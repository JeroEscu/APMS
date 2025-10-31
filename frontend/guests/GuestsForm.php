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
require_once __DIR__ . '/../../backend/models/Guest.php';

$guestModel = new Guest($pdo);
$guest = null;

if (isset($_GET['id'])) {
    $guest = $guestModel->getById($_GET['id']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $guest ? 'Editar' : 'Nuevo' ?> Huésped - HostTrack</title>
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
      max-width: 700px;
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
    input[type="email"],
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
    input[type="email"]:focus,
    select:focus {
      outline: none;
      border-color: #667eea;
      background-color: white;
      box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
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
      <h1><i class="bi bi-person"></i> <?= $guest ? 'Editar' : 'Nuevo' ?> Huésped</h1>
    </div>

    <div class="form-card">
      <form method="POST" action="../../backend/controllers/GuestController.php">
        <?php if ($guest): ?>
          <input type="hidden" name="id" value="<?= $guest['id'] ?>">
        <?php endif; ?>

        <div class="form-group">
          <label>Nombre completo *</label>
          <input type="text" name="name" value="<?= $guest['name'] ?? '' ?>" required placeholder="Ej: Juan Pérez García">
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Correo electrónico *</label>
            <input type="email" name="email" value="<?= $guest['email'] ?? '' ?>" required placeholder="correo@ejemplo.com">
          </div>

          <div class="form-group">
            <label>Teléfono *</label>
            <input type="text" name="phone" value="<?= $guest['phone'] ?? '' ?>" required placeholder="Ej: 3001234567">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Tipo de documento *</label>
            <select name="document_type" required>
              <option value="">Seleccionar...</option>
              <option value="CC" <?= isset($guest['document_type']) && $guest['document_type'] === 'CC' ? 'selected' : '' ?>>Cédula de Ciudadanía</option>
              <option value="CE" <?= isset($guest['document_type']) && $guest['document_type'] === 'CE' ? 'selected' : '' ?>>Cédula Extranjera</option>
              <option value="PAS" <?= isset($guest['document_type']) && $guest['document_type'] === 'PAS' ? 'selected' : '' ?>>Pasaporte</option>
            </select>
          </div>

          <div class="form-group">
            <label>Número de documento *</label>
            <input type="text" name="document_number" value="<?= $guest['document_number'] ?? '' ?>" required placeholder="Ej: 1234567890">
          </div>
        </div>

        <div class="button-group">
          <a href="GuestsList.php" class="back-link">← Volver</a>
          <button type="submit"><i class="bi bi-floppy"></i> Guardar Huésped</button>
        </div>
      </form>
    </div>
  </div>
</body>
</html>