<?php
require_once __DIR__ . '/../app/controllers/GalleryController.php';
session_start();

// Перевіряємо, чи запит є POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Помилка: некоректний метод запиту.");
}

// Дебагінг: перевіряємо, що передано в $_POST
error_log(print_r($_POST, true));

// Перевіряємо CSRF-токен
if (!isset($_SESSION['csrf_token']) || !isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("Помилка: CSRF-токен недійсний.");
}

// Перевіряємо, чи передано ID категорії
if (!isset($_POST['category_id']) || !is_numeric($_POST['category_id'])) {
    die("Помилка: Невірний ID категорії.");
}

$galleryController = new GalleryController();
$galleryController->deleteCategory((int)$_POST['category_id']);

// Перенаправлення назад в адмінку
header("Location: admin.php");
exit;
?>