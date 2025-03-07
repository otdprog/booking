<?php
session_start();

// Захист від фіксації сесії (Session Fixation Attack)
if (!isset($_SESSION['last_regeneration'])) {
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 300) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// Якщо користувач не адмін – знищуємо сесію і перенаправляємо на login.php
if (empty($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    session_destroy();
    header("Location: login.php");
    exit;
}

// Захист від захоплення сесії через зміну браузера/IP
if (!isset($_SESSION['user_agent'])) {
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
} elseif ($_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
    session_destroy();
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['user_ip'])) {
    $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'];
} elseif ($_SESSION['user_ip'] !== $_SERVER['REMOTE_ADDR']) {
    session_destroy();
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../app/controllers/BookingController.php';

$bookingController = new BookingController();
$message = "";

// Перевірка, чи передано ID бронювання
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: admin.php?error=Invalid booking ID");
    exit;
}

$booking = $bookingController->getBookingById($_GET['id']);
if (!$booking) {
    header("Location: admin.php?error=Booking not found");
    exit;
}

// Обробка оновлення бронювання
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF validation failed.");
    }

    $message = $bookingController->updateBooking($_POST);

    if ($message === "Booking updated successfully!") {
        $_SESSION['success_message'] = "Booking successfully updated!";
        header("Location: edit-booking.php?id=" . $_GET['id']);
        exit;
    }
}

require_once __DIR__ . '/../views/templates/header.php';
?>
<div class="content-wrapper">
<div class="container">
    <h2 class="hello">Редагувати бронювання<a href="admin.php" class="btn btn-danger ms-2">Назад</a></h2>
    
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= strpos($message, 'success') !== false ? 'success' : 'danger' ?>">
            <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

<form method="post">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="id" value="<?= htmlspecialchars($booking['id']); ?>">
    <input type="hidden" name="room_id" value="<?= htmlspecialchars($booking['room_id']); ?>">

    <label for="check_in">Дата заїзду:</label>
    <input type="date" id="check_in" name="check_in" value="<?= htmlspecialchars($booking['check_in']); ?>" required min="<?= date('Y-m-d'); ?>">

    <label for="check_out">Дата виїзду:</label>
    <input type="date" id="check_out" name="check_out" value="<?= htmlspecialchars($booking['check_out']); ?>" required min="<?= date('Y-m-d'); ?>">

    <label for="status">Статус:</label>
    <select id="status" name="status">
        <option value="Очікує" <?= $booking['status'] == 'Очікує' ? 'selected' : ''; ?>>Очікує підтвердження</option>
        <option value="Готово" <?= $booking['status'] == 'Готово' ? 'selected' : ''; ?>>Підтверджено</option>
        <option value="Скасовано" <?= $booking['status'] == 'Скасовано' ? 'selected' : ''; ?>>Скасовано</option>
    </select>
<label for="admin_comment">Коментар менеджера:</label>
<textarea id="admin_comment" name="admin_comment" class="form-control"><?= htmlspecialchars($booking['admin_comment'] ?? ''); ?></textarea>

    <button type="submit" class="btn btn-primary btn-sm">Оновити бронювання</button>
</form>
</div>
</div>
<?php require_once __DIR__ . '/../views/templates/footer.php'; ?>