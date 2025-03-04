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
?>

<div class="content-wrapper">
<div class="container">
<h2>Edit Room<a href="admin.php" class="btn btn-danger ms-2">Back</a></h2>
<form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="room_id" value="<?= htmlspecialchars($room['id']); ?>">

    <label>Room Number</label>
    <input type="text" name="room_number" class="form-control" value="<?= htmlspecialchars($room['room_number']); ?>" required>

    <label>Room Type</label>
    <select name="room_type" class="form-control">
        <option value="single" <?= $room['room_type'] == 'single' ? 'selected' : ''; ?>>Single</option>
        <option value="double" <?= $room['room_type'] == 'double' ? 'selected' : ''; ?>>Double</option>
        <option value="suite" <?= $room['room_type'] == 'suite' ? 'selected' : ''; ?>>Suite</option>
    </select>

    <label>Price per Night</label>
    <input type="number" name="price" class="form-control" value="<?= htmlspecialchars($room['price']); ?>" step="0.01" required>

    <label>Upload New Images</label>
    <input type="file" name="new_images[]" multiple class="form-control" accept="image/*">

    <button type="submit" class="btn btn-primary w-100 mt-3">Save Changes</button>
</form>

<h3>Room Images</h3>
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