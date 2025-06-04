<?php
/**
 * @var string $login_error Any error message from login attempt.
 * @var string $submitted_username The username submitted in the form (for repopulating).
 */
?>
<div class="login-container">
    <div class="logo">4031 CAFE</div>
    <h2>POS Login</h2>

    <?php if (!empty($login_error)): ?>
        <div class="error-message"><?php echo htmlspecialchars($login_error); ?></div>
    <?php endif; ?>

    <form action="" method="POST"> <?php // Action is empty, will submit to the current page (login/index.php) ?>
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required
                value="<?php echo htmlspecialchars($submitted_username ?? ''); ?>">
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="login-btn">Login</button>
    </form>
    <p class="footer-text">&copy; <?php echo date("Y"); ?> 4031 Cafe. All rights reserved.</p>
</div>