<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../frontend/login.php');
    exit;
}

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../models/Payment.php';
require_once __DIR__ . '/../models/Reservation.php';
require_once __DIR__ . '/../helpers/Logger.php';

$paymentModel = new Payment($pdo);
$reservationModel = new Reservation($pdo);

// Crear o actualizar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_POST['created_by'] = $_SESSION['user_id'];

    $reservation = $reservationModel->getById($_POST['reservation_id']);
    $_POST['amount'] = $reservation['total_cost'] ?? 0;

    if (!empty($_POST['id'])) {
        $paymentModel->update($_POST['id'], $_POST);
        Logger::log($pdo, $_SESSION['user_id'], 'update', 'payment', $_POST['id'], 'Updated payment');
    } else {
        $paymentModel->add($_POST);
        Logger::log($pdo, $_SESSION['user_id'], 'create', 'payment', null, 'Created payment');
    }
    header('Location: ../../frontend/payments/PaymentsList.php');
    exit;
}

// Borrado lógico
if (isset($_GET['delete'])) {
    $paymentModel->deactivate($_GET['delete']);
    Logger::log($pdo, $_SESSION['user_id'], 'delete', 'payment', $_GET['delete'], 'Deactivated payment');
    header('Location: ../../frontend/payments/PaymentsList.php');
    exit;
}
