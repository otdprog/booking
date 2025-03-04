<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../app/controllers/BookingController.php';

$bookingController = new BookingController();
$bookings = $bookingController->getUserBookings($_SESSION['user_id']);

require_once __DIR__ . '/../views/templates/header.php';
?>

<div class="container">
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['user_email'], ENT_QUOTES, 'UTF-8'); ?></h2>
    <p>Your bookings:</p>

    <?php if (empty($bookings)): ?>
        <p>No bookings found.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Room Type</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($booking['id']); ?></td>
                        <td><?php echo htmlspecialchars($booking['check_in']); ?></td>
                        <td><?php echo htmlspecialchars($booking['check_out']); ?></td>
                        <td><?php echo htmlspecialchars($booking['room_type']); ?></td>
                        <td><?php echo htmlspecialchars($booking['status']); ?></td>
                        <td>
                            <?php if ($booking['status'] === 'pending'): ?>
                                <form method="post" action="cancel-booking.php">
                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Cancel</button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted">Cannot cancel</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php if (isset($_GET['message'])): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($_GET['message'], ENT_QUOTES, 'UTF-8'); ?></div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8'); ?></div>
<?php endif; ?>
<?php require_once __DIR__ . '/../views/templates/footer.php'; ?>