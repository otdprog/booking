<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../app/controllers/BookingController.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['booking_id'])) {
    $bookingController = new BookingController();
    $message = $bookingController->cancelBooking($_POST['booking_id']);

    header("Location: admin.php?message=" . urlencode($message));
    exit;
}