<?php
session_start();

require_once __DIR__ . '/../app/controllers/RoomController.php';
require_once __DIR__ . '/../app/controllers/BookingController.php';
require_once __DIR__ . '/../views/templates/header.php';

$roomController = new RoomController();
$bookingController = new BookingController();
$rooms = $roomController->getRooms();

$roomImages = [];
$bookedDates = [];

// Отримуємо зображення кімнат та заброньовані дати
foreach ($rooms as $room) {
    $roomImages[$room['id']] = $roomController->getRoomImages($room['id']) ?: [];
    $bookedDates[$room['id']] = $bookingController->getBookedDates($room['id']) ?: [];
}
?>

<!-- Додаємо фіксований фон -->
<div class="fixed-background"></div>

<div class="scrollable-content"> <!-- Обгортка для всього контенту -->

    <div class="content-wrapper"> <!-- Додаємо обгортку для футера -->
        <div class="container text-center mt-4">
            <h2>Вітаємо у відпочинковій зоні</h2>
            <h3 class="mt-4">SOSNOVA relax zone</h3>
            <h3 class="mt-4">Будиночки для відпочинку</h3>

            <div class="row">
                <?php foreach ($rooms as $room): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <?php if (!empty($roomImages[$room['id']])): ?>
                                <div id="carouselRoom<?= $room['id']; ?>" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="3000">
                                    <div class="carousel-indicators">
                                        <?php foreach ($roomImages[$room['id']] as $index => $image): ?>
                                            <button type="button" data-bs-target="#carouselRoom<?= $room['id']; ?>" 
                                                    data-bs-slide-to="<?= $index; ?>" 
                                                    class="<?= $index == 0 ? 'active' : '' ?>" 
                                                    aria-current="true" 
                                                    aria-label="Slide <?= $index + 1; ?>"></button>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="carousel-inner">
                                        <?php foreach ($roomImages[$room['id']] as $index => $image): ?>
                                            <div class="carousel-item <?= $index == 0 ? 'active' : '' ?>">
                                                <img src="<?= htmlspecialchars($image['image_path']); ?>" class="d-block w-100 carousel-img" alt="Room Image">
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselRoom<?= $room['id']; ?>" data-bs-slide="prev">
                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Previous</span>
                                    </button>
                                    <button class="carousel-control-next custom-carousel-control" type="button" data-bs-target="#carouselRoom<?= $room['id']; ?>" data-bs-slide="next">
                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Next</span>
                                    </button>
                                </div>
                            <?php endif; ?>

                            <div class="card-body">
                                <h5 class="card-title">Будиночок <?= htmlspecialchars($room['room_number']); ?></h5>
                                <p class="card-text">Кількість осіб: <?= htmlspecialchars($room['room_type']); ?></p>
                                <p class="card-text">Ціна: $<?= htmlspecialchars($room['price']); ?> за добу</p>
                                <a href="booking.php?room_id=<?= $room['id']; ?>" class="btn custom-booking-btn mt-3">ЗАБРОНЮВАТИ</a>
                            </div>
                            
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/../views/templates/footer.php'; ?>
</div> <!-- Закриваємо .scrollable-content -->