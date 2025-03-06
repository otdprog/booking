<?php
session_start();
require_once __DIR__ . '/../app/controllers/BookingController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'], $_POST['admin_comment'], $_POST['csrf_token'])) {
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF validation failed.");
    }

    $bookingController = new BookingController();
    $result = $bookingController->updateComment($_POST['booking_id'], $_POST['admin_comment']);

    echo $result ? "Коментар оновлено" : "Помилка оновлення коментаря";
}
?>