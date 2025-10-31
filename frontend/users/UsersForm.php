<?php
session_start();
// Si no hay sesión, redirigir
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}
// Restricción de acceso según rol
if ($_SESSION['role_id'] != 1) {
    die("No tienes permiso para acceder a esta página.");
}

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../backend/models/User.php';

$userModel = new User($pdo);
$user = null;

if (isset($_GET['id'])) {
    $user = $userModel->getById($_GET['id']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $user ? 'Editar Usuario' : 'Nuevo Usuario' ?> - HostTrack</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #667eea;
      min-height: 100vh;
      padding: 40px 20px;
    }
    .container { max-width: 800px; margin: 0 auto; }
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
    .form-group { margin-bottom: 20px; }
    label {
      display: block;
      color: #374151;
      font-weight: 600;
      margin-bottom: 8px;
      font-size: 14px;
    }
    input[type="text"], input[type="password"], input[type="email"], select {
      width: 100%;
      padding: 12px 15px;
      border: 2px solid #e5e7eb;
      border-radius: 10px;
      font-size: 15px;
      transition: all 0.3s ease;
      background-color: #f9fafb;
    }
    input:focus, select:focus {
      outline: none;
      border-color: #667eea;
      background-color: white;
      box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
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
      .button-group { flex-direction: column-reverse; }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header-card">
      <h1><i class="bi bi-person-gear"></i> <?= $user ? 'Editar Usuario' : 'Nuevo Usuario' ?></h1>
    </div>

    <div class="form-card">
      <form method="POST" action="../../backend/controllers/UserController.php">
        <?php if ($user): ?>
          <input type="hidden" name="id" value="<?= $user['id'] ?>">
        <?php endif; ?>

        <div class="form-group">
          <label>Nombre de usuario *</label>
          <input type="text" name="username" value="<?= htmlspecialchars($user['username'] ?? '') ?>" required>
        </div>

        <div class="form-group">
          <label>Contraseña <?= $user ? '(dejar en blanco para no cambiar)' : '*' ?></label>
          <input type="password" name="password" placeholder="<?= $user ? 'Dejar en blanco para no cambiar' : '' ?>">
        </div>

        <div class="form-group">
          <label>Nombre completo *</label>
          <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" required>
        </div>

        <div class="form-group">
          <label>Email *</label>
          <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
        </div>

        <div class="form-group">
          <label>Rol *</label>
          <select name="role_id" required>
            <option value="">Seleccionar...</option>
            <option value="1" <?= ($user && $user['role_id'] == 1) ? 'selected' : '' ?>>Administrador</option>
            <option value="2" <?= ($user && $user['role_id'] == 2) ? 'selected' : '' ?>>Personal Administrativo</option>
            <option value="3" <?= ($user && $user['role_id'] == 3) ? 'selected' : '' ?>>Personal de Limpieza</option>
          </select>
        </div>

        <div class="button-group">
          <a href="UsersList.php" class="back-link">← Volver</a>
          <button type="submit"><i class="bi bi-floppy"></i> Guardar Usuario</button>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
