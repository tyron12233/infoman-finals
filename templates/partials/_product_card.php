<?php
/**
 * @var array $product The product data array.
 * @var int|string $selected_category_id The currently selected category ID.
 */
$display_class = ($selected_category_id !== 'all' && $product['category_id'] != $selected_category_id) ? 'hidden' : '';
?>
<div class="product-card bg-slate-50 border border-slate-200 rounded-xl p-3 md:p-4 text-center shadow-md hover:shadow-lg transition-shadow duration-300 flex flex-col justify-between <?php echo $display_class; ?>"
    data-product-id="<?php echo $product['id']; ?>" data-category-id="<?php echo $product['category_id']; ?>"
    data-product-name="<?php echo htmlspecialchars($product['name']); ?>">
    <div>
        <img src="<?php echo htmlspecialchars($product['image_url'] ?: 'https://via.placeholder.com/150'); ?>"
            alt="<?php echo htmlspecialchars($product['name']); ?>"
            class="w-full h-28 md:h-32 object-cover rounded-md mb-3">
        <h4 class="text-sm md:text-base font-semibold text-slate-700 my-2 min-h-[2.5em] md:min-h-[2.8em] line-clamp-2">
            <?php echo htmlspecialchars($product['name']); ?>
        </h4>
        <p
            class="product-price-desc text-xs md:text-sm text-slate-500 mb-3 min-h-[1.3em] md:min-h-[1.5em] line-clamp-1">
            <?php echo htmlspecialchars($product['description']); ?>
        </p>
        <div class="variant-options mb-3 flex justify-center flex-wrap gap-2">
            <?php foreach ($product['variants'] as $index => $variant): ?>
                <button
                    class="variant-btn py-1 px-3 border border-slate-300 bg-white text-slate-600 cursor-pointer rounded-full text-xs transition duration-200 hover:bg-slate-100 <?php echo $index === 0 ? 'selected' : ''; ?>"
                    data-variant-id="<?php echo $variant['id']; ?>"
                    data-variant-name="<?php echo htmlspecialchars($variant['name']); ?>"
                    data-variant-price="<?php echo $variant['price']; ?>"
                    data-variant-sku="<?php echo htmlspecialchars($variant['sku']); ?>">
                    <?php echo htmlspecialchars($variant['name']); ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>
    <button
        class="add-to-cart-btn w-full mt-2 py-2 px-3 bg-white text-amber-600 border border-amber-600 rounded-lg font-bold transition duration-300 hover:bg-amber-600 hover:text-white text-sm">Add
        to Cart</button>
</div>