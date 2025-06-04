<?php
/**
 * @var array $categories All categories data.
 * @var array $products_data All product data.
 * @var int|string $selected_category_id The currently selected category ID.
 */
?>
<!-- include css -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
<!-- dashboard.js -->
<script src="<?php echo BASE_URL; ?>assets/js/dashboard.js" defer></script>

<main class="main-content flex-grow p-4 md:p-6 bg-white overflow-y-auto flex flex-col order-2 lg:order-none h-auto">
    <header class="flex flex-col lg:flex-row justify-between lg:items-center mb-5 gap-4">
        <div class="category-filters flex flex-wrap gap-2 items-center justify-center lg:justify-start">
            <h3 class="hidden sm:block mr-3 text-lg font-semibold text-slate-700 self-center">Choose Category</h3>
            <button
                class="category-btn py-2 px-4 border border-slate-300 bg-slate-100 text-slate-600 cursor-pointer rounded-full text-sm transition duration-300 hover:bg-slate-200 <?php echo ($selected_category_id === 'all') ? 'active' : ''; ?>"
                data-category-id="all">All</button>
            <?php foreach ($categories as $category): ?>
                <button
                    class="category-btn py-2 px-4 border border-slate-300 bg-slate-100 text-slate-600 cursor-pointer rounded-full text-sm transition duration-300 hover:bg-slate-200 <?php echo ($selected_category_id == $category['id']) ? 'active' : ''; ?>"
                    data-category-id="<?php echo htmlspecialchars($category['id']); ?>">
                    <?php echo htmlspecialchars($category['name']); ?>
                </button>
            <?php endforeach; ?>
        </div>
        <div class="search-product w-full lg:w-auto">
            <input type="text" id="productSearchInput" placeholder="Search Product..."
                class="py-2 px-4 border border-slate-300 rounded-full w-full lg:w-64 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none">
        </div>
    </header>

    <?php include __DIR__ . '/partials/product_grid.php'; ?>

</main>