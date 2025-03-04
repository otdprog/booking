<?php
require_once __DIR__ . '/../dao/BookingDAO.php';

class AdminController {
    private $bookingDAO;

    public function __construct() {
        $this->bookingDAO = new BookingDAO();
    }

    public function getAllBookings() {
        return $this->bookingDAO->getAllBookings();
    }

    public function updateBookingStatus($bookingId, $status) {
        return $this->bookingDAO->updateBookingStatus($bookingId, $status);
    }
}