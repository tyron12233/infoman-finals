<?php
// templates/partials/sidebar.php

// --- Configuration & Helpers ---

// It's highly recommended to define BASE_URL in a central configuration file.
// Example: define('BASE_URL', '/your-app-root-path/public/');
// Ensure it ends with a slash. If your app is at the domain root, BASE_URL would be '/'.
if (!defined('BASE_URL')) {
    // Fallback if BASE_URL is not set - this is less reliable.
    // Determine a sensible default, or trigger an error.
    // This basic fallback assumes the sidebar is included from files within a 'public' or root context.
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    // Basic attempt to find a root, might need adjustment for your setup
    $script_dir = dirname($_SERVER['SCRIPT_NAME']);
    $base_path = ($script_dir === '/' || $script_dir === '\\') ? '/' : rtrim($script_dir, '/') . '/';
    // If you have a known structure like /public/, adjust this.
    // For this example, a simpler relative pathing might be assumed by the old get_link_path if BASE_URL isn't set.
    // define('BASE_URL', $protocol . $host . $base_path); // Not defining here to avoid side-effects, but illustrating.
    trigger_error(
        "BASE_URL is not defined. Sidebar links may be incorrect or use basic relative pathing. Please define BASE_URL in your application's configuration.",
        E_USER_WARNING
    );
}

function get_link_path(string $target_path): string
{
    if (defined('BASE_URL')) {
        return rtrim(BASE_URL, '/') . '/' . ltrim($target_path, '/');
    }
    // Fallback: Very basic relative pathing if BASE_URL is not set.
    // This is likely what your original function attempted in a more complex way.
    // For a simple structure, this might work from pages one level deep (e.g., in /dashboard/, /orders/).
    // If $target_path is 'dashboard/', it assumes ../dashboard/ from e.g. /orders/index.php
    // This part is less robust and ideally BASE_URL should be used.
    $current_depth = count(array_filter(explode('/', dirname($_SERVER['SCRIPT_NAME']))));
    $target_clean = ltrim($target_path, '/');

    if ($current_depth > 1 && strpos($target_clean, '/') === false && $target_clean !== basename(dirname($_SERVER['SCRIPT_NAME']))) { // Simple target like 'products/' from '/dashboard/'
        return '../' . $target_clean;
    }
    return $target_clean; // If in same dir or target is more complex
}

function is_active_link(string $link_target_dir): bool
{
    $current_script_path_normalized = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $current_dir_basename = basename($current_script_path_normalized);

    // Handle cases like 'auth/logout.php' where target_dir might be 'auth'
    // and current script is /public/auth/logout.php
    if ($link_target_dir === 'auth' && $current_dir_basename === 'auth') {
        return true;
    }

    return $current_dir_basename === rtrim($link_target_dir, '/');
}

function get_nav_link_classes(string $target_dir_for_active_check): string
{
    $base_classes = "block text-slate-300 py-2.5 px-4 lg:py-3 lg:px-5 text-sm lg:text-base transition-all duration-200 ease-in-out rounded-md lg:rounded-none lg:border-l-4";

    if (is_active_link($target_dir_for_active_check)) {
        // Active classes: distinct background, text color, and always visible border
        return $base_classes . " bg-slate-900 text-white border-amber-500 font-medium";
    } else {
        // Non-active links: transparent border, specific hover states
        $hover_classes = "hover:bg-slate-700 hover:text-white hover:border-slate-700 lg:hover:border-amber-500";
        return $base_classes . " border-transparent " . $hover_classes;
    }
}

// Define links (using BASE_URL is preferred)
$dashboard_link = get_link_path('dashboard/');
$view_orders_link = get_link_path('orders/');
$products_link = get_link_path('products/');
$sales_link = get_link_path('sales/');
$logout_link = get_link_path('auth/logout.php'); // Assuming logout.php is in an 'auth' directory

?>

