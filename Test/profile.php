<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../app/controllers/ProfileController.php';

$profileController = new ProfileController();
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $message = $profileController->updateProfile($_SESSION['user_id'], $_POST);
}

require_once __DIR__ . '/../views/templates/header.php';
?>
<div class="container">
    <h2>Edit Profile</h2>
    <?php if (!empty($message)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <form method="post">
        <label>Name:</label>
        <input type="text" name="name" required>
        <label>Email:</label>
        <input type="email" name="email" required>
        <button type="submit">Save Changes</button>
    </form>
</div>
<?php require_once __DIR__ . '/../views/templates/footer.php'; ?>