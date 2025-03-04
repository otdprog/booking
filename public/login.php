<?php
session_start();
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
    header("Location: admin.php");
    exit;
}

require_once __DIR__ . '/../app/controllers/AuthController.php';

$authController = new AuthController();
$message = "";

// Обробка авторизації
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $message = $authController->login($_POST);
}

require_once __DIR__ . '/../views/templates/header.php';
?>
<div class="content-wrapper">
<div class="container">
    <h2>Admin Login</h2>

    <?php if (!empty($message)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <form method="post" class="p-4 bg-white shadow rounded">
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" id="email" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>
</div>
</div>

<?php require_once __DIR__ . '/../views/templates/footer.php'; ?>