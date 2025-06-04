<?php
// templates/partials/sidebar.php

$current_script_path = $_SERVER['SCRIPT_NAME'];

function get_link_path(string $target_path): string
{
    if (defined('BASE_URL')) {
        return rtrim(BASE_URL, '/') . '/' . ltrim($target_path, '/');
    }
    // Calculate relative path from current script to target
    // Example: from /public/dashboard/index.php to /public/orders/
    // SCRIPT_NAME: /myapp/public/dashboard/index.php
    // Target: orders/
    // Relative: ../orders/

    $base = dirname($_SERVER['SCRIPT_NAME']); // /myapp/public/dashboard
    // Normalize base by removing /public if it's the webroot context
    if (defined('PUBLIC_IS_WEBROOT') && PUBLIC_IS_WEBROOT) {
        // This constant would be set if your webserver points directly to /public
        // For now, assume it's not and paths are relative to script location in URL
    }

    // A simpler approach for fixed structure:
    if (strpos($base, '/dashboard') !== false) {
        return '../' . ltrim($target_path, '/');
    } elseif (strpos($base, '/orders') !== false) {
        if (strpos($target_path, 'dashboard') !== false)
            return '../' . ltrim($target_path, '/');
        return ltrim($target_path, '/'); // if already in orders, target 'orders/' is current dir
    } elseif (strpos($base, '/login') !== false) {
        return '../' . ltrim($target_path, '/'); // from login to dashboard or orders
    } elseif (strpos($base, '/auth') !== false) {
        return '../' . ltrim($target_path, '/');
    }
    // Fallback for root or unknown structure
    return ltrim($target_path, '/');
}

$dashboard_link = get_link_path('dashboard/');
$view_orders_link = get_link_path('orders/');
$products_link = get_link_path('products/');
$sales_link = get_link_path('sales/');
$logout_link = get_link_path('auth/logout.php');

function is_active_link(string $link_target_dir): bool
{
    // Check if the current script's directory matches the target link directory
    // e.g., if current is /public/orders/index.php, and link_target_dir is 'orders'
    $current_dir_basename = basename(dirname($_SERVER['SCRIPT_NAME']));
    return $current_dir_basename === $link_target_dir;
}

?>
<nav
    class="sidebar w-full lg:w-56 bg-slate-800 text-slate-100 p-3 lg:p-5 flex lg:flex-col items-center flex-shrink-0 shadow-md lg:shadow-none order-1 lg:order-none">
    <div class="logo text-xl lg:text-2xl font-bold lg:mb-8 p-2 lg:p-3 border-2 border-white rounded-lg mr-auto lg:mr-0">
        4031 CAFE
    </div>
    <ul class="flex lg:flex-col list-none p-0 w-auto lg:w-full">
        <li><a href="<?php echo $dashboard_link; ?>"
                class="<?php echo is_active_link('dashboard') ? 'active' : ''; ?> nav-link block text-slate-300 hover:bg-white hover:text-slate-800 py-2 px-3 lg:py-3 lg:px-6 text-sm lg:text-base transition duration-300 ease-in-out rounded-md lg:rounded-none lg:border-l-4 border-transparent lg:hover:border-amber-500">Dashboard</a>
        </li>
        <li><a href="<?php echo $view_orders_link; ?>"
                class="<?php echo is_active_link('orders') ? 'active' : ''; ?> nav-link block text-slate-300 hover:bg-white hover:text-slate-800 py-2 px-3 lg:py-3 lg:px-6 text-sm lg:text-base transition duration-300 ease-in-out rounded-md lg:rounded-none lg:border-l-4 border-transparent lg:hover:border-amber-500">View
                Orders</a></li>
        <li><a href="<?php echo $products_link; ?>"
                class="nav-link block text-slate-300 hover:bg-white hover:text-slate-800 py-2 px-3 lg:py-3 lg:px-6 text-sm lg:text-base transition duration-300 ease-in-out rounded-md lg:rounded-none lg:border-l-4 border-transparent lg:hover:border-amber-500">Products</a>
        </li>
        <li><a href="<?php echo $sales_link; ?>"
                class="nav-link block text-slate-300 hover:bg-white hover:text-slate-800 py-2 px-3 lg:py-3 lg:px-6 text-sm lg:text-base transition duration-300 ease-in-out rounded-md lg:rounded-none lg:border-l-4 border-transparent lg:hover:border-amber-500">Sales</a>
        </li>
    </ul>

    <div class="flex-grow"></div>

    <a href="<?php echo $logout_link; ?>"
        class="nav-link block text-slate-300 hover:bg-white hover:text-slate-800 py-2 px-3 lg:py-3 lg:px-6 text-sm lg:text-base transition duration-300 ease-in-out rounded-md lg:rounded-none lg:border-l-4 border-transparent lg:hover:border-amber-500">
        Sign Out</a>
    <div class="text-xs text-slate-400 mt-4 lg:mt-8">
        <p>Version 1.0.0</p>
        <p>Powered by PHP & MySQL</p>
    </div>
</nav>