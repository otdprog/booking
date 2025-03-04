<?php
require_once __DIR__ . '/../dao/UserDAO.php';

class AuthController {
    private $userDAO;

    public function __construct() {
        $this->userDAO = new UserDAO();
    }

    // Авторизація адміністратора
    public function login($data) {
        if (!isset($data['email'], $data['password'])) {
            return "Email and password are required.";
        }

        $email = filter_var($data['email'], FILTER_VALIDATE_EMAIL);
        $password = $data['password'];

        if (!$email) {
            return "Invalid email format.";
        }

        $user = $this->userDAO->getUserByEmail($email);

        // Авторизуємо тільки адміністратора
        if ($user && password_verify($password, $user['password']) && $user['is_admin']) {
            session_start();

// Захист сесії від атак XSS та крадіжки даних
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1); // Використовувати тільки на HTTPS
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Strict');

// Оновлення session_id після входу
session_regenerate_id(true);

$_SESSION['admin_email'] = $user['email'];
$_SESSION['is_admin'] = true;
$_SESSION['last_regeneration'] = time();

header("Location: admin.php");
exit;
        }

        return "Invalid credentials or access denied.";
    }
}