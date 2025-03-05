<?php
require_once __DIR__ . '/../DB.php';

class BookingDAO {
    private $pdo;

    public function __construct() {
        $this->pdo = DB::getInstance()->getConnection();
    }

    // Додавання гостьового бронювання
    public function addGuestBooking($guestEmail, $guestPhone, $roomId, $checkIn, $checkOut) {
        $sql = "INSERT INTO bookings (guest_email, guest_phone, room_id, check_in, check_out, status) 
                VALUES (?, ?, ?, ?, ?, 'pending')";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$guestEmail, $guestPhone, $roomId, $checkIn, $checkOut]);
    }

    // Отримання всіх бронювань (для адмінки)
    public function getAllBookings() {
        $sql = "SELECT bookings.id, 
                       bookings.guest_email, 
                       bookings.guest_phone, 
                       rooms.room_number, 
                       bookings.check_in, 
                       bookings.check_out, 
                       bookings.status 
                FROM bookings
                JOIN rooms ON bookings.room_id = rooms.id
                ORDER BY bookings.check_in ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Фільтрація бронювань (по статусу або контакту гостя)
    public function getFilteredBookings($limit, $offset, $status = null, $guestContact = null) {
        $sql = "SELECT bookings.*, rooms.room_number 
                FROM bookings 
                LEFT JOIN rooms ON bookings.room_id = rooms.id
                WHERE 1=1";
        $params = [];

        if ($status) {
            $sql .= " AND bookings.status = ?";
            $params[] = $status;
        }

        if ($guestContact) {
            $sql .= " AND (bookings.guest_email LIKE ? OR bookings.guest_phone LIKE ?)";
            $params[] = "%$guestContact%";
            $params[] = "%$guestContact%";
        }

        $sql .= " ORDER BY bookings.check_in DESC LIMIT " . (int) $limit . " OFFSET " . (int) $offset;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Підтвердження бронювання адміністратором
    public function confirmBooking($bookingId) {
   // error_log("BookingDAO: Updating booking ID " . $bookingId);

    $sql = "UPDATE bookings SET status = 'confirmed' WHERE id = ?";
    $stmt = $this->pdo->prepare($sql);
    $result = $stmt->execute([$bookingId]);

    

    return $result;
}

    // Оновлення бронювання
    public function updateBooking($bookingId, $checkIn, $checkOut, $status) {
        $sql = "UPDATE bookings SET check_in = ?, check_out = ?, status = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$checkIn, $checkOut, $status, $bookingId]);
    }

    // Видалення бронювання
    public function deleteBooking($bookingId) {
        $sql = "DELETE FROM bookings WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$bookingId]);
    }

    // Підрахунок кількості бронювань (для пагінації)
    public function countBookings($filterStatus = null, $filterGuest = null) {
        $sql = "SELECT COUNT(*) as total FROM bookings";
        $params = [];
        $conditions = [];

        if ($filterStatus) {
            $conditions[] = "status = ?";
            $params[] = $filterStatus;
        }

        if ($filterGuest) {
            $conditions[] = "(guest_email LIKE ? OR guest_phone LIKE ?)";
            $params[] = "%$filterGuest%";
            $params[] = "%$filterGuest%";
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
    
    public function getBookedDates($roomId) {
    $sql = "SELECT check_in, check_out FROM bookings WHERE room_id = ? AND status = 'confirmed'";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([$roomId]);

    $dates = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $start = new DateTime($row['check_in']);
        $end = new DateTime($row['check_out']);
        
        while ($start <= $end) {
            $dates[] = $start->format('Y-m-d');
            $start->modify('+1 day');
        }
    }

    return $dates;
}
public function isRoomAvailable($roomId, $checkIn, $checkOut) {
    $sql = "SELECT COUNT(*) FROM bookings 
            WHERE room_id = ? 
            AND status = 'confirmed' 
            AND (check_in <= ? AND check_out >= ?)";
    $stmt = $this->pdo->prepare($sql);

    $stmt->execute([$roomId, $checkOut, $checkIn]);
    
    return $stmt->fetchColumn() == 0;
}
public function getBookingById($id) {
    $sql = "SELECT * FROM bookings WHERE id = ?";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
public function getConflictingBookings($bookingId, $checkIn, $checkOut) {
    $stmt = $this->pdo->prepare("SELECT * FROM bookings WHERE id != :bookingId AND (
        (check_in BETWEEN :checkIn AND :checkOut) OR 
        (check_out BETWEEN :checkIn AND :checkOut) OR 
        (check_in <= :checkIn AND check_out >= :checkOut)
    )");
    $stmt->execute([
        'bookingId' => $bookingId,
        'checkIn' => $checkIn,
        'checkOut' => $checkOut
    ]);
    return $stmt->fetchAll();
}

}