<?php
require_once __DIR__ . '/../../app/controllers/BookingController.php';

header('Content-Type: application/json');

if (!isset($_GET['room_id']) || empty($_GET['room_id'])) {
  echo json_encode(['success' => false, 'message' => 'Room ID is required']);
  exit();
}

$bookingController = new BookingController();
$bookedDates = $bookingController->getBookedDates($_GET['room_id']);

echo json_encode(['success' => true, 'bookedDates' => $bookedDates]);
exit();
