<?php
// login/index.php

// --- Session Configuration & Start ---
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$user_session_key = "user_id"; // Session key for storing authenticated user's ID
$user_name_session_key = "user_full_name"; // Session key for user's full name
$user_role_session_key = "user_role"; // Session key for user's role

// --- Database Utility ---
// Ensure the path is correct relative to this login/index.php file.
// It should be one level up from the 'login' directory.
require_once 'db_util.php'; // $mysqli object should be available now.

// --- Redirect if already logged in ---
if (isset($_SESSION[$user_session_key]) && !empty($_SESSION[$user_session_key])) {
    header("Location: ../dashboard/index.php"); // Or ../index.php to go through router
    exit;
}

// --- Login Logic ---
$login_error = ""; // Variable to store login error messages

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($username) || empty($password)) {
        $login_error = "Username and password are required.";
    } else {
        // Prepare statement to prevent SQL injection
        $stmt = $mysqli->prepare("SELECT id, username, password_hash, full_name, role FROM users WHERE username = ?");
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                // Verify the password against the stored hash
                if (password_verify($password, $user['password_hash'])) {
                    // Authentication successful
                    $_SESSION[$user_session_key] = $user['id']; // Store user's database ID
                    $_SESSION[$user_name_session_key] = $user['full_name'] ?: $user['username']; // Store full name or username
                    $_SESSION[$user_role_session_key] = $user['role']; // Store user's role

                    // Regenerate session ID for security (prevents session fixation)
                    session_regenerate_id(true);

                    // Redirect to the main router, which will then send to dashboard
                    header("Location: ../index.php");
                    exit;
                } else {
                    // Password verification failed
                    $login_error = "Invalid username or password.";
                }
            } else {
                // Username not found
                $login_error = "Invalid username or password.";
            }
            $stmt->close();
        } else {
            // Database query preparation failed
            $login_error = "Login error. Please try again later.";
            // Log the actual MySQL error for debugging (don't show to user)
            error_log("MySQLi prepare failed: " . $mysqli->error);
        }
    }
}
// Close the database connection if it was opened by db_util.php
// $mysqli->close(); // db_util.php might handle its own closing, or you might close it at the end of script execution.
// For a script that might exit early, it's often better to let PHP handle closing on script end,
// unless you have specific reasons to close it sooner.
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - 4031 Cafe POS</title>
    <style>
        body,
        html {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #1e1e1e;
            /* Dark background, similar to sidebar */
            color: #fff;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background-color: #2d2d2d;
            /* Slightly lighter dark shade for the box */
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .login-container .logo {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 15px;
            padding: 10px 20px;
            border: 2px solid #fff;
            border-radius: 8px;
            display: inline-block;
            /* So it doesn't take full width */
            color: #fff;
            /* White logo text */
        }

        .login-container h2 {
            color: #c8a07d;
            /* Accent color from POS UI */
            margin-bottom: 30px;
            font-size: 1.8em;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            font-size: 0.9em;
            color: #ccc;
            margin-bottom: 8px;
        }

        .form-group input[type="text"],
        .form-group input[type="password"] {
            width: calc(100% - 24px);
            /* Full width minus padding */
            padding: 12px;
            border: 1px solid #555;
            background-color: #333;
            color: #fff;
            border-radius: 6px;
            font-size: 1em;
            transition: border-color 0.3s;
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="password"]:focus {
            outline: none;
            border-color: #c8a07d;
            /* Accent color on focus */
        }

        .login-btn {
            width: 100%;
            padding: 12px;
            background-color: #c8a07d;
            /* Accent color */
            color: #1e1e1e;
            /* Dark text on accent button */
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1.1em;
            font-weight: bold;
            transition: background-color 0.3s;
            margin-top: 10px;
        }

        .login-btn:hover {
            background-color: #b38f6f;
            /* Darker shade of accent */
        }

        .error-message {
            color: #ff6b6b;
            /* Light red for errors */
            background-color: rgba(255, 107, 107, 0.1);
            border: 1px solid rgba(255, 107, 107, 0.3);
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 0.9em;
        }

        .footer-text {
            margin-top: 30px;
            font-size: 0.8em;
            color: #777;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="logo">4031 CAFE</div>
        <h2>POS Login</h2>

        <?php if (!empty($login_error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($login_error); ?></div>
        <?php endif; ?>

        <form action="index.php" method="POST"> <!-- Action is this same file -->
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required
                    value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="login-btn">Login</button>
        </form>
        <p class="footer-text">&copy; <?php echo date("Y"); ?> 4031 Cafe. All rights reserved.</p>
    </div>
</body>

</html>