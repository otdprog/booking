<?php
require_once __DIR__ . '/../dao/GalleryDAO.php';

class GalleryController
{
    private $galleryDAO;

    public function __construct()
    {
        $this->galleryDAO = new GalleryDAO();
    }
    public function addPhoto($data, $files)
{
    // Відладковий лог
    error_log("POST: " . print_r($data, true));
    error_log("FILES: " . print_r($files, true));

    if (!isset($data['title']) || empty($data['title']) ||
        !isset($data['description']) || empty($data['description']) ||
        !isset($data['category_id']) || empty($data['category_id']) ||
        empty($files['image']['name'])
    ) {
        $_SESSION['message'] = "Усі поля є обов'язковими.";
        header("Location: gallery.php?category_id=" . intval($data['category_id']));
        exit();
    }

    // Завантаження файлу
    $imagePath = $this->uploadImage($files['image']);

    if (!$imagePath) {
        $_SESSION['message'] = "Помилка завантаження зображення.";
        header("Location: gallery.php?category_id=" . intval($data['category_id']));
        exit();
    }

    // Збереження у базі
    $success = $this->galleryDAO->addPhoto(
        $data['title'],
        $data['description'],
        $imagePath,
        intval($data['category_id']) // Фіксований параметр
    );

    $_SESSION['message'] = $success ? "Фото додано!" : "Помилка додавання фото.";
    header("Location: gallery.php?category_id=" . intval($data['category_id']));
    exit();
}
    public function addCategory($categoryName)
    {
        if (empty($categoryName)) {
            $_SESSION['message'] = "Назва категорії обов'язкова.";
            header("Location: admin.php");
            exit();
        }

        $success = $this->galleryDAO->addCategory($categoryName);

        $_SESSION['message'] = $success
            ? "Категорію додано!"
            : "Помилка додавання категорії.";
        header("Location: admin.php");
        exit();
    }

    public function deleteCategory($categoryId)
    {
        if (empty($categoryId) || !is_numeric($categoryId)) {
            $_SESSION['message'] = "Помилка: некоректний ID категорії.";
            header("Location: admin.php");
            exit();
        }

        $success = $this->galleryDAO->deleteCategory($categoryId);

        $_SESSION['message'] = $success
            ? "Категорію видалено!"
            : "Помилка при видаленні категорії.";
        header("Location: admin.php");
        exit();
    }

    public function getPhotosByCategory($categoryId)
    {
        if (empty($categoryId) || !is_numeric($categoryId)) {
            return [];
        }
        return $this->galleryDAO->getPhotosByCategory($categoryId);
    }

    public function getCategories() {
    $categories = $this->galleryDAO->getCategories();
    return is_array($categories) ? $categories : [];
}

    public function getCategoryById($categoryId)
    {
        if (empty($categoryId) || !is_numeric($categoryId)) {
            return null;
        }
        return $this->galleryDAO->getCategoryById($categoryId);
    }
    private function uploadImage($file)
{
    $uploadDir = __DIR__ . "/../../public/galery/";

    // Створюємо папку, якщо вона не існує
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Генеруємо унікальне ім'я файлу
    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = uniqid() . "." . $fileExtension;
    $targetFile = $uploadDir . $fileName;

    // Перевіряємо тип файлу
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array(strtolower($fileExtension), $allowedTypes)) {
        return false;
    }

    // Завантажуємо файл
    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        return "galery/" . $fileName;
    } else {
        return false;
    }
}
public function deletePhoto($photoId)
{
    if (empty($photoId) || !is_numeric($photoId)) {
        return false;
    }

    return $this->galleryDAO->deletePhoto($photoId);
}

public function getAllPhotos()
{
    return $this->galleryDAO->getAllPhotos();
}

}
?>
