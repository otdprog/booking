<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
   header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../app/controllers/RoomController.php';

$roomController = new RoomController();
$message = "";



if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Передаємо і $_POST, і $_FILES['image']
    $message = $roomController->addRoom($_POST, $_FILES);
}

require_once __DIR__ . '/../views/templates/header.php';
?>

<div class="container">
    <h2>Add New Room</h2>

    <?php if (!empty($message)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

<form method="post" enctype="multipart/form-data" action="">
    <label>Room Number</label>
    <input type="text" name="room_number" class="form-control" required>

    <label>Room Type</label>
    <select name="room_type" class="form-control">
        <option value="single">Single</option>
        <option value="double">Double</option>
        <option value="suite">Suite</option>
    </select>

    <label>Price per Night</label>
    <input type="number" name="price" class="form-control" step="0.01" required>

    <label>Upload Images</label>
    <input type="file" name="images[]" multiple class="form-control" accept="image/*">

    <button type="submit" class="btn btn-primary w-100 mt-3">Add Room</button>
</form>
</div>

<?php require_once __DIR__ . '/../views/templates/footer.php'; ?>