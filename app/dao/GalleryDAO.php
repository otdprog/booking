<?php
require_once __DIR__ . '/../DB.php';

class GalleryDAO
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = DB::getInstance()->getConnection();
    }
    public function addPhoto($title, $description, $imagePath, $categoryId)
{
    try {
        $this->pdo->beginTransaction();

        // Додаємо фото в `gallery_photos`
        $sql = "INSERT INTO gallery_photos (title, description, image_path) VALUES (?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$title, $description, $imagePath]);

        // Отримуємо ID тільки-що доданого фото
        $photoId = $this->pdo->lastInsertId();

        // Додаємо зв’язок фото-категорія у `gallery_photo_category`
        $sql = "INSERT INTO gallery_photo_category (photo_id, category_id) VALUES (?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$photoId, $categoryId]);

        $this->pdo->commit();
        return true;
    } catch (Exception $e) {
        $this->pdo->rollBack();
        die("Помилка БД при додаванні фото: " . $e->getMessage()); // Виводимо помилку
    }
}
    public function getAllPhotos() {
    $sql = "SELECT p.id, p.title, p.description, p.image_path, p.created_at, 
                   GROUP_CONCAT(c.name SEPARATOR ', ') AS categories
            FROM gallery_photos p
            LEFT JOIN gallery_photo_category pc ON p.id = pc.photo_id
            LEFT JOIN gallery_categories c ON pc.category_id = c.id
            GROUP BY p.id
            ORDER BY p.created_at DESC";
    $stmt = $this->pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    public function getPhotosByCategory($categoryId)
    {
        $sql = "SELECT p.id, p.title, p.description, p.image_path, p.created_at
            FROM gallery_photos p
            JOIN gallery_photo_category pc ON p.id = pc.photo_id
            WHERE pc.category_id = ?
            ORDER BY p.created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$categoryId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deletePhoto($photoId)
{
    try {
        // Отримуємо дані про фото
        $sql = "SELECT image_path FROM gallery_photos WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$photoId]);
        $photo = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$photo) {
            return false; // Якщо фото не знайдено
        }

        // Видаляємо файл із сервера
        $imagePath = __DIR__ . "/../../public/" . $photo['image_path'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }

        // Видаляємо записи у `gallery_photo_category`
        $sql = "DELETE FROM gallery_photo_category WHERE photo_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$photoId]);

        // Видаляємо фото з `gallery_photos`
        $sql = "DELETE FROM gallery_photos WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$photoId]);

        return true;
    } catch (Exception $e) {
        error_log("❌ Помилка БД при видаленні фото: " . $e->getMessage());
        return false;
    }
}
    public function addCategory($name)
    {
        $sql = "INSERT INTO gallery_categories (name) VALUES (?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$name]);
    }
    public function getCategories() {
    try {
        $sql = "SELECT id, name FROM gallery_categories ORDER BY name ASC"; // Додаємо id
        $stmt = $this->pdo->query($sql);
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $categories ?: []; // Якщо немає категорій, повертаємо пустий масив
    } catch (PDOException $e) {
        error_log("Помилка БД (отримання категорій): " . $e->getMessage());
        return [];
    }
}
    public function deleteCategory($id)
    {
        try {
            $sql = "DELETE FROM gallery_categories WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);

            // Перевіряємо, чи видалено хоча б один рядок
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log(
                "Помилка БД при видаленні категорії: " . $e->getMessage()
            );
            return false;
        }
    }

    public function getCategoryById($categoryId)
    {
        $sql = "SELECT * FROM gallery_categories WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$categoryId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
