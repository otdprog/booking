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
    <h2>Edit Booking<a href="admin.php" class="btn btn-danger ms-2">Back</a></h2>
    
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= strpos($message, 'success') !== false ? 'success' : 'danger' ?>">
            <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
        <input type="hidden" name="id" value="<?= htmlspecialchars($booking['id']); ?>">

        <label for="check_in">Check-in Date:</label>
        <input type="date" id="check_in" name="check_in" value="<?= htmlspecialchars($booking['check_in']); ?>" required min="<?= date('Y-m-d'); ?>">

        <label for="check_out">Check-out Date:</label>
        <input type="date" id="check_out" name="check_out" value="<?= htmlspecialchars($booking['check_out']); ?>" required min="<?= date('Y-m-d'); ?>">

        <label for="status">Status:</label>
        <select id="status" name="status">
            <option value="pending" <?= $booking['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
            <option value="confirmed" <?= $booking['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
            <option value="cancelled" <?= $booking['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
        </select>

        <button type="submit" class="btn btn-primary btn-sm">Update Booking</button>
    </form>
</div>
</div>
<?php require_once __DIR__ . '/../views/templates/footer.php'; ?>