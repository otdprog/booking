<div class="container">
    <h2 class="text-center">Admin Dashboard</h2>
    <table class="table table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Email</th>
                <th>Check-in</th>
                <th>Check-out</th>
                <th>Room Type</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($bookings as $booking): ?>
                <tr>
                    <td><?php echo htmlspecialchars($booking['id']); ?></td>
                    <td><?php echo htmlspecialchars($booking['email']); ?></td>
                    <td><?php echo htmlspecialchars($booking['check_in']); ?></td>
                    <td><?php echo htmlspecialchars($booking['check_out']); ?></td>
                    <td><?php echo htmlspecialchars($booking['room_type']); ?></td>
                    <td>
                        <span class="badge bg-<?php echo $booking['status'] == 'confirmed' ? 'success' : 'warning'; ?>">
                            <?php echo htmlspecialchars($booking['status']); ?>
                        </span>
                    </td>
                    <td>
                        <form method="post" action="update-booking.php">
                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                            <select name="status" class="form-select">
                                <option value="confirmed">Confirm</option>
                                <option value="cancelled">Cancel</option>
                            </select>
                            <button type="submit" class="btn btn-primary btn-sm">Update</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>