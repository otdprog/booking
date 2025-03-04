<?php
session_start();
require_once __DIR__ . '/../app/controllers/RoomController.php';

// Переконуємося, що користувач має права адміністратора
if (empty($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    die("Access denied.");
}

// Перевіряємо CSRF-токен
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_SESSION['csrf_token']) || !isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF validation failed.");
    }

    $roomController = new RoomController();
    $imageId = isset($_POST['image_id']) ? intval($_POST['image_id']) : 0;

    if ($imageId > 0) {
        $roomController->deleteRoomImage($imageId);
    }

    header("Location: " . $_SERVER['HTTP_REFERER']); // Повертаємо користувача назад
    exit;
}
?>