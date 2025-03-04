<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

require_once __DIR__ . '/../app/controllers/AuthController.php';

$authController = new AuthController();
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $message = $authController->register($_POST);
}

require_once __DIR__ . '/../views/templates/header.php';
?>
<div class="container">
    <h2>Register</h2>
    <?php if (!empty($message)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <?php require_once __DIR__ . '/../views/register-form.php'; ?>
</div>
<?php require_once __DIR__ . '/../views/templates/footer.php'; ?>