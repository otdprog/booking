<?php
require_once __DIR__ . '/../dao/BookingDAO.php';

class BookingController {
    private $bookingDAO;

    public function __construct() {
        $this->bookingDAO = new BookingDAO();
    }

    public function createBooking($data) {
        if (!isset($data['room_id'], $data['check_in'], $data['guest_email'], $data['guest_phone'])) {
            return ['status' => 'danger', 'message' => 'Усі поля є обов’язковими.'];
        }

        $roomId = intval($data['room_id']);
        $checkIn = $data['check_in'];
        $checkOut = $data['check_out'] ?? $checkIn; // Якщо не вказано check_out, бронюємо лише один день

        if (empty($checkIn) || empty($checkOut)) {
            return ['status' => 'danger', 'message' => 'Дата заїзду та виїзду є обов’язковими!'];
        }

        if ($checkIn < date('Y-m-d')) {
            return ['status' => 'danger', 'message' => 'Дата заїзду не може бути в минулому.'];
        }

        return $this->bookingDAO->addGuestBooking($data['guest_email'], $data['guest_phone'], $roomId, $checkIn, $checkOut)
            ? ['status' => 'success', 'message' => 'Бронювання успішне!']
            : ['status' => 'danger', 'message' => 'Помилка бронювання.'];
    }
    // Отримання всіх бронювань (для адмінки)
    public function getAllBookings() {
        return $this->bookingDAO->getAllBookings();
    }

    // Фільтровані бронювання (гостьові)
public function getFilteredBookings($limit, $offset, $status = null, $guestContact = null, $sortColumn = 'id', $sortOrder = 'ASC') {
    return $this->bookingDAO->getFilteredBookings($limit, $offset, $status, $guestContact, $sortColumn, $sortOrder);
}

    // Підтвердження бронювання
public function confirmBooking($bookingId) {
    error_log("BookingController: Trying to confirm booking ID: " . $bookingId);

    $result = $this->bookingDAO->confirmBooking($bookingId);

    if ($result !== true) {
        error_log("Booking confirmation failed: " . $result);
        return $result; // Якщо це помилка (конфлікт), повертаємо її
    }

    return "Booking confirmed successfully!";
}

public function updateBooking($data) {
  

    if (!isset($data['id'], $data['room_id'], $data['check_in'], $data['check_out'], $data['status'])) {
        return "All fields are required.";
    }

    $checkIn = $data['check_in'];
    $checkOut = $data['check_out'];
    $adminComment = $data['admin_comment'] ?? null;

    if ($checkIn < date('Y-m-d')) {
        return "Check-in date cannot be in the past.";
    }

    if ($checkOut < $checkIn) {
        return "Check-out date must be after check-in date.";
    }

    $conflictingBookings = $this->bookingDAO->getConflictingBookings($data['id'], $data['room_id'], $checkIn, $checkOut);
    error_log("Conflicting bookings found: " . print_r($conflictingBookings, true));

    if (!empty($conflictingBookings)) {
        return "The selected dates are already booked. Please choose different dates.";
    }

    return $this->bookingDAO->updateBooking($data['id'], $checkIn, $checkOut, $data['status'], $adminComment)
        ? "Booking updated successfully!"
        : "Failed to update booking.";
}
    // Видалення бронювання
 public function deleteBooking($id) {
        return $this->bookingDAO->deleteBooking($id)
            ? "Бронювання видалено!"
            : "Помилка видалення бронювання.";
    }
    public function getBookedDates($roomId) {
    return $this->bookingDAO->getBookedDates($roomId);
}

public function countBookings($status = null, $guestContact = null) {
    return $this->bookingDAO->countBookings($status, $guestContact);
}
public function getBookingById($id) {
    return $this->bookingDAO->getBookingById($id);
}
public function deleteExpiredBookings() {
    return $this->bookingDAO->deleteExpiredBookings()
        ? "Expired bookings deleted successfully!"
        : "No expired bookings found or failed to delete.";
}
    public function updateComment($bookingId, $adminComment) {
    return $this->bookingDAO->updateComment($bookingId, $adminComment);
}
}