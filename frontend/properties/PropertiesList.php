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

$propertyModel = new Property($pdo);
$properties = $propertyModel->getAll();

$ownerModel = new Owner($pdo);
$cityModel = new City($pdo);

$owners = $ownerModel->getAll();
$cities = $cityModel->getAll();

// Aplicar filtros
$filters = [
  'owner_id' => $_GET['owner_id'] ?? null,
  'city_id' => $_GET['city_id'] ?? null,
  'type' => $_GET['type'] ?? null,
  'order' => $_GET['order'] ?? null
];

$properties = $propertyModel->getFiltered($filters);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Listado de Propiedades - HostTrack</title>
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

    .btn-secondary:hover { background: #e5e7eb; }

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

    .filter-group select {
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

    .filter-actions { display: flex; gap: 10px; }

    .table-card {
      background: white;
      border-radius: 20px;
      padding: 25px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
      animation: fadeIn 0.7s ease;
      overflow-x: auto;
    }

    table { width: 100%; border-collapse: collapse; }

    thead { background: #667eea; }

    th {
      padding: 15px 10px;
      text-align: left;
      color: white;
      font-weight: 600;
      font-size: 14px;
      white-space: nowrap;
    }

    th:first-child {
      border-top-left-radius: 10px;
    }

    th:last-child {
      border-top-right-radius: 10px;
    }

    td {
      padding: 15px 10px;
      border-bottom: 1px solid #e5e7eb;
      color: #374151;
      font-size: 14px;
    }

    tbody tr:hover { background-color: #f9fafb; }

    .action-links { display: flex; gap: 10px; flex-wrap: wrap; }

    .action-link {
      color: #667eea;
      text-decoration: none;
      font-weight: 600;
      font-size: 13px;
      transition: color 0.3s ease;
    }

    .action-link:hover { color: #764ba2; text-decoration: underline; }

    .action-link.delete { color: #ef4444; }

    .action-link.delete:hover { color: #dc2626; }

    .no-data {
      text-align: center;
      padding: 40px;
      color: #6b7280;
      font-size: 16px;
    }

    @media (max-width: 768px) {
      .filter-form { grid-template-columns: 1fr; }
      th, td { padding: 10px 8px; font-size: 12px; }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header-section">
      <h1><i class="bi bi-house-door"></i> Listado de Propiedades</h1>
      <div class="action-bar">
        <a href="PropertiesForm.php" class="btn btn-primary">+ Nueva Propiedad</a>
        <a href="../dashboard.php" class="btn btn-secondary">← Volver al Dashboard</a>
      </div>
    </div>

    <div class="filter-card">
      <form method="GET" action="" class="filter-form">
        <div class="filter-group">
          <label>Propietario</label>
          <select name="owner_id">
            <option value="">Todos</option>
            <?php foreach ($owners as $o): ?>
              <option value="<?= $o['id'] ?>" <?= ($_GET['owner_id'] ?? '') == $o['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($o['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="filter-group">
          <label>Ciudad</label>
          <select name="city_id">
            <option value="">Todas</option>
            <?php foreach ($cities as $c): ?>
              <option value="<?= $c['id'] ?>" <?= ($_GET['city_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($c['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="filter-group">
          <label>Tipo</label>
          <select name="type">
            <option value="">Todos</option>
            <option value="Apartamento" <?= ($_GET['type'] ?? '') == 'Apartamento' ? 'selected' : '' ?>>Apartamento</option>
            <option value="Casa" <?= ($_GET['type'] ?? '') == 'Casa' ? 'selected' : '' ?>>Casa</option>
            <option value="Cabaña" <?= ($_GET['type'] ?? '') == 'Cabaña' ? 'selected' : '' ?>>Cabaña</option>
          </select>
        </div>

        <div class="filter-group">
          <label>Ordenar por</label>
          <select name="order">
            <option value="id" <?= ($_GET['order'] ?? '') == 'id' ? 'selected' : '' ?>>ID</option>
            <option value="title" <?= ($_GET['order'] ?? '') == 'title' ? 'selected' : '' ?>>Título</option>
          </select>
        </div>

        <div class="filter-actions">
          <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Filtrar</button>
          <a href="PropertiesList.php" class="btn btn-secondary">Limpiar</a>
        </div>
      </form>
    </div>

    <div class="table-card">
      <?php if (count($properties) > 0): ?>
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Título</th>
              <th>Propietario</th>
              <th>Ciudad</th>
              <th>Tipo</th>
              <th>Precio/Noche</th>
              <th>Costo de Limpieza</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($properties as $p): ?>
            <tr>
              <td><?= $p['id'] ?></td>
              <td><?= htmlspecialchars($p['title']) ?></td>
              <td><?= htmlspecialchars($p['owner_name']) ?></td>
              <td><?= htmlspecialchars($p['city']) ?></td>
              <td><?= htmlspecialchars($p['type']) ?></td>
              <td>$<?= number_format($p['nightly_price'], 2) ?></td>
              <td>$<?= number_format($p['cleaning_cost'], 2) ?></td>
              <td>
                <div class="action-links">
                  <a href="PropertiesForm.php?id=<?= $p['id'] ?>" class="action-link"><i class="bi bi-pencil"></i> Editar</a>
                  <a href="../../backend/controllers/PropertyController.php?delete=<?= $p['id'] ?>"
                    class="action-link delete"
                    onclick="return confirm('¿Estás seguro de que deseas eliminar esta propiedad? Las reservas futuras serán canceladas.');">
                    <i class="bi bi-trash"></i> Eliminar
                  </a>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <div class="no-data">📭 No se encontraron propiedades</div>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
