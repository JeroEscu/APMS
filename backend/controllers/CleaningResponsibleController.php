<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../frontend/login.php');
    exit;
}

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../models/CleaningResponsible.php';
require_once __DIR__ . '/../helpers/Logger.php';

$responsibleModel = new CleaningResponsible($pdo);

// Guardar o editar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['id'])) {
        $responsibleModel->update($_POST['id'], $_POST);
        Logger::log($pdo, $_SESSION['user_id'], 'update', 'cleaning_responsible', $_POST['id'], 'Updated cleaning responsible');
    } else {
        $responsibleModel->add($_POST);
        Logger::log($pdo, $_SESSION['user_id'], 'create', 'cleaning_responsible', null, 'Created cleaning responsible');
    }

    header('Location: ../../frontend/cleaning_responsibles/CleaningResponsiblesList.php');
    exit;
}

// Borrar
if (isset($_GET['delete'])) {
    $responsibleModel->delete($_GET['delete']);

    Logger::log($pdo, $_SESSION['user_id'], 'delete', 'cleaning_responsible', $_GET['delete'], 'Deleted cleaning responsible');
    
    header('Location: ../../frontend/cleaning_responsibles/CleaningResponsiblesList.php');
    exit;
}
