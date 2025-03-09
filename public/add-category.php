<?php
require_once __DIR__ . '/../app/controllers/GalleryController.php';
session_start();

// Перевіряємо, чи POST-запит взагалі прийшов
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  die("Помилка: некоректний метод запиту.");
}

// Дебагінг: перевіряємо, що передано в $_POST
error_log(print_r($_POST, true));

// Перевіряємо CSRF токен
if (
  !isset($_SESSION['csrf_token']) ||
  !isset($_POST['csrf_token']) ||
  $_POST['csrf_token'] !== $_SESSION['csrf_token']
) {
  die("CSRF validation failed.");
}

// Перевіряємо, чи є значення у 'category_name'
if (!isset($_POST['category_name']) || trim($_POST['category_name']) === '') {
  die("Помилка: Назва категорії обов'язкова.");
}

// Якщо все добре, додаємо категорію
$galleryController = new GalleryController();
$galleryController->addCategory($_POST['category_name']);

// Перенаправлення назад в адмінку
header("Location: admin.php");
exit();
?>
