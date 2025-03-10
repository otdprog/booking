<?php
require_once __DIR__ . '/../DB.php';

class RoomDAO
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = DB::getInstance()->getConnection();
    }

    // Отримання всіх кімнат
    public function getAllRooms()
    {
        $sql = "SELECT * FROM rooms";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Отримання конкретної кімнати
    public function getRoomById($id)
    {
        $sql = "SELECT * FROM rooms WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Додавання нової кімнати
    public function addRoom($room_number, $room_type, $price, $description)
    {
        // Перевіряємо, чи існує вже такий будиночок
        if ($this->roomExists($room_number)) {
            return "Помилка: Будиночок із таким номером вже існує!";
        }
        $sql =
            "INSERT INTO rooms (room_number, room_type, price, description) VALUES (?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $room_number,
            $room_type ?? 'Невідомий',
            $price,
            $description,
        ])
            ? $this->pdo->lastInsertId()
            : false;
    }

    // Оновлення кімнати
    public function updateRoom(
        $id,
        $room_number,
        $room_type,
        $price,
        $description
    ) {
        $sql =
            "UPDATE rooms SET room_number = ?, room_type = ?, price = ?, description = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $room_number,
            $room_type,
            $price,
            $description,
            $id,
        ]);
    }

    // Видалення кімнати
    public function deleteRoom($id)
    {
        $sql = "DELETE FROM rooms WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }

    // Додавання фото кімнат
    public function addRoomImages($roomId, $imagePaths)
    {
        $sql = "INSERT INTO room_images (room_id, image_path) VALUES (?, ?)";
        $stmt = $this->pdo->prepare($sql);

        foreach ($imagePaths as $path) {
            $stmt->execute([$roomId, $path]);
        }
    }

    // Отримання всіх фото кімнати
    public function getRoomImages($roomId)
    {
        $sql = "SELECT id, image_path FROM room_images WHERE room_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$roomId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Отримання конкретного зображення кімнати
    public function getRoomImageById($imageId)
    {
        $sql = "SELECT id, image_path FROM room_images WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$imageId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Видалення зображення кімнати
    public function deleteRoomImage($imageId)
    {
        $sql = "DELETE FROM room_images WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$imageId]);
    }
    public function roomExists($roomNumber)
    {
        $sql = "SELECT COUNT(*) FROM rooms WHERE room_number = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$roomNumber]);
        return $stmt->fetchColumn() > 0;
    }
}
