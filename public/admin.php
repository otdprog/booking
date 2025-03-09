<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
// Захист від фіксації сесії (Session Fixation Attack)
if (!isset($_SESSION['last_regeneration'])) {
  $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 300) {
  // Оновлення кожні 5 хвилин
  session_regenerate_id(true);
  $_SESSION['last_regeneration'] = time();
}

// Якщо користувач не адмін – знищуємо сесію і перенаправляємо на login.php
if (empty($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
  session_destroy();
  header("Location: login.php");
  exit();
}

// Захист від захоплення сесії через зміну браузера/IP
if (!isset($_SESSION['user_agent'])) {
  $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
} elseif ($_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
  session_destroy();
  header("Location: login.php");
  exit();
}

if (!isset($_SESSION['user_ip'])) {
  $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'];
} elseif ($_SESSION['user_ip'] !== $_SERVER['REMOTE_ADDR']) {
  session_destroy();
  header("Location: login.php");
  exit();
}

require_once __DIR__ . '/../app/controllers/BookingController.php';
require_once __DIR__ . '/../app/controllers/RoomController.php';

$bookingController = new BookingController();
$roomController = new RoomController();

// Отримуємо параметри фільтрації, пагінації та сортування
$filterStatus = isset($_GET['status']) ? $_GET['status'] : '';
$filterGuestContact = isset($_GET['guest_contact'])
  ? $_GET['guest_contact']
  : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Дозволені колонки для сортування
$allowedColumns = [
  'id',
  'guest_email',
  'guest_phone',
  'room_number',
  'check_in',
  'check_out',
  'status',
];
$sortColumn =
  isset($_GET['sort']) && in_array($_GET['sort'], $allowedColumns)
    ? $_GET['sort']
    : 'id';
$sortOrder =
  isset($_GET['order']) && $_GET['order'] === 'desc' ? 'DESC' : 'ASC';

// Отримуємо відсортовані дані
$bookings = $bookingController->getFilteredBookings(
  $limit,
  $offset,
  $filterStatus,
  $filterGuestContact,
  $sortColumn,
  $sortOrder
);
$totalBookings = $bookingController->countBookings(
  $filterStatus,
  $filterGuestContact
);
$totalPages = $totalBookings > 0 ? ceil($totalBookings / $limit) : 1;

$rooms = $roomController->getRooms();
$message = "";

// Обробка підтвердження бронювання
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  if (
    !isset($_POST['csrf_token']) ||
    $_POST['csrf_token'] !== $_SESSION['csrf_token']
  ) {
    die("CSRF validation failed.");
  }

  if (isset($_POST['confirm_booking_id'])) {
    $message = $bookingController->confirmBooking($_POST['confirm_booking_id']);
    $_SESSION['message'] = $message;
    header("Location: admin.php"); // Уникаємо дублювання при оновленні сторінки
    exit();
  }
}
function toggleOrder($column)
{
  $currentOrder = isset($_GET['order']) ? $_GET['order'] : 'asc';
  $currentSort = isset($_GET['sort']) ? $_GET['sort'] : '';

  if ($currentSort === $column) {
    return $currentOrder === 'asc' ? 'desc' : 'asc';
  }
  return 'asc';
}
require_once __DIR__ . '/../views/templates/header.php';
?>
<?php if (!empty($_SESSION['message'])): ?>
<div class="alert alert-success"><?= htmlspecialchars(
  $_SESSION['message']
) ?></div>
<?php unset($_SESSION['message']); ?>
<?php endif; ?>
<div class="fixed-background"></div>

