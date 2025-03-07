<?php
require_once __DIR__ . '/../app/controllers/RoomController.php';
session_start();

$roomController = new RoomController();

// Отримуємо ID кімнати з URL
$roomId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$room = $roomController->getRoomById($roomId);
$images = $roomController->getRoomImages($roomId); // Отримуємо всі зображення кімнати

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF validation failed.");
    }

    // Передаємо $_FILES у updateRoom
    $roomController->updateRoom($_POST, $_FILES);
    header("Location: admin.php");
    exit;
}

require_once __DIR__ . '/../views/templates/header.php';
if (isset($_SESSION['message'])) {
    echo "<div class='alert alert-success'>" . $_SESSION['message'] . "</div>";
    unset($_SESSION['message']); // Очищаємо повідомлення після відображення
}
?>

<div class="content-wrapper">
<div class="container">
<h2 class="hello">Редагування будиночка<a href="admin.php" class="btn btn-danger ms-2">Назад</a></h2>
<form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="room_id" value="<?= htmlspecialchars($room['id']); ?>">

    <label>Назва будиночка:</label>
    <input type="text" name="room_number" value="<?= htmlspecialchars($room['room_number']); ?>" class="form-control" required>

<input type="text" name="room_type" class="form-control" value="<?= htmlspecialchars($room['room_type']); ?>" required>


    <label>Ціна будиночка:</label>
    <input type="number" name="price" value="<?= htmlspecialchars($room['price']); ?>" class="form-control" step="0.01" required>

    <label>Опис:</label>
    <textarea name="description" class="form-control" required><?= htmlspecialchars($room['description'] ?? ''); ?></textarea>

    <label>Додати нові зображення:</label>
    <input type="file" name="new_images[]" multiple class="form-control" accept="image/*">

    <button type="submit" class="btn btn-primary mt-3">Зберегти зміни</button>
</form>

<h3 class="hello">Зображення будиночка</h3>
<div class="d-flex flex-wrap gap-3">
    <?php foreach ($images as $image): ?>
        <div class="position-relative" style="width: 250px; height: 200px; overflow: hidden;">
            <img src="<?= htmlspecialchars($image['image_path']); ?>" alt="Room Image" class="img-fluid w-100 h-100 object-fit-cover">
            <form method="post" action="delete-room-image.php" class="position-absolute top-0 end-0 p-0 m-0">
    <input type="hidden" name="image_id" value="<?= $image['id']; ?>">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
    <button type="submit" class="btn btn-danger btn-sm p-10 m-10">
        <span class="visually-hidden">Delete</span>
        X
    </button>
</form>
        </div>
    <?php endforeach; ?>
</div>
</div>
</div>

<?php require_once __DIR__ . '/../views/templates/footer.php'; ?>