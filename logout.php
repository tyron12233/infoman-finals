<?php
// logout.php

// --- Session Configuration & Start ---
// Must be called before any session operations.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Session keys (should match login and dashboard scripts)
$user_session_key = "user_id";
$user_name_session_key = "user_full_name";
$user_role_session_key = "user_role";

// --- Clear Specific Session Variables ---
// It's good practice to unset specific variables you've set.
unset($_SESSION[$user_session_key]);
unset($_SESSION[$user_name_session_key]);
unset($_SESSION[$user_role_session_key]);

// --- Destroy The Entire Session ---
// This removes all session data.
session_destroy();

// --- Clear The Session Cookie (Optional but Recommended for Completeness) ---
// Though session_destroy() often handles this, explicitly clearing the cookie
// can be a good fallback.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000, // Set expiry in the past
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// --- Redirect to Login Page ---
// Redirect back to the login page (or the main router index.php).
// Ensure the path is correct. Since logout.php is in the root,
// login is in /login/index.php
header("Location: login/index.php"); // Or use "index.php" to go through main router
exit; // Important to prevent further script execution after redirect.
?>