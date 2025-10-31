<?php
session_start();
require_once __DIR__ . '/../config/config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $activation_code = trim($_POST['activation_code']);

    $codes = [
        'STAFF2025' => 2,
        'CLEAN2025' => 3
    ];

    if (!isset($codes[$activation_code])) {
        $message = "❌ Código de activación inválido.";
    } elseif (empty($username) || empty($password) || empty($full_name) || empty($email)) {
        $message = "❌ Todos los campos son obligatorios.";
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :u");
        $stmt->execute([':u' => $username]);

        if ($stmt->fetchColumn() > 0) {
            $message = "❌ El nombre de usuario ya está en uso.";
        } else {
            $role_id = $codes[$activation_code];
            $hash = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $pdo->prepare("
                INSERT INTO users (username, password, full_name, email, role_id, created_at, is_active)
                VALUES (:u, :p, :f, :e, :r, NOW(), 1)
            ");
            $stmt->execute([
                ':u' => $username,
                ':p' => $hash,
                ':f' => $full_name,
                ':e' => $email,
                ':r' => $role_id
            ]);

            $user_id = $pdo->lastInsertId();

            if ($role_id == 3) {
                $stmt = $pdo->prepare("
                    INSERT INTO cleaning_responsibles (user_id, name)
                    VALUES (:user_id, :name)
                ");
                $stmt->execute([
                    ':user_id' => $user_id,
                    ':name' => $full_name
                ]);
            }

            $message = "✅ Cuenta creada exitosamente. Ya puedes iniciar sesión.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Crear cuenta - HostTrack</title>
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
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 20px;
    }

    .register-container {
      background: white;
      border-radius: 20px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
      padding: 40px;
      width: 100%;
      max-width: 480px;
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

    h1 {
      color: #1f2937;
      font-size: 26px;
      margin-bottom: 25px;
      text-align: center;
    }

    .message {
      padding: 12px 15px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-size: 14px;
    }

    .message.success {
      background-color: #d1fae5;
      border-left: 4px solid #10b981;
      color: #065f46;
    }

    .message.error {
      background-color: #fee;
      border-left: 4px solid #ef4444;
      color: #dc2626;
    }

    .form-group {
      margin-bottom: 20px;
    }

    label {
      display: block;
      color: #374151;
      font-weight: 600;
      margin-bottom: 8px;
      font-size: 14px;
    }

    input[type="text"],
    input[type="password"],
    input[type="email"] {
      width: 100%;
      padding: 12px 15px;
      border: 2px solid #e5e7eb;
      border-radius: 10px;
      font-size: 15px;
      transition: all 0.3s ease;
      background-color: #f9fafb;
    }

    input[type="text"]:focus,
    input[type="password"]:focus,
    input[type="email"]:focus {
      outline: none;
      border-color: #667eea;
      background-color: white;
      box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    button[type="submit"] {
      width: 100%;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 14px;
      border: none;
      border-radius: 10px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      margin-top: 10px;
    }

    button[type="submit"]:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
    }

    button[type="submit"]:active {
      transform: translateY(0);
    }

    .back-link {
      text-align: center;
      margin-top: 25px;
      padding-top: 25px;
      border-top: 1px solid #e5e7eb;
    }

    .back-link a {
      color: #667eea;
      text-decoration: none;
      font-weight: 600;
      font-size: 14px;
      transition: color 0.3s ease;
    }

    .back-link a:hover {
      color: #764ba2;
      text-decoration: underline;
    }

    .info-box {
      background-color: #eff6ff;
      border-left: 4px solid #3b82f6;
      padding: 12px 15px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-size: 13px;
      color: #1e40af;
    }
  </style>
</head>
<body>
  <div class="register-container">
    <h1>🏠 Registro de Nuevo Empleado</h1>

    <?php if (!empty($message)): ?>
      <div class="message <?= str_starts_with($message, '✅') ? 'success' : 'error' ?>">
        <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>

    <div class="info-box">
      💡 Solicita tu código de activación a tu supervisor
    </div>

    <form method="POST" action="">
      <div class="form-group">
        <label>Nombre de usuario</label>
        <input type="text" name="username" required placeholder="Ej: jperez">
      </div>

      <div class="form-group">
        <label>Contraseña</label>
        <input type="password" name="password" required placeholder="Mínimo 6 caracteres">
      </div>

      <div class="form-group">
        <label>Nombre completo</label>
        <input type="text" name="full_name" required placeholder="Ej: Juan Pérez">
      </div>

      <div class="form-group">
        <label>Correo electrónico</label>
        <input type="email" name="email" required placeholder="tu@email.com">
      </div>

      <div class="form-group">
        <label>Código de activación</label>
        <input type="text" name="activation_code" placeholder="Ej: STAFF2025" required>
      </div>

      <button type="submit">Crear Cuenta</button>
    </form>

    <div class="back-link">
      <a href="login.php">← Volver al inicio de sesión</a>
    </div>
  </div>
</body>
</html>