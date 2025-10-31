<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../frontend/login.php');
    exit;
}

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../models/Cleaning.php';
require_once __DIR__ . '/../models/Reservation.php';
require_once __DIR__ . '/../models/Property.php';
require_once __DIR__ . '/../models/CleaningResponsible.php';
require_once __DIR__ . '/../helpers/Logger.php';

$cleaningModel = new Cleaning($pdo);
$reservationModel = new Reservation($pdo);
$propertyModel = new Property($pdo);
$responsibleModel = new CleaningResponsible($pdo);

// Verificación adicional para el personal de limpieza
if ($_SESSION['role_id'] == 3) {
    $assigned = $responsibleModel->getByUserId($_SESSION['user_id']);

    // Si es edición, verificar que la limpieza le pertenece
    if (!empty($_POST['id'])) {
        $cleaning = $cleaningModel->getById($_POST['id']);
        if ($cleaning['responsible_id'] != $assigned['id']) {
            die("No tienes permiso para modificar esta limpieza.");
        }
    }
    // En cualquier caso, forzamos su responsible_id
    $_POST['responsible_id'] = $assigned['id'];
}

// Crear o actualizar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos de la reserva
    $reservation = $reservationModel->getById($_POST['reservation_id']);
    $_POST['property_id'] = $reservation['property_id'];

    // Obtener costo de limpieza de la propiedad
    $property = $propertyModel->getById($_POST['property_id']);
    $_POST['cost'] = $property['cleaning_cost'];

    if (!empty($_POST['id'])) {

        $cleaningModel->update($_POST['id'], $_POST);

        Logger::log($pdo, $_SESSION['user_id'], 'update', 'cleaning', $_POST['id'], 'Updated cleaning');

    } else {
        $cleaningModel->add($_POST);

        Logger::log($pdo, $_SESSION['user_id'], 'create', 'cleaning', null, 'Created cleaning');
    }

    

    header('Location: ../../frontend/cleanings/CleaningsList.php');
    exit;
}

// Borrado lógico
if (isset($_GET['delete'])) {
    $cleaningModel->deactivate($_GET['delete']);

    Logger::log($pdo, $_SESSION['user_id'], 'delete', 'cleaning', $_GET['delete'], 'Deactivated cleaning');
    
    header('Location: ../../frontend/cleanings/CleaningsList.php');
    exit;
}
