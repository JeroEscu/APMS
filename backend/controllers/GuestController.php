<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../frontend/login.php');
    exit;
}

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../models/Guest.php';
require_once __DIR__ . '/../helpers/Logger.php';

$guestModel = new Guest($pdo);

// Guardar nuevo o editar existente
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!empty($_POST['id'])) {
        $guestModel->update($_POST['id'], $_POST);
        Logger::log($pdo, $_SESSION['user_id'], 'update', 'guest', $_POST['id'], 'Updated guest');
    } else {
        $guestModel->add($_POST);
        Logger::log($pdo, $_SESSION['user_id'], 'create', 'guest', null, 'Created guest');
    }

    header('Location: ../../frontend/guests/GuestsList.php');
    exit;
}

// Borrado lógico
if (isset($_GET['delete'])) {
    $guestModel->deactivate($_GET['delete']);

    Logger::log($pdo, $_SESSION['user_id'], 'delete', 'guest', $_GET['delete'], 'Deactivated guest');
    
    header('Location: ../../frontend/guests/GuestsList.php');
    exit;
}