<div class="scrollable-content">
    <!-- Обгортка для всього контенту -->
    <div class="content-wrapper">
        <div class="container">
            <h2 class="sosnova">Панель адміністратора <a href="logout.php" class="btn btn-danger ms-2">Вийти</a></h2>

            <?php if (!empty($message)): ?>
            <div class="alert alert-success"><?= htmlspecialchars(
              $message
            ) ?></div>
            <?php endif; ?>

            <!-- Навігація по вкладках -->
            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#bookings">Бронювання</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#rooms">Будиночки</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#gallery">Галерея</a>
                </li>
            </ul>

            <div class="tab-content mt-3">
                <!-- Вкладка "Bookings" -->
                <div class="tab-pane fade show active" id="bookings">
                    <h3>Бронювання будиночків</h3>

                    <!-- Форма фільтрації + Кнопка видалення в одному рядку -->
                    <div class="d-flex gap-2 align-items-center mb-3">
                        <form method="get" class="d-flex gap-2 mb-0">
                            <input type="text" name="guest_contact" placeholder="Email та телефон" value="<?= htmlspecialchars(
                              $filterGuestContact ?? '',
                              ENT_QUOTES,
                              'UTF-8'
                            ) ?>" class="form-control">
                            <select name="status" class="form-control">
                                <option value="">Всі статуси</option>
                                <option value="Очікує" <?= $filterStatus ==
                                'Очікує'
                                  ? 'selected'
                                  : '' ?>>Очікує підтвердження</option>
                                <option value="Готово" <?= $filterStatus ==
                                'Готово'
                                  ? 'selected'
                                  : '' ?>>Підтверджено</option>
                                <option value="Скасовано" <?= $filterStatus ==
                                'Скасовано'
                                  ? 'selected'
                                  : '' ?>>Скасовано</option>
                            </select>
                            <button type="submit" class="btn btn-primary">Фільтр</button>
                        </form>

                        <!-- Форма для видалення (виглядає як кнопка) -->
                        <form method="post" class="p-0 m-0 border-0 bg-transparent" onsubmit="return confirm('Are you sure you want to delete all expired bookings?');">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION[
                              'csrf_token'
                            ] ?>">
                            <button type="submit" name="delete_expired" class="btn btn-outline-danger">Видалити прострочені</button>
                        </form>
                    </div>
                    <!-- Таблиця бронювань -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th><a href="?sort=id&order=<?= toggleOrder(
                                      'id'
                                    ) ?>">ID</a></th>
                                    <th><a href="?sort=guest_email&order=<?= toggleOrder(
                                      'guest_email'
                                    ) ?>">Email</a></th>
                                    <th><a href="?sort=guest_phone&order=<?= toggleOrder(
                                      'guest_phone'
                                    ) ?>">Телефон</a></th>
                                    <th><a href="?sort=room_number&order=<?= toggleOrder(
                                      'room_number'
                                    ) ?>">Будиночок</a></th>
                                    <th><a href="?sort=check_in&order=<?= toggleOrder(
                                      'check_in'
                                    ) ?>">Дата заїзду</a></th>
                                    <th><a href="?sort=check_out&order=<?= toggleOrder(
                                      'check_out'
                                    ) ?>">Дата виїзду</a></th>
                                    <th><a href="?sort=status&order=<?= toggleOrder(
                                      'status'
                                    ) ?>">Статус</a></th>
                                    <th>Коментар</th>
                                    <th>Дія</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td><?= htmlspecialchars(
                                      $booking['id']
                                    ) ?></td>
                                    <td><?= htmlspecialchars(
                                      $booking['guest_email']
                                    ) ?></td>
                                    <td><?= htmlspecialchars(
                                      $booking['guest_phone']
                                    ) ?></td>
                                    <td><?= htmlspecialchars(
                                      $booking['room_number'] ?? '',
                                      ENT_QUOTES,
                                      'UTF-8'
                                    ) ?></td>
                                    <td><?= htmlspecialchars(
                                      $booking['check_in']
                                    ) ?></td>
                                    <td><?= htmlspecialchars(
                                      $booking['check_out']
                                    ) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $booking[
                                          'status'
                                        ] == 'Готово'
                                          ? 'success'
                                          : ($booking['status'] == 'Скасовано'
                                            ? 'danger'
                                            : 'warning') ?>">
                                            <?= htmlspecialchars(
                                              $booking['status']
                                            ) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars(
                                      $booking['admin_comment'] ?? ''
                                    ) ?></td>

                                    <td>
                                        <?php if (
                                          $booking['status'] == 'Очікує'
                                        ): ?>
                                        <form method="post" class="d-inline-block p-0 m-0">
                                            <input type="hidden" name="confirm_booking_id" value="<?= htmlspecialchars(
                                              $booking['id']
                                            ) ?>">
                                            <input type="hidden" name="csrf_token" value="<?= $_SESSION[
                                              'csrf_token'
                                            ] ?>">
                                            <button class="btn btn-success confirm-btn">Підтвердити</button>
                                        </form>
                                        <?php endif; ?>
                                        <a href="edit-booking.php?id=<?= $booking[
                                          'id'
                                        ] ?>" class="btn btn-primary btn-sm">Редагувати</a>
                                        <form method="post" action="delete-booking.php" class="d-inline-block p-0 m-0">
                                            <input type="hidden" name="booking_id" value="<?= $booking[
                                              'id'
                                            ] ?>">
                                            <input type="hidden" name="csrf_token" value="<?= $_SESSION[
                                              'csrf_token'
                                            ] ?>">
                                            <button class="btn btn-danger btn-sm">Видалити</button>
                                        </form>
                                        <a href="#" class="btn btn-secondary btn-sm edit-comment" data-id="<?= $booking[
                                          'id'
                                        ] ?>" data-comment="<?= htmlspecialchars(
  $booking['admin_comment'] ?? ''
) ?>">
                                            Коментар
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Пагінація -->

                    <ul class="pagination">
                        <!-- Кнопка "Попередня" -->
                        <li class="page-item <?= $page <= 1
                          ? 'disabled'
                          : '' ?>">
                            <a class="page-link" href="?page=<?= max(
                              1,
                              $page - 1
                            ) ?>&status=<?= urlencode(
  $filterStatus
) ?>&guest_contact=<?= urlencode($filterGuestContact) ?>">« Попередня</a>
                        </li>

                        <!-- Нумерація сторінок -->
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i == $page
                          ? 'active'
                          : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&status=<?= urlencode(
  $filterStatus
) ?>&guest_contact=<?= urlencode($filterGuestContact) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                        <?php endfor; ?>

                        <!-- Кнопка "Наступна" -->
                        <li class="page-item <?= $page >= $totalPages
                          ? 'disabled'
                          : '' ?>">
                            <a class="page-link" href="?page=<?= min(
                              $totalPages,
                              $page + 1
                            ) ?>&status=<?= urlencode(
  $filterStatus
) ?>&guest_contact=<?= urlencode($filterGuestContact) ?>">Наступна »</a>
                        </li>
                    </ul>

                </div>

                <!-- Вкладка "Rooms" -->
                <div class="tab-pane fade" id="rooms">
                    <h3>Керувати будиночками</h3>
                    <button id="toggleAddRoom" class="btn btn-success mb-3">Додати новий</button>

                    <!-- Форма додавання кімнати -->
                    <div id="addRoomForm" style="display: none;">

                        <form method="post" enctype="multipart/form-data" action="add-room.php">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION[
                              'csrf_token'
                            ] ?>">

                            <label>Назва будиночка</label>
                            <input type="text" name="room_number" class="form-control" required>

                            <label>Тип будиночка</label>
                            <input type="text" name="room_type" class="form-control" required>

                            <label>Ціна за добу</label>
                            <input type="number" name="price" class="form-control" step="0.01" required>

                            <label>Опис</label>
                            <textarea name="description" class="form-control" required></textarea>

                            <label>Завантажити зображення</label>
                            <input type="file" name="images[]" multiple class="form-control" accept="image/*">

                            <button type="submit" class="btn btn-primary w-100 mt-3">Додати будиночок</button>
                        </form>
                    </div>

                    <!-- Таблиця з кімнатами -->
                    <table class="table table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Номер будиночка</th>
                                <th>Тип</th>
                                <th>Ціна</th>
                                <th>Дія</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rooms as $room): ?>
                            <tr>
                                <td><?= htmlspecialchars($room['id']) ?></td>
                                <td><?= htmlspecialchars(
                                  $room['room_number']
                                ) ?></td>
                                <td><?= htmlspecialchars(
                                  $room['room_type']
                                ) ?></td>
                                <td>$<?= htmlspecialchars(
                                  $room['price']
                                ) ?></td>
                                <td>
                                    <a href="edit-room.php?id=<?= $room[
                                      'id'
                                    ] ?>" class="btn btn-primary btn-sm">Редагувати</a>
                                    <form method="post" action="delete-room.php" class="d-inline-block p-0 m-0">
                                        <input type="hidden" name="room_id" value="<?= $room[
                                          'id'
                                        ] ?>">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION[
                                          'csrf_token'
                                        ] ?>">
                                        <button class="btn btn-danger btn-sm">Видалити</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <!-- Вкладка "Gallery" -->
                <div class="tab-pane fade" id="gallery">
                    <h3>Галерея</h3>

                    <!-- Додавання категорій -->
                    <button id="toggleAddCategory" class="btn btn-success mb-3">Додати категорію</button>

                    <div id="addCategoryForm" style="display: none;">
                        <form method="post" action="add-category.php">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION[
                              'csrf_token'
                            ] ?>">
                            <label>Назва категорії:</label>
                            <input type="text" name="category_name" class="form-control" required>
                            <button type="submit" class="btn btn-primary w-100 mt-3">Додати</button>
                        </form>
                    </div>

                    <!-- Відображення категорій -->
                    <h3>Список категорій</h3>

    <?php
    require_once __DIR__ . '/../app/controllers/GalleryController.php';
    $galleryController = new GalleryController();
    $categories = $galleryController->getCategories();
    foreach ($categories as $category): ?>
    <li class="list-group-item d-flex align-items-center ms-3 mb-2">  <!-- Додаємо відступи для li -->
        <a href="gallery.php?category_id=<?= $category['id'] ?>" class="btn btn-info me-3"> <!-- Відступ справа -->
            <?= htmlspecialchars($category['name']) ?>
        </a>
        <form method="post" action="delete-category.php"  
            onsubmit="return confirm('Ви впевнені, що хочете видалити цю категорію?');" class="p-0 m-0 border-0 bg-transparent">
        
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="category_id" value="<?= $category['id'] ?>">

            <button type="submit" class="btn btn-outline-danger">Видалити</button>
        </form>
    </li>
    <?php endforeach; ?>

                    
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="commentModal" tabindex="-1" aria-labelledby="commentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="commentModalLabel">Редагування коментаря</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрити"></button>
            </div>
            <div class="modal-body">
                <form id="commentForm" method="post">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION[
                      'csrf_token'
                    ] ?>">
                    <input type="hidden" name="booking_id" id="bookingId">
                    <label for="admin_comment">Коментар:</label>
                    <textarea id="admin_comment" name="admin_comment" class="form-control"></textarea>
                    <button type="submit" class="btn btn-primary mt-3">Зберегти</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    document.getElementById("toggleAddRoom").addEventListener("click", function() {
        let form = document.getElementById("addRoomForm");
        form.style.display = form.style.display === "none" ? "block" : "none";
    });
    document.getElementById("toggleAddCategory").addEventListener("click", function() {
        let form = document.getElementById("addCategoryForm");
        form.style.display = form.style.display === "none" ? "block" : "none";
    });
</script>

<?php require_once __DIR__ . '/../views/templates/footer.php'; ?>
</div>