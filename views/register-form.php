<form method="post" action="register.php" class="p-4 bg-white shadow rounded">
    <h2 class="text-center">Register</h2>

    <?php if (!empty($message)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <div class="mb-3">
        <label for="name" class="form-label">Full Name</label>
        <input type="text" name="name" id="name" class="form-control" required>
    </div>

    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" name="email" id="email" class="form-control" required>
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" name="password" id="password" class="form-control" required minlength="6">
    </div>

    <button type="submit" class="btn btn-success w-100">Register</button>

    <p class="text-center mt-3">
        Already have an account? <a href="login.php">Login here</a>.
    </p>
</form>