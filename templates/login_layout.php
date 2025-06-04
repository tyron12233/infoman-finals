<?php
/**
 * @var string $pageTitle The title for the login page.
 * @var string $login_error Any error message to pass to the form.
 * @var string $submitted_username Submitted username to pass to the form.
 */
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle ?? 'Login'); ?></title>
    <?php
    // Construct asset path. If BASE_URL is defined, use it. Otherwise, assume relative path.
    $css_path = "assets/css/login.css";
    if (defined('BASE_URL')) {
        $css_path = rtrim(BASE_URL, '/') . '/' . ltrim($css_path, '/');
    } else {
        // If public/login/index.php is including this, path needs to be relative from public/
        // Assuming login_layout.php is in templates/ and called from public/login/index.php
        // then assets/ is ../../assets/ from templates/ or assets/ from public/
        // Let's make it relative to the public root for consistency if BASE_URL isn't set
        // This means the controller (public/login/index.php) will need to ensure paths are correct
        // For simplicity here, assuming it's relative to where the final HTML is served (public root)
        $css_path = (str_starts_with($_SERVER['REQUEST_URI'], '/login/') ? '../assets/css/login.css' : 'assets/css/login.css');
        // A better way for assets if not using BASE_URL is to calculate path from script location
        // For now, this is a simpler approach.
        // Best: Define BASE_URL or use a helper function for asset paths.
        // Path from public/login/index.php to assets/css/login.css is ../../assets/css/login.css
        // But if the HTML is generated, the browser sees it from /login/
        // So, the path from /login/ to /assets/css/login.css is ../assets/css/login.css
    }
    ?>
    <link rel="stylesheet"
        href="<?php echo defined('BASE_URL') ? rtrim(BASE_URL, '/') . '/assets/css/login.css' : '../../assets/css/login.css'; ?>">
    <?php // The path from public/login/index.php to assets/css/login.css is ../../assets/css/login.css ?>
    <?php // However, when the browser requests it, the path is relative to the URL /login/ ?>
    <?php // So, if the URL is example.com/login/, then ../assets/css/login.css resolves to example.com/assets/css/login.css ?>
    <?php // Let's adjust for the controller being in public/login/index.php ?>
    <?php // The path from public/login/index.php to assets/css/login.css is `../../assets/css/login.css` if we think from file system. ?>
    <?php // But the browser will request it from `BASE_URL/login/`. So `../assets/css/login.css` is correct for the browser. ?>
    <?php // For the include from `public/login/index.php` to `templates/login_layout.php`, the path to assets needs to be relative to `public/login/index.php` for the browser. ?>
    <?php // So, if `public/login/index.php` is the entry, and it includes `../../templates/login_layout.php`, then inside layout, `../../assets/css/login.css` is relative to `public/login/` which is `public/assets/css/login.css`. This is correct. ?>
    <?php // The most robust way is to use an absolute path from the web root, or BASE_URL. ?>
    <?php // Path from public/login/index.php to assets/css/login.css is '../../assets/css/login.css' if we're thinking from the PHP file location. ?>
    <?php // Path for browser from /login/ to /assets/css/login.css is '../assets/css/login.css' ?>
    <?php // Path for browser from / to /assets/css/login.css is 'assets/css/login.css' ?>
    <?php // Let's assume public/login/index.php is the script. The CSS link should be relative to the *URL* of public/login/index.php ?>
    <link rel="stylesheet" href="../assets/css/login.css">
    <?php // This assumes login page is at /login/ and assets is at /assets/ ?>


</head>

<body>
    <?php
    require 'login_form.php';
    ?>
</body>

</html>