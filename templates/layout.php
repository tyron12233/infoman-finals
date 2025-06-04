<?php
/**
 * @var string $pageTitle The title of the page.
 * @var array $categories All categories data.
 * @var array $products_data All product data.
 * @var int|string $selected_category_id The currently selected category ID.
 */
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle ?? 'POS System'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/assets/css/style.css"> <?php // Path relative to public/index.php ?>
</head>

<body class="bg-slate-100 text-slate-800 font-sans h-screen overflow-hidden">
    <div class="app-container flex flex-col lg:flex-row h-screen">
        <?php include __DIR__ . '/partials/sidebar.php'; ?>
        <?php include __DIR__ . '/index_content.php'; // Or a variable path for different page contents ?>
        <?php include __DIR__ . '/partials/order_summary.php'; ?>
    </div>

    <?php include __DIR__ . '/partials/dialog.php'; ?>

    <script src="/assets/js/dashboard.js"></script>
</body>

</html>