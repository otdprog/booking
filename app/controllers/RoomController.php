<?php
require_once __DIR__ . '/../dao/RoomDAO.php';

class RoomController {
    private $roomDAO;

    public function __construct() {
        $this->roomDAO = new RoomDAO();
    }

    // Отримати всі кімнати
    public function getRooms() {
        return $this->roomDAO->getAllRooms();
    }

    // Отримати конкретну кімнату
    public function getRoomById($id) {
        return $this->roomDAO->getRoomById($id);
    }

    // Отримати зображення кімнати
    public function getRoomImages($roomId) {
        return $this->roomDAO->getRoomImages($roomId);
    }

// Додавання кімнати
    public function addRoom($data, $files) {
        if (!isset($data['room_number'], $data['room_type'], $data['price'], $data['description'])) {
            $_SESSION['message'] = "Усі поля є обов’язковими.";
            header("Location: admin.php");
            exit;
        }
 $roomNumber = trim($data['room_number']);
    $roomType = trim($data['room_type']);
    $price = floatval($data['price']);
    $description = trim($data['description']);

    // Перевірка, чи існує будиночок
    $roomExists = $this->roomDAO->roomExists($roomNumber);
    if ($roomExists) {
        $_SESSION['message'] = "Помилка: Будиночок із таким номером вже існує!";
        header("Location: admin.php");
        exit;
    }
        $roomId = $this->roomDAO->addRoom($data['room_number'], $data['room_type'], $data['price'], $data['description']);

        if (!$roomId) {
            $_SESSION['message'] = "Не вдалося додати кімнату.";
            header("Location: admin.php");
            exit;
        }

        if (isset($files['images']) && !empty($files['images']['name'][0])) {
            $imagePaths = $this->uploadImages($files['images']);
            if (!empty($imagePaths)) {
                $this->roomDAO->addRoomImages($roomId, $imagePaths);
            }
        }

        $_SESSION['message'] = "Кімната успішно додана!";
        header("Location: admin.php");
        exit;
    }
    // Завантаження зображень кімнат
    private function uploadImages($files) {
        $targetDir = __DIR__ . "/../../public/uploads/";
        if (!file_exists($targetDir) && !mkdir($targetDir, 0777, true)) {
            die("Помилка створення директорії для завантаження: " . $targetDir);
        }

        if (!is_writable($targetDir)) {
            die("Каталог не має прав на запис: " . $targetDir);
        }

        $imagePaths = [];
        foreach ($files['tmp_name'] as $key => $tmpName) {
            if ($files['error'][$key] === UPLOAD_ERR_OK) {
                $fileName = uniqid() . "_" . basename($files['name'][$key]);
                $targetFile = $targetDir . $fileName;

                if (move_uploaded_file($tmpName, $targetFile)) {
                    $imagePaths[] = "uploads/" . $fileName;
                } else {
                    die("Помилка при переміщенні файлу: " . $tmpName . " -> " . $targetFile);
                }
            } else {
                die("Помилка завантаження файлу (код): " . $files['error'][$key]);
            }
        }

        return $imagePaths;
    }

// Оновлення кімнати
public function updateRoom($data, $files) {
    if (!isset($data['room_id'], $data['room_number'], $data['room_type'], $data['price'], $data['description'])) {
        $_SESSION['message'] = "Усі поля є обов’язковими.";
        header("Location: edit-room.php?id=" . $data['room_id']);
        exit;
    }

    $success = $this->roomDAO->updateRoom($data['room_id'], $data['room_number'], $data['room_type'], $data['price'], $data['description']);

    if ($success && isset($files['new_images']) && !empty($files['new_images']['name'][0])) {
        $imagePaths = $this->uploadImages($files['new_images']);
        if (!empty($imagePaths)) {
            $this->roomDAO->addRoomImages($data['room_id'], $imagePaths);
        }
    }

    $_SESSION['message'] = "Кімната успішно оновлена!";
    header("Location: edit-room.php?id=" . $data['room_id']);
    exit;
}

// Видалення кімнати
    public function deleteRoom($id) {
        return $this->roomDAO->deleteRoom($id)
            ? "Кімната успішно видалена!"
            : "Помилка видалення кімнати.";
    }

// Видалення зображення кімнати
    public function deleteRoomImage($imageId) {
        $image = $this->roomDAO->getRoomImageById($imageId);
        
        if ($image) {
            $imagePath = __DIR__ . "/../../public/" . $image['image_path'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
            return $this->roomDAO->deleteRoomImage($imageId);
        }

        return false;
    }
}