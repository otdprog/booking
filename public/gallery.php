<?php
require_once __DIR__ . '/../app/controllers/GalleryController.php';
session_start();

if (!isset($_GET['category_id'])) {
    header("Location: admin.php");
    exit;
}

$categoryId = intval($_GET['category_id']);
$galleryController = new GalleryController();
$photos = $galleryController->getPhotosByCategory($categoryId);
$category = $galleryController->getCategoryById($categoryId);

require_once __DIR__ . '/../views/templates/header.php';
?>
<div class="container">
    <h2 class="hello">Галерея: <?= htmlspecialchars($category['name']); ?><a href="admin.php" class="btn btn-danger ms-2">Назад</a></h2>
    
    <!-- Форма додавання фото -->
    <button id="toggleAddPhoto" class="btn btn-success mb-3">Додати фото</button>
    <div id="addPhotoForm" style="display: none;">
        <form method="post" enctype="multipart/form-data" action="add-photo.php">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="category_id" value="<?= $categoryId; ?>">
            
            <label>Заголовок</label>
            <input type="text" name="title" class="form-control" required>
            
            <label>Опис</label>
            <textarea name="description" class="form-control"></textarea>
            
            <label>Фото</label>
            <input type="file" name="image" class="form-control" accept="image/*" required>

            <button type="submit" class="btn btn-primary w-100 mt-3">Додати фото</button>
        </form>
    </div>

    <!-- Відображення фото -->
<div class="row mt-3">
    <?php foreach ($photos as $photo): ?>
        <div class="col-md-4 mt-3">
            <div class="card mb-4">
                <img src="<?= htmlspecialchars($photo['image_path']); ?>" class="position-relative img-fluid" style="max-height: 250px; object-fit: cover;" alt="Фото">
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($photo['title']); ?></h5>
                    <p class="card-text"><?= htmlspecialchars($photo['description']); ?></p>
                </div>
                <form method="post" action="delete-photo.php" class="d-flex justify-content-center p-0 m-0 border-0 bg-transparent">
                    <input type="hidden" name="photo_id" value="<?= $photo['id']; ?>">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                    <button class="btn btn-outline-danger">Видалити</button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
</div>
</div>
<script>
document.getElementById("toggleAddPhoto").addEventListener("click", function() {
    let form = document.getElementById("addPhotoForm");
    form.style.display = form.style.display === "none" ? "block" : "none";
});
</script>
<?php require_once __DIR__ . '/../views/templates/footer.php'; ?>