<?php
require_once __DIR__ . '/../app/controllers/GalleryController.php';
$galleryController = new GalleryController();
require_once __DIR__ . '/../views/templates/header.php';
// Перевіряємо, чи метод getCategories() повернув масив
$categories = $galleryController->getCategories() ?? []; 
$photos = $galleryController->getAllPhotos();
?>



<div class="fixed-background"></div>

<div class="scrollable-content"> <!-- Обгортка для всього контенту -->

<div class="content-wrapper"> 
<div class="container">
    <div class="row">
        <div class="col-lg-12 text-center my-2">
            <h4 class="sosnova">Галерея</h4>
        </div>
    </div>

    <!-- Динамічні фільтри -->
    <div class="portfolio-menu mt-2 mb-4 text-center">
        <ul class="list-inline">
            <li class="btn btn-outline-light active" data-filter="*">Усі</li>
            <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $category): ?>
                    <li class="btn btn-outline-light" data-filter=".cat-<?= strtolower($category['name']); ?>">
                        <?= htmlspecialchars($category['name']); ?>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li class="btn btn-outline-dark disabled">Категорії не знайдено</li>
            <?php endif; ?>
        </ul>
    </div>

    <!-- Галерея -->
    <div class="positions">
    <div class="portfolio-item row">
        <?php foreach ($photos as $photo): ?>
            <?php
                // Додаємо класи для фільтрів (конвертуємо категорії у CSS-класи)
                $categoryClass = '';
if (!empty($photo['categories'])) {
    $categoriesArray = explode(',', $photo['categories']); // Масив категорій
    $categoryClass = implode(' ', array_map(fn($cat) => 'cat-' . strtolower(trim($cat)), $categoriesArray)); // Додаємо префікс "cat-"
}
            ?>
            <div class="item <?= $categoryClass ?>">
                <a href="<?= htmlspecialchars($photo['image_path']); ?>" class="fancylight popup-btn" data-fancybox-group="<?= $categoryClass; ?>">
                    <img class="img-fluid" src="<?= htmlspecialchars($photo['image_path']); ?>" alt="">
                </a>
<div class="caption">
                            <h5><?= htmlspecialchars($photo['title']); ?></h5>
                            <p><?= htmlspecialchars($photo['description']); ?></p>
                        </div>
            </div>
        <?php endforeach; ?>
    </div>
    </div>
</div>

<!-- JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.isotope/3.0.6/isotope.pkgd.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/jquery.magnific-popup.js"></script>
<script src="/js/photo-gallery.js?v=3.9"></script>
<!-- Masonry.js -->
        </div>
    </div>
<?php require_once __DIR__ . '/../views/templates/footer.php'; ?>
</div>