<nav
    class="sidebar w-full lg:w-60 bg-slate-800 text-slate-100 flex flex-col flex-shrink-0 shadow-xl order-1 lg:order-none lg:min-h-screen">
    <div class="flex items-center justify-between p-3 lg:flex-col lg:items-center lg:py-5 lg:px-4">
        <a href="<?php echo $dashboard_link; ?>"
            class="logo text-xl lg:text-2xl font-bold lg:mb-8 p-2.5 lg:p-3 border-2 border-slate-300 hover:border-white transition-colors rounded-lg">
            4031 CAFE
        </a>
        <button id="hamburgerButton" aria-label="Toggle Menu" aria-expanded="false" aria-controls="collapsibleMenu"
            class="lg:hidden text-slate-300 hover:text-white focus:outline-none p-2">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg">
                <path id="iconOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 6h16M4 12h16M4 18h16"></path>
                <path id="iconClose" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M6 18L18 6M6 6l12 12" class="hidden"></path>
            </svg>
        </button>
    </div>

    <div id="collapsibleMenu" class="w-full px-3 pb-3 lg:px-0 lg:pb-0 
                overflow-hidden transition-all duration-300 ease-in-out motion-reduce:transition-none 
                lg:flex lg:flex-col lg:flex-grow lg:items-stretch 
                lg:max-h-none lg:opacity-100 lg:transform-none lg:visible lg:overflow-visible">

        <ul class="flex flex-col list-none p-0 w-full space-y-1.5 lg:space-y-2 lg:overflow-y-auto lg:flex-grow">
            <li><a href="<?php echo $dashboard_link; ?>"
                    class="<?php echo get_nav_link_classes('dashboard'); ?>">Dashboard</a></li>
            <li><a href="<?php echo $view_orders_link; ?>" class="<?php echo get_nav_link_classes('orders'); ?>">View
                    Orders</a></li>
            <li><a href="<?php echo $products_link; ?>"
                    class="<?php echo get_nav_link_classes('products'); ?>">Products</a></li>
            <li><a href="<?php echo $sales_link; ?>" class="<?php echo get_nav_link_classes('sales'); ?>">Sales</a></li>
        </ul>

        <div class="mt-auto"></div>

        <div class="mt-6 pt-6 border-t border-slate-700 lg:pt-4">
            <a href="<?php echo $logout_link; ?>" class="<?php echo get_nav_link_classes('auth'); ?>">Sign Out</a>
            <div class="text-xs text-slate-500 mt-4 lg:mt-5 text-center lg:text-left px-4 lg:px-5">
                <p>Version 1.0.0</p>
                <p>Powered by PHP & MySQL</p>
            </div>
        </div>
    </div>
</nav>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const hamburgerButton = document.getElementById('hamburgerButton');
        const collapsibleMenu = document.getElementById('collapsibleMenu');
        const iconOpen = document.getElementById('iconOpen');
        const iconClose = document.getElementById('iconClose');

        // Define Tailwind classes for open/closed states for animation
        // Ensure your Tailwind JIT process can see these full class names.
        const menuOpenMobileLayoutClasses = ['flex', 'flex-col', 'flex-grow', 'overflow-y-auto']; // Layout for open mobile menu, allows scrolling within menu
        const menuOpenAnimationClasses = ['max-h-[85vh]', 'opacity-100', 'visible', 'translate-y-0']; // Animation to open state

        const menuClosedAnimationClasses = ['max-h-0', 'opacity-0', 'invisible', '-translate-y-3']; // Animation to closed state, -translate-y-3 for slight "up and away"

        function applyClasses(element, classesToRemove, classesToAdd) {
            if (element) {
                element.classList.remove(...classesToRemove);
                element.classList.add(...classesToAdd);
            }
        }

        function setInitialMenuState() {
            if (!collapsibleMenu) return;

            if (window.innerWidth < 1024) { // Mobile view
                // Start closed: apply layout classes for open state (they are inert when max-h-0)
                // then apply closed animation classes.
                // Remove open animation classes first to ensure clean state if resizing.
                collapsibleMenu.classList.remove(...menuOpenAnimationClasses);
                collapsibleMenu.classList.add(...menuClosedAnimationClasses);
                // Layout classes for mobile (flex, flex-col, etc.) are added when opening.
                // For closed state, they should be removed if they were added.
                collapsibleMenu.classList.remove(...menuOpenMobileLayoutClasses);


                if (hamburgerButton) hamburgerButton.setAttribute('aria-expanded', 'false');
                if (iconOpen) iconOpen.classList.remove('hidden');
                if (iconClose) iconClose.classList.add('hidden');

            } else { // Desktop view
                // Remove all mobile animation AND layout classes, rely on base/lg: classes in HTML
                applyClasses(collapsibleMenu,
                    [...menuOpenAnimationClasses, ...menuClosedAnimationClasses, ...menuOpenMobileLayoutClasses],
                    []
                );
                if (hamburgerButton) hamburgerButton.setAttribute('aria-expanded', 'false'); // Hamburger itself is hidden by CSS
            }
        }

        // Set initial state
        setInitialMenuState();

        if (hamburgerButton && collapsibleMenu && iconOpen && iconClose) {
            hamburgerButton.addEventListener('click', function () {
                const isClosed = collapsibleMenu.classList.contains('max-h-0') || !collapsibleMenu.classList.contains('opacity-100');

                if (isClosed) { // If closed or closing, then open it
                    applyClasses(collapsibleMenu, menuClosedAnimationClasses, [...menuOpenMobileLayoutClasses, ...menuOpenAnimationClasses]);
                    hamburgerButton.setAttribute('aria-expanded', 'true');
                    iconOpen.classList.add('hidden');
                    iconClose.classList.remove('hidden');
                } else { // If open or opening, then close it
                    applyClasses(collapsibleMenu, [...menuOpenMobileLayoutClasses, ...menuOpenAnimationClasses], menuClosedAnimationClasses);
                    hamburgerButton.setAttribute('aria-expanded', 'false');
                    iconOpen.classList.remove('hidden');
                    iconClose.classList.add('hidden');
                }
            });
        }

        window.addEventListener('resize', setInitialMenuState); // Re-evaluate on resize
    });
</script>