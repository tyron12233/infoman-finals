<?php
/**
 * @var array $products_data All product data.
 * @var int|string $selected_category_id The currently selected category ID.
 */
?>
<section
    class="product-grid grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-4 flex-grow"
    id="productGrid">
    <?php if (empty($products_data)): ?>
        <p class="col-span-full text-center text-slate-500 py-10">No products found.</p>
    <?php else: ?>
        <?php foreach ($products_data as $product): ?>
            <?php include '_product_card.php'; // Pass $product and $selected_category_id by scope ?>
        <?php endforeach; ?>
    <?php endif; ?>
</section>