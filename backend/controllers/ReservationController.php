<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../frontend/login.php');
    exit;
}

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../models/Reservation.php';
require_once __DIR__ . '/../models/Property.php';
require_once __DIR__ . '/../helpers/Logger.php';

$reservationModel = new Reservation($pdo);
$propertyModel = new Property($pdo);

// Guardar o editar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Obtener precio de la propiedad
    $property = $propertyModel->getById($_POST['property_id']);
    $price = $property['nightly_price'] ?? 0;

    // Calcular noches
    $start = new DateTime($_POST['start_date']);
    $end = new DateTime($_POST['end_date']);
    $interval = $start->diff($end);
    $_POST['nights'] = $interval->days;

    // Calcular costo total
    $_POST['total_cost'] = $interval->days * $price;

    // Guardar o actualizar según el caso
    if (!empty($_POST['id'])) {
        $reservationModel->update($_POST['id'], $_POST);
        Logger::log($pdo, $_SESSION['user_id'], 'update', 'reservation', $_POST['id'], 'Updated reservation');
    } else {
        $reservationModel->add($_POST);
        Logger::log($pdo, $_SESSION['user_id'], 'create', 'reservation', null, 'Created reservation');
    }

    // Redirigir al listado
    header('Location: ../../frontend/reservations/ReservationsList.php');
    exit;
}

// Borrado lógico
if (isset($_GET['delete'])) {
    $reservationModel->deactivate($_GET['delete']);
    Logger::log($pdo, $_SESSION['user_id'], 'delete', 'reservation', $_GET['delete'], 'Deactivated reservation');
    header('Location: ../../frontend/reservations/ReservationsList.php');
    exit;
}
