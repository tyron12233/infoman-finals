<?php
/**
 * @var string $pageTitle The title of the page.
 * @var string $content_template The path to the main content template for this page.
 * @var string $page_specific_js Optional path to a JS file specific to this page.
 *
 * Variables passed from the controller, like $categories, $products_data for the dashboard,
 * will NOT be automatically available in $content_template unless explicitly passed or re-fetched by JS.
 * For the orders page, data is fetched by JS, so PHP doesn't need to pass it to the template.
 */

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle ?? 'POS System'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>

    <script src="<?php echo BASE_URL; ?>assets/js/custom-dialog.js" defer></script>
</head>

<body class="bg-slate-100 text-slate-800 font-sans h-screen overflow-hidden">
    <div class="app-container flex flex-col lg:flex-row h-screen">
        <?php require ROOT_PATH . '/templates/partials/sidebar.php'; // Sidebar is common ?>

        <?php
        // Include the specific content for the page
        if (isset($content_template) && file_exists($content_template)) {
            require $content_template;
        } else {
            echo "<main class='main-content flex-grow p-6 bg-white'><p class='text-red-500'>Error: Content template not found." . $content_template . "</p></main>";
        }
        ?>

        <?php
        // Conditionally include order summary if it's not the orders page itself
        // Or better, the specific content template can decide if it needs an order summary area
        // For now, let's assume the dashboard needs it, orders page might not.
        // The $content_template can be checked.
        if (strpos($content_template, 'index_content.php') !== false) { // A bit hacky, better to pass a flag
            require ROOT_PATH . '/templates/partials/order_summary.php';
        }
        ?>
    </div>

    <?php require ROOT_PATH . '/templates/partials/dialog.php'; // Common dialog modal ?>
</body>

</html>