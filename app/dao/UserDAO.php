<?php
require_once __DIR__ . '/../DB.php';

class UserDAO {
    private $pdo;

    public function __construct() {
        $this->pdo = DB::getInstance()->getConnection();
    }

    // Отримує адміністратора за email
    public function getUserByEmail($email) {
        $sql = "SELECT id, email, password, is_admin FROM users WHERE email = ? LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}