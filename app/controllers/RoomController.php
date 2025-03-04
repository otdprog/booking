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
    if (!isset($data['room_number'], $data['room_type'], $data['price'])) {
        $_SESSION['message'] = "All fields are required.";
        header("Location: admin.php");
        exit;
    }

    // Додаємо кімнату в базу даних
    $roomId = $this->roomDAO->addRoom($data['room_number'], $data['room_type'], $data['price']);

    // Якщо додано успішно, перевіряємо наявність зображень
    if ($roomId && isset($files['images']) && !empty($files['images']['name'][0])) {
        $imagePaths = $this->uploadImages($files['images']);
        if (!empty($imagePaths)) {
            $this->roomDAO->addRoomImages($roomId, $imagePaths);
        }
    }

    // Повідомлення про успіх і перенаправлення
    if ($roomId) {
        $_SESSION['message'] = "Room added successfully!";
    } else {
        $_SESSION['message'] = "Failed to add room.";
    }

    header("Location: admin.php");
    exit;
}

    // Завантаження зображень кімнат
private function uploadImages($files) {
    $targetDir = __DIR__ . "/../../public/uploads/";
if (!file_exists($targetDir)) {
    if (!mkdir($targetDir, 0777, true)) {
        die("Не вдалося створити директорію для завантаження: " . $targetDir);
    }
}

if (!is_writable($targetDir)) {
    die("Каталог не має прав на запис: " . $targetDir);
}
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
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
    if (!isset($data['room_id'], $data['room_number'], $data['room_type'], $data['price'])) {
        $_SESSION['message'] = "All fields are required.";
        header("Location: edit-room.php?id=" . $data['room_id']);
        exit;
    }

    // Оновлення даних кімнати
    $success = $this->roomDAO->updateRoom($data['room_id'], $data['room_number'], $data['room_type'], $data['price']);

    // Якщо є нові зображення, завантажуємо їх
if ($success && isset($files['new_images']) && !empty($files['new_images']['name'][0])) {


    $imagePaths = $this->uploadImages($files['new_images']);
    if (!empty($imagePaths)) {
        $this->roomDAO->addRoomImages($data['room_id'], $imagePaths);
    }
}

    $_SESSION['message'] = "Room updated successfully!";
    header("Location: edit-room.php?id=" . $data['room_id']);
    exit;
}

// Видалення кімнати
public function deleteRoom($id) {
    return $this->roomDAO->deleteRoom($id)
        ? "Room deleted successfully!"
        : "Failed to delete room.";
}

// Видалення зображення кімнати
public function deleteRoomImage($imageId) {
    $image = $this->roomDAO->getRoomImageById($imageId);
    
    if ($image) {
        $imagePath = __DIR__ . "/../../public/" . $image['image_path'];
        
        if (file_exists($imagePath)) {
            unlink($imagePath); // Видаляємо файл
        }

        return $this->roomDAO->deleteRoomImage($imageId);
    }

    return false;
}
}