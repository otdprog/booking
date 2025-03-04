<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../app/controllers/RoomController.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: admin.php?error=Invalid room ID");
    exit;
}

$roomController = new RoomController();
$message = $roomController->deleteRoom($_GET['id']);

header("Location: admin.php?message=" . urlencode($message));
exit;