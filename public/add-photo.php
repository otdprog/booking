<?php
require_once __DIR__ . '/../app/controllers/GalleryController.php';
session_start();

if (!isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("CSRF validation failed.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES['image'])) {
    $galleryController = new GalleryController();
    $galleryController->addPhoto($_POST, $_FILES);
}

header("Location: gallery.php?category_id=" . intval($_POST['category_id']));
exit;
?>
<?php
require_once __DIR__ . '/../app/controllers/GalleryController.php';
session_start();

$controller = new GalleryController();
$categories = $controller->getCategories();
?>
<h3>Додати фото</h3>
<form method="post" enctype="multipart/form-data" action="process-add-photo.php">
    <label>Заголовок:</label>
    <input type="text" name="title" required class="form-control">
    
    <label>Опис:</label>
    <textarea name="description" required class="form-control"></textarea>

    <label>Категорії:</label>
    <select name="category_id[]" multiple required class="form-control">
        <?php foreach ($categories as $category): ?>
            <option value="<?= $category['id']; ?>"><?= htmlspecialchars($category['name']); ?></option>
        <?php endforeach; ?>
    </select>

    <label>Фото:</label>
    <input type="file" name="image" required class="form-control" accept="image/*">

    <button type="submit" class="btn btn-primary mt-3">Додати</button>
</form>