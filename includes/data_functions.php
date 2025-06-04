<?php
// Function to fetch categories
function fetchCategories(mysqli $mysqli): array
{
    $categories = [];
    $result = $mysqli->query("SELECT id, name FROM categories ORDER BY display_order, name");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
        $result->free();
    } else {
        error_log("Failed to fetch categories: " . $mysqli->error);
    }
    return $categories;
}

// Function to fetch products with variants
function fetchProductsWithVariants(mysqli $mysqli): array
{
    $raw_products = [];
    $sql = "
        SELECT
            p.id as product_id, p.name as product_name, p.image_url,
            p.description as product_description, p.category_id,
            c.name as category_name,
            pv.id as variant_id, pv.variant_name,
            pv.price as variant_price, pv.sku as variant_sku
        FROM products p
        JOIN categories c ON p.category_id = c.id
        JOIN product_variants pv ON p.id = pv.product_id
        ORDER BY c.display_order, c.name, p.display_order, p.name, pv.id;
    ";
    $result = $mysqli->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $raw_products[] = $row;
        }
        $result->free();
    } else {
        error_log("Failed to fetch products: " . $mysqli->error);
    }
    return $raw_products;
}

// Function to group products for rendering
function groupProducts(array $raw_products): array
{
    $grouped_products = [];
    foreach ($raw_products as $row) {
        $product_id = $row['product_id'];
        if (!isset($grouped_products[$product_id])) {
            $grouped_products[$product_id] = [
                'id' => $product_id,
                'name' => $row['product_name'],
                'image_url' => $row['image_url'],
                'description' => $row['product_description'],
                'category_id' => $row['category_id'],
                'category_name' => $row['category_name'],
                'variants' => []
            ];
        }
        $grouped_products[$product_id]['variants'][] = [
            'id' => $row['variant_id'],
            'name' => $row['variant_name'],
            'price' => $row['variant_price'],
            'sku' => $row['variant_sku']
        ];
    }
    return array_values($grouped_products); // Re-index
}

// Function to determine the selected category ID
function getSelectedCategoryId(array $categories): string|int
{
    $selected_category_id = 'all';
    if (isset($_GET['category']) && $_GET['category'] !== 'all') {
        $selected_category_id = (int) $_GET['category'];
    }
    // Optional: Default to first category if 'all' is not the primary choice and no specific category is chosen
    // elseif (!empty($categories) && !isset($_GET['category'])) {
    //    $selected_category_id = $categories[0]['id'];
    // }
    return $selected_category_id;
}
?>