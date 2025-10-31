<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    die("Acceso denegado. Solo el administrador puede gestionar usuarios.");
}

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/Logger.php';

$userModel = new User($pdo);

// Guardar o editar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['id'])) {
        $userModel->update($_POST['id'], $_POST);
        Logger::log($pdo, $_SESSION['user_id'], 'update', 'user', $_POST['id'], 'Updated user');
    } else {
        $userModel->add($_POST);
        Logger::log($pdo, $_SESSION['user_id'], 'create', 'user', null, 'Created user');
    }

    header('Location: ../../frontend/users/UsersList.php');
    exit;
}

// Borrado lógico
if (isset($_GET['delete'])) {
    $userModel->deactivate($_GET['delete']);
    Logger::log($pdo, $_SESSION['user_id'], 'delete', 'user', $_GET['delete'], 'Deactivated user');
    header('Location: ../../frontend/users/UsersList.php');
    exit;
}
