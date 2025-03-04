<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../app/controllers/AdminController.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['booking_id'], $_POST['status'])) {
    $adminController = new AdminController();
    if ($adminController->updateBookingStatus($_POST['booking_id'], $_POST['status'])) {
        header("Location: admin.php?message=Booking updated successfully");
    } else {
        header("Location: admin.php?error=Failed to update booking");
    }
}
exit;