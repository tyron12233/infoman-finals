<?php
// includes/auth_functions.php

/**
 * Starts a secure session if one is not already active.
 * Configures session cookie parameters for security.
 */
function startSecureSession(): void
{
    if (session_status() == PHP_SESSION_NONE) {
        // More secure session cookie parameters (consider your needs)
        // ini_set('session.cookie_httponly', 1); // Helps prevent XSS
        // ini_set('session.use_only_cookies', 1); // Prevents session ID in URL
        // ini_set('session.cookie_secure', isset($_SERVER['HTTPS'])); // Send cookie only over HTTPS
        // ini_set('session.cookie_samesite', 'Lax'); // Or 'Strict' for more security

        session_start();
    }
}

/**
 * Checks if a user is currently logged in.
 * @return bool True if logged in, false otherwise.
 */
function isLoggedIn(): bool
{
    startSecureSession(); // Ensure session is started
    return isset($_SESSION[SESSION_USER_ID_KEY]) && !empty($_SESSION[SESSION_USER_ID_KEY]);
}

/**
 * Attempts to log in a user.
 *
 * @param mysqli $mysqli The database connection object.
 * @param string $username The username to check.
 * @param string $password The password to verify.
 * @return array ['success' => bool, 'message' => string, 'user_data' => array|null]
 */
function attemptLogin(mysqli $mysqli, string $username, string $password): array
{
    if (empty($username) || empty($password)) {
        return ['success' => false, 'message' => "Username and password are required.", 'user_data' => null];
    }

    $stmt = $mysqli->prepare("SELECT id, username, password_hash, full_name, role FROM users WHERE username = ?");
    if (!$stmt) {
        error_log("MySQLi prepare failed in attemptLogin: " . $mysqli->error);
        return ['success' => false, 'message' => "Login error. Please try again later (DB prepare failed).", 'user_data' => null];
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password_hash'])) {
            // Authentication successful
            $_SESSION[SESSION_USER_ID_KEY] = $user['id'];
            $_SESSION[SESSION_USER_NAME_KEY] = $user['full_name'] ?: $user['username'];
            $_SESSION[SESSION_USER_ROLE_KEY] = $user['role'];

            session_regenerate_id(true); // Regenerate session ID for security

            $stmt->close();
            return ['success' => true, 'message' => "Login successful.", 'user_data' => $user];
        } else {
            // Password verification failed
            $stmt->close();
            return ['success' => false, 'message' => "Invalid username or password.", 'user_data' => null];
        }
    } else {
        // Username not found
        $stmt->close();
        return ['success' => false, 'message' => "Invalid username or password.", 'user_data' => null];
    }
}

/**
 * Redirects the user to a specified URL and exits the script.
 *
 * @param string $url The URL to redirect to.
 * If BASE_URL is defined and $url is relative (doesn't start with http),
 * it will be prepended. Otherwise, $url is used as is.
 */
function redirect(string $url): void
{
    if (defined('BASE_URL') && !preg_match('/^https?:\/\//', $url)) {
        $final_url = rtrim(BASE_URL, '/') . '/' . ltrim($url, '/');
    } else {
        $final_url = $url;
    }
    header("Location: " . $final_url);
    exit;
}

/**
 * Logs out the current user by destroying the session.
 */
function logoutUser(): void
{
    startSecureSession(); // Ensure session is started

    // Unset all session variables
    $_SESSION = [];

    // If it's desired to kill the session, also delete the session cookie.
    // Note: This will destroy the session, not just the session data!
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    // Finally, destroy the session.
    session_destroy();
}
?>