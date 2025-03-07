<?php
session_start();
require_once __DIR__ . '/../app/controllers/BookingController.php';
require_once __DIR__ . '/../app/controllers/RoomController.php';

$bookingController = new BookingController();
$roomController = new RoomController();

// Перевіряємо, чи переданий `room_id`
if (!isset($_GET['room_id']) || empty($_GET['room_id'])) {
    header("Location: index.php");
    exit;
}

$room = $roomController->getRoomById($_GET['room_id']);
$roomImages = $roomController->getRoomImages($_GET['room_id']);

// Якщо кімнати немає, перенаправляємо на головну
if (!$room) {
    header("Location: index.php");
    exit;
}

// Генеруємо унікальний токен для захисту від дублювання
if (!isset($_SESSION['booking_token'])) {
    $_SESSION['booking_token'] = bin2hex(random_bytes(32));
}

// Перевіряємо, чи є повідомлення у сесії
$response = $_SESSION['booking_message'] ?? null;
unset($_SESSION['booking_message']); // Видаляємо повідомлення після першого виведення

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST['booking_token']) || $_POST['booking_token'] !== $_SESSION['booking_token']) {
        $response = ['status' => 'danger', 'message' => 'Невірний запит бронювання.'];
    } else {
        $response = $bookingController->createBooking($_POST);

        if ($response['status'] === 'success') {
            $_SESSION['booking_message'] = $response; // Зберігаємо повідомлення у сесії
            unset($_SESSION['booking_token']); // Видаляємо токен, щоб уникнути повторного бронювання
            header("Location: booking.php?room_id=" . $_GET['room_id']); // PRG-патерн
            exit;
        }
    }
}

require_once __DIR__ . '/../views/templates/header.php';
?>
<div class="fixed-background"></div>

<div class="scrollable-content"> <!-- Обгортка для всього контенту -->
<div class="content-wrapper">
    <div class="container">
        <h2 class="hello">Бронювання будиночка</h2>
        
        <?php if (!empty($response)): ?>
            <div class="alert alert-<?= htmlspecialchars($response['status']); ?>">
                <?= htmlspecialchars($response['message'], ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-6">
    <?php if (!empty($roomImages)): ?>
        <div id="carouselBooking" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
            <div class="carousel-inner">
                <?php foreach ($roomImages as $index => $image): ?>
                    <div class="carousel-item <?= $index == 0 ? 'active' : '' ?>">
                        <img src="<?= htmlspecialchars($image['image_path']); ?>" class="d-block w-100" alt="Room Image">
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselBooking" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselBooking" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    <?php else: ?>
        <p>No images available for this room.</p>
    <?php endif; ?>

    <!-- Додаємо опис під каруселлю -->
    <div class="room-description mt-3">
        <h4 class="hello">Опис будиночка</h4>
        <p class="deskription"><?= nl2br(htmlspecialchars($room['description'] ?? 'Опис відсутній')); ?></p>
    </div>
</div>

            <div class="col-md-6">
                <h3 class="hello"><p><strong>Будиночок:</strong> <?= htmlspecialchars($room['room_number']); ?></p></h3>
                <h3 class="hello"><p><strong>Тип будиночка:</strong> <?= htmlspecialchars($room['room_type'] ?? 'Не вказано'); ?></p></h3>
                <h3 class="hello"><p><strong>Ціна:</strong> <?= htmlspecialchars($room['price']); ?>грн За добу</p></h3>
                
                <form method="post">
                    <input type="hidden" name="room_id" value="<?= htmlspecialchars($room['id']); ?>">
                    <input type="hidden" name="booking_token" value="<?= $_SESSION['booking_token']; ?>">

                    <!-- Контейнер для календаря -->
                    <div id="calendar-wrapper">
                        <div id="custom-inner">
                            <div id="custom-prev" class="custom-nav">&#9664;</div>
                            <div id="custom-month"></div>
                            <div id="custom-year"></div>
                            <div id="custom-next" class="custom-nav">&#9654;</div>
                        </div>
                        <div id="calendar"></div>
                    </div>
                    <button type="button" id="reset-dates" class="btn btn-warning">Скинути вибір</button>
                    
                    <div class="selected-dates-info">
                        <p><strong>Дата заїзду:</strong> <span id="display-check-in">Не вибрано</span></p>
                        <p><strong>Дата виїзду:</strong> <span id="display-check-out">Не вибрано</span></p>
                        <input type="hidden" id="check-in" name="check_in">
                        <input type="hidden" id="check-out" name="check_out">
                    </div>

                    <h5>Інформація для бронювання</h5>
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="guest_email" class="form-control" required>
                        
                    </div>
                    <div class="mb-3">
                        <label>Номер телефону</label>
                        <input type="text" name="guest_phone" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-success">Забронювати</button>
                </form>
            </div>
        </div>
    </div>
</div>
</div>
<?php require_once __DIR__ . '/../views/templates/footer.php'; ?>
</div> <!-- Закриваємо .scrollable-content -->