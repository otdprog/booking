<?php
require_once __DIR__ . '/../app/controllers/RoomController.php';
session_start();

// Перевіряємо CSRF-токен
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF validation failed.");
    }

    $roomController = new RoomController();
    $roomController->addRoom($_POST, $_FILES);
}