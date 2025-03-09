<?php
require_once __DIR__ . '/../dao/BookingDAO.php';

class BookingController
{
      private $bookingDAO;

      public function __construct()
      {
            $this->bookingDAO = new BookingDAO();
      }

      public function createBooking($data)
      {
            if (
                  !isset(
                        $data['room_id'],
                        $data['check_in'],
                        $data['guest_email'],
                        $data['guest_phone']
                  )
            ) {
                  return [
                        'status' => 'danger',
                        'message' => 'Усі поля є обов’язковими.',
                  ];
            }

            $roomId = intval($data['room_id']);
            $checkIn = $data['check_in'];
            $checkOut = $data['check_out'] ?? $checkIn; // Якщо не вказано check_out, бронюємо лише один день
            if (empty($checkIn) || empty($checkOut)) {
                  return [
                        'status' => 'danger',
                        'message' => 'Дата заїзду та виїзду є обов’язковими!',
                  ];
            }

            if ($checkIn < date('Y-m-d')) {
                  return [
                        'status' => 'danger',
                        'message' => 'Дата заїзду не може бути в минулому.',
                  ];
            }

            $isBooked = $this->bookingDAO->addGuestBooking(
                  $data['guest_email'],
                  $data['guest_phone'],
                  $roomId,
                  $checkIn,
                  $checkOut
            );

            if ($isBooked) {
                  $this->sendTelegramNotification([
                        'room_number' => $roomId,
                        'check_in' => $checkIn,
                        'check_out' => $checkOut,
                        'guest_email' => $data['guest_email'],
                        'guest_phone' => $data['guest_phone'],
                  ]);

                  return [
                        'status' => 'success',
                        'message' => 'Бронювання успішне!',
                  ];
            }

            return [
                  'status' => 'danger',
                  'message' => 'Помилка бронювання.',
            ];
      }
      // Отримання всіх бронювань (для адмінки)
      public function getAllBookings()
      {
            return $this->bookingDAO->getAllBookings();
      }

      // Фільтровані бронювання (гостьові)
      public function getFilteredBookings(
            $limit,
            $offset,
            $status = null,
            $guestContact = null,
            $sortColumn = 'id',
            $sortOrder = 'ASC'
      ) {
            return $this->bookingDAO->getFilteredBookings(
                  $limit,
                  $offset,
                  $status,
                  $guestContact,
                  $sortColumn,
                  $sortOrder
            );
      }

      // Підтвердження бронювання
      public function confirmBooking($bookingId)
      {
            error_log(
                  "BookingController: Trying to confirm booking ID: " .
                        $bookingId
            );

            $result = $this->bookingDAO->confirmBooking($bookingId);

            if ($result !== true) {
                  error_log("Booking confirmation failed: " . $result);
                  return $result; // Якщо це помилка (конфлікт), повертаємо її
            }

            return "Booking confirmed successfully!";
      }

      public function updateBooking($data)
      {
            if (
                  !isset(
                        $data['id'],
                        $data['room_id'],
                        $data['check_in'],
                        $data['check_out'],
                        $data['status']
                  )
            ) {
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

            $conflictingBookings = $this->bookingDAO->getConflictingBookings(
                  $data['id'],
                  $data['room_id'],
                  $checkIn,
                  $checkOut
            );
            error_log(
                  "Conflicting bookings found: " .
                        print_r($conflictingBookings, true)
            );

            if (!empty($conflictingBookings)) {
                  return "The selected dates are already booked. Please choose different dates.";
            }

            return $this->bookingDAO->updateBooking(
                  $data['id'],
                  $checkIn,
                  $checkOut,
                  $data['status'],
                  $adminComment
            )
                  ? "Booking updated successfully!"
                  : "Failed to update booking.";
      }
      // Видалення бронювання
      public function deleteBooking($id)
      {
            return $this->bookingDAO->deleteBooking($id)
                  ? "Бронювання видалено!"
                  : "Помилка видалення бронювання.";
      }
      public function getBookedDates($roomId)
      {
            return $this->bookingDAO->getBookedDates($roomId);
      }

      public function countBookings($status = null, $guestContact = null)
      {
            return $this->bookingDAO->countBookings($status, $guestContact);
      }
      public function getBookingById($id)
      {
            return $this->bookingDAO->getBookingById($id);
      }
      public function deleteExpiredBookings()
      {
            return $this->bookingDAO->deleteExpiredBookings()
                  ? "Expired bookings deleted successfully!"
                  : "No expired bookings found or failed to delete.";
      }
      public function updateComment($bookingId, $adminComment)
      {
            return $this->bookingDAO->updateComment($bookingId, $adminComment);
      }

      private function sendTelegramNotification($bookingDetails)
      {
            $botToken = "5830443244:AAHZRPPANOj49VT_aLvpiOES9yBcN7Rah4Y"; //  Вставити свій Telegram Token
            $chatId = "-1002418250901"; //  Вставити `chat_id` (групи або особистого чату)

            // Формуємо текст повідомлення
            // Формуємо текст повідомлення
            $message =
                  "📢 *Нове бронювання!*\n\n" .
                  "🏠 *Будиночок:* " .
                  $bookingDetails['room_number'] .
                  "\n" .
                  "📅 *Заїзд:* " .
                  $bookingDetails['check_in'] .
                  "\n" .
                  "📅 *Виїзд:* " .
                  $bookingDetails['check_out'] .
                  "\n" .
                  "📧 *Email:* " .
                  $bookingDetails['guest_email'] .
                  "\n" .
                  "📞 *Телефон:* " .
                  $bookingDetails['guest_phone'] .
                  "\n\n" .
                  "🔗 [Перейти в адмін-панель](https://yourwebsite.com/admin.php)";

            // Формуємо URL для API-запиту
            $url = "https://api.telegram.org/bot$botToken/sendMessage";
            $data = [
                  'chat_id' => $chatId,
                  'text' => $message,
                  'parse_mode' => 'Markdown',
                  'disable_web_page_preview' => true, // Відключає прев’ю посилання
            ];

            // Відправка через CURL
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_exec($ch);
            curl_close($ch);
      }
}
