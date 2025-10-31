<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../frontend/login.php');
    exit;
}

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../models/Property.php';
require_once __DIR__ . '/../helpers/Logger.php';

$propertyModel = new Property($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Agregar o actualizar
    if (isset($_POST['id']) && $_POST['id'] != '') {
        $propertyModel->update($_POST['id'], $_POST);
        Logger::log($pdo, $_SESSION['user_id'], 'update', 'property', $_POST['id'], 'Updated property');
    } else {
        $propertyModel->add($_POST);
        Logger::log($pdo, $_SESSION['user_id'], 'create', 'property', null, 'Created property');
    }
    header('Location: ../../frontend/properties/PropertiesList.php');
    exit;
}

if (isset($_GET['delete'])) {
    $propertyModel->deactivateWithReservations($_GET['delete'], $_SESSION['user_id']);
    Logger::log($pdo, $_SESSION['user_id'], 'delete', 'property', $_GET['delete'], 'Deactivated property');
    header('Location: ../../frontend/properties/PropertiesList.php');
    exit;
}

