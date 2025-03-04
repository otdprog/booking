<form action="booking.php" method="post">
    <div class="form-group">
        <label for="email">Email:</label>
        <input type="email" name="email" id="email" class="form-control" required>
    </div>
    <div class="form-group">
        <label for="check_in">Check-in Date:</label>
        <input type="date" name="check_in" id="check_in" class="form-control" required>
    </div>
    <div class="form-group">
        <label for="check_out">Check-out Date:</label>
        <input type="date" name="check_out" id="check_out" class="form-control" required>
    </div>
    <div class="form-group">
        <label for="room_type">Room Type:</label>
        <select name="room_type" id="room_type" class="form-control">
            <option value="single">Single</option>
            <option value="double">Double</option>
            <option value="suite">Suite</option>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Book Now</button>
</form>