<?php
session_start();
require_once __DIR__ . '/../app/controllers/BookingController.php';

// Перевіряємо, чи запит надіслано методом POST і чи є CSRF-токен
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['booking_id'], $_POST['csrf_token'])) {
    die("Invalid request.");
}

// Перевіряємо, чи CSRF-токен правильний
if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("CSRF validation failed.");
}

$bookingController = new BookingController();
$message = $bookingController->deleteBooking($_POST['booking_id']);

header("Location: admin.php?message=" . urlencode($message));
exit;