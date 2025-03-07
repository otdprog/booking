<?php
require_once __DIR__ . '/../app/controllers/RoomController.php';
require_once __DIR__ . '/../app/controllers/BookingController.php';

session_start();

if (!isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("CSRF validation failed.");
}

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    die("Unauthorized access.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['room_id'])) {
    $roomId = intval($_POST['room_id']);
    
    $bookingController = new BookingController();
    $activeBookings = $bookingController->getBookedDates($roomId);

    if (!empty($activeBookings)) {
        $_SESSION['message'] = "Cannot delete room with active bookings!";
        header("Location: admin.php");
        exit;
    }

    $roomController = new RoomController();
    $message = $roomController->deleteRoom($roomId);

    $_SESSION['message'] = $message;
    header("Location: admin.php");
    exit;
}