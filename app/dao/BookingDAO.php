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
                VALUES (?, ?, ?, ?, ?, 'Очікує')";
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
public function getFilteredBookings($limit, $offset, $status = null, $guestContact = null, $sortColumn = 'id', $sortOrder = 'ASC') {
    // Дозволені колонки для сортування
    $allowedSortColumns = ['id', 'guest_email', 'guest_phone', 'room_number', 'check_in', 'check_out', 'status'];

    // Сортування для `room_number`
    if ($sortColumn === 'room_number') {
        $sortColumn = 'rooms.room_number'; // Використовуємо колонку з таблиці rooms
    }
    // Сортування для номера телефону як число
    elseif ($sortColumn === 'guest_phone') {
        $sortColumn = "CAST(bookings.guest_phone AS UNSIGNED)";
    }
    // Перевірка на SQL-ін'єкцію
    elseif (!in_array($sortColumn, $allowedSortColumns)) {
        $sortColumn = 'id';
    }

    $query = "SELECT bookings.*, rooms.room_number FROM bookings 
              LEFT JOIN rooms ON bookings.room_id = rooms.id
              WHERE 1=1";

    if ($status) {
        $query .= " AND bookings.status = :status";
    }
    if ($guestContact) {
        $query .= " AND (bookings.guest_email LIKE :guest OR bookings.guest_phone LIKE :guest)";
    }

    // Додаємо сортування
    $query .= " ORDER BY $sortColumn $sortOrder LIMIT :limit OFFSET :offset";

    $stmt = $this->pdo->prepare($query);

    if ($status) {
        $stmt->bindParam(':status', $status);
    }
    if ($guestContact) {
        $searchTerm = "%$guestContact%";
        $stmt->bindParam(':guest', $searchTerm);
    }

    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

     // Підтвердження бронювання адміністратором
public function confirmBooking($bookingId) {
    // Отримуємо дані бронювання
    $sql = "SELECT room_id, check_in, check_out FROM bookings WHERE id = ?";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([$bookingId]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        return "Booking not found.";
    }

    $roomId = $booking['room_id'];
    $checkIn = $booking['check_in'];
    $checkOut = $booking['check_out'];

    // Перевіряємо, чи є конфліктне бронювання
    $conflictSql = "SELECT COUNT(*) FROM bookings 
                    WHERE room_id = ? 
                    AND status = 'Готово' 
                    AND id != ? 
                    AND (check_in <= ? AND check_out >= ?)";
    $conflictStmt = $this->pdo->prepare($conflictSql);
    $conflictStmt->execute([$roomId, $bookingId, $checkOut, $checkIn]);

    if ($conflictStmt->fetchColumn() > 0) {
        return "Error: The room is already booked for this period.";
    }

    // Якщо конфлікту немає, оновлюємо статус бронювання
    $updateSql = "UPDATE bookings SET status = 'Готово' WHERE id = ?";
    $updateStmt = $this->pdo->prepare($updateSql);
    
    return $updateStmt->execute([$bookingId]) ? true : "Failed to confirm booking.";
}
    // Оновлення бронювання
  public function updateBooking($bookingId, $checkIn, $checkOut, $status, $adminComment) {
    $sql = "UPDATE bookings SET check_in = ?, check_out = ?, status = ?, admin_comment = ? WHERE id = ?";
    $stmt = $this->pdo->prepare($sql);
    return $stmt->execute([$checkIn, $checkOut, $status, $adminComment, $bookingId]);
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
    $sql = "SELECT check_in, check_out FROM bookings WHERE room_id = ? AND status = 'Готово'";
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
            AND status = 'Готово' 
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
public function getConflictingBookings($bookingId, $roomId, $checkIn, $checkOut) {
    $sql = "SELECT * FROM bookings 
            WHERE id != :bookingId 
            AND room_id = :roomId
            AND status = 'Готово'
            AND (
                (check_in <= :checkOut AND check_out >= :checkIn)
            )";
    
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([
        'bookingId' => $bookingId,
        'roomId' => $roomId,
        'checkIn' => $checkIn,
        'checkOut' => $checkOut
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
public function deleteExpiredBookings() {
    $sql = "DELETE FROM bookings WHERE check_out < CURDATE()";
    $stmt = $this->pdo->prepare($sql);
    return $stmt->execute();
}
public function updateComment($bookingId, $adminComment) {
    $sql = "UPDATE bookings SET admin_comment = ? WHERE id = ?";
    $stmt = $this->pdo->prepare($sql);
    return $stmt->execute([$adminComment, $bookingId]);
}

}