<?php
require_once __DIR__ . '/../app/controllers/BookingController.php';
session_start();



//var_dump($_POST); 
//die;
// Перевіряємо CSRF-токен
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo json_encode(['success' => false, 'message' => 'CSRF validation failed.']);
    exit;
}

// Перевіряємо, чи користувач - адмін
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'])) {
    $bookingId = intval($_POST['booking_id']);

    $controller = new BookingController();
    $message = $controller->confirmBooking($bookingId);

    echo json_encode(['success' => ($message === "Booking confirmed successfully!"), 'message' => $message]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);
exit;