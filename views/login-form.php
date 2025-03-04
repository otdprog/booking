<form method="post" action="login.php" class="p-4 bg-white shadow rounded">
    <h2 class="text-center">Login</h2>
    
    <?php if (!empty($message)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" name="email" id="email" class="form-control" required>
    </div>
    
    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" name="password" id="password" class="form-control" required>
    </div>

    <button type="submit" class="btn btn-primary w-100">Login</button>

    <p class="text-center mt-3">
        Don't have an account? <a href="register.php">Register here</a>.
    </p>
</form>