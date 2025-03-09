<?php
require_once __DIR__ . '/../app/controllers/GalleryController.php';
session_start();

// Перевіряємо, чи це POST-запит
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("❌ Помилка: Некоректний метод запиту.");
}

// Перевіряємо CSRF-токен
if (!isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("❌ Помилка: CSRF-токен недійсний.");
}

// Перевіряємо, чи передано ID фото
if (!isset($_POST['photo_id']) || !is_numeric($_POST['photo_id'])) {
    die("❌ Помилка: Некоректний ID фото.");
}

$galleryController = new GalleryController();
$success = $galleryController->deletePhoto((int)$_POST['photo_id']);

if ($success) {
    $_SESSION['message'] = "✅ Фото успішно видалено!";
} else {
    $_SESSION['message'] = "❌ Помилка видалення фото.";
}

// Повертаємо користувача назад
header("Location: " . $_SERVER['HTTP_REFERER']);
exit;
?>