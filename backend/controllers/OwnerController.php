<?php
session_start();

// Verificación de sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../frontend/login.php');
    exit;
}

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../models/Owner.php';
require_once __DIR__ . '/../helpers/Logger.php';

$ownerModel = new Owner($pdo);

// Si llega un formulario por POST (crear o editar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id']) && $_POST['id'] != '') {
        $ownerModel->update($_POST['id'], $_POST);
        Logger::log($pdo, $_SESSION['user_id'], 'update', 'owner', $_POST['id'], 'Updated owner');
    } else {
        $ownerModel->add($_POST);
        Logger::log($pdo, $_SESSION['user_id'], 'create', 'owner', null, 'Created owner');
    }
    header('Location: ../../frontend/owners/OwnersList.php');
    exit;
}

// Si se solicita eliminación lógica
if (isset($_GET['delete'])) {
    $ownerModel->deactivate($_GET['delete']);
    Logger::log($pdo, $_SESSION['user_id'], 'delete', 'owner', $_GET['delete'], 'Deactivated owner');
    header('Location: ../../frontend/owners/OwnersList.php');
    exit;
}
