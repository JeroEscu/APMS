<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['username'];
$roleName = $_SESSION['role'];
$roleId = $_SESSION['role_id'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel Principal - HostTrack</title>
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

    .dashboard-container {
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
      margin-bottom: 10px;
    }

    .role-badge {
      display: inline-block;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 6px 16px;
      border-radius: 20px;
      font-size: 14px;
      font-weight: 600;
    }

    .menu-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
      animation: fadeIn 0.7s ease;
    }

    .menu-item {
      background: white;
      border-radius: 15px;
      padding: 25px;
      text-decoration: none;
      color: #1f2937;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .menu-item:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
    }

    .menu-icon {
      font-size: 32px;
      min-width: 40px;
    }

    .menu-text {
      font-size: 16px;
      font-weight: 600;
    }

    .logout-item {
      background: #ef4444;
      color: white;
    }

    .logout-item:hover {
      background: #dc2626;
      color: white;
    }

    hr {
      border: none;
      border-top: 2px solid #e5e7eb;
      margin: 20px 0;
    }

    @media (max-width: 768px) {
      .dashboard-container {
        padding: 20px;
      }
      
      .menu-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <div class="dashboard-container">
    <div class="header-card">
      <h1>👋 Bienvenido/a, <?= htmlspecialchars($user) ?></h1>
      <span class="role-badge"><?= htmlspecialchars($roleName) ?></span>
    </div>

    <div class="menu-grid">
      <?php if ($roleId == 1 || $roleId == 2): ?>
        <a href="properties/PropertiesList.php" class="menu-item">
          <span class="menu-icon"><i class="bi bi-house-door"></i></span>
          <span class="menu-text">Propiedades</span>
        </a>

        <a href="owners/OwnersList.php" class="menu-item">
          <span class="menu-icon"><i class="bi bi-briefcase"></i></span>
          <span class="menu-text">Propietarios</span>
        </a>

        <a href="guests/GuestsList.php" class="menu-item">
          <span class="menu-icon"><i class="bi bi-person"></i></span>
          <span class="menu-text">Huéspedes</span>
        </a>

        <a href="reservations/ReservationsList.php" class="menu-item">
          <span class="menu-icon"><i class="bi bi-calendar-week"></i></span>
          <span class="menu-text">Reservas</span>
        </a>

        <a href="payments/PaymentsList.php" class="menu-item">
          <span class="menu-icon"><i class="bi bi-cash-coin"></i></span>
          <span class="menu-text">Pagos</span>
        </a>

        <a href="cleanings/CleaningsList.php" class="menu-item">
          <span class="menu-icon"><i class="bi bi-bucket"></i></span>
          <span class="menu-text">Limpiezas</span>
        </a>

        <a href="reports/ReportsList.php" class="menu-item">
          <span class="menu-icon"><i class="bi bi-graph-up"></i></span>
          <span class="menu-text">Reportes</span>
        </a>

        <?php if ($roleId == 1): ?>
          <a href="users/UsersList.php" class="menu-item">
            <span class="menu-icon"><i class="bi bi-person-gear"></i></span>
            <span class="menu-text">Gestión de Usuarios</span>
          </a>
        <?php endif; ?>

      <?php elseif ($roleId == 3): ?>
        <a href="cleanings/CleaningsList.php" class="menu-item">
          <span class="menu-icon"><i class="bi bi-bucket"></i></span>
          <span class="menu-text">Limpiezas Asignadas</span>
        </a>
      <?php endif; ?>

      <a href="logout.php" class="menu-item logout-item">
        <span class="menu-icon"><i class="bi bi-box-arrow-left"></i></span>
        <span class="menu-text">Cerrar Sesión</span>
      </a>
    </div>
  </div>
</body>
</html>