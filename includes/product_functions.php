<?php
// includes/product_functions.php

/**
 * Fetches all products with their variants and category name.
 *
 * @param mysqli $mysqli The database connection object.
 * @return array An array of products.
 */
function fetchAllProductsWithDetails(mysqli $mysqli): array
{
    $products_data = [];
    // Removed p.created_at and p.updated_at from SELECT as they are not in the provided schema for products table
    $sql = "
        SELECT
            p.id as product_id, p.name as product_name, p.image_url, p.description as product_description,
            p.category_id, p.display_order as product_display_order,
            c.name as category_name,
            pv.id as variant_id, pv.variant_name, pv.price as variant_price, pv.sku as variant_sku
        FROM
            products p
        LEFT JOIN
            categories c ON p.category_id = c.id
        LEFT JOIN
            product_variants pv ON p.id = pv.product_id
        ORDER BY
            c.display_order, c.name, p.display_order, p.name, pv.id;
    ";

    $result = $mysqli->query($sql);
    if (!$result) {
        error_log("Failed to fetch products with details: " . $mysqli->error);
        return [];
    }

    $grouped_products = [];
    while ($row = $result->fetch_assoc()) {
        $product_id = $row['product_id'];
        if (!isset($grouped_products[$product_id])) {
            $grouped_products[$product_id] = [
                'product_id' => $product_id,
                'product_name' => $row['product_name'],
                'image_url' => $row['image_url'],
                'product_description' => $row['product_description'],
                'category_id' => $row['category_id'],
                'category_name' => $row['category_name'],
                'product_display_order' => $row['product_display_order'],
                // product_created_at and product_updated_at removed
                'variants' => []
            ];
        }
        // Add variant only if variant_id is not null (handles products with no variants)
        if ($row['variant_id'] !== null) {
            $grouped_products[$product_id]['variants'][] = [
                'variant_id' => $row['variant_id'],
                'variant_name' => $row['variant_name'],
                'variant_price' => $row['variant_price'],
                'variant_sku' => $row['variant_sku']
            ];
        }
    }
    $result->free();
    return array_values($grouped_products); // Re-index
}

/**
 * Fetches a single product with its details for editing.
 * @param mysqli $mysqli
 * @param int $productId
 * @return array|null
 */
function fetchSingleProductForEdit(mysqli $mysqli, int $productId): ?array
{
    // This function selects columns that are present in the schema
    $sql = "
        SELECT
            p.id as product_id, p.name as product_name, p.image_url, p.description as product_description,
            p.category_id, p.display_order as product_display_order,
            c.name as category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.id = ?
    ";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        error_log("Prepare failed for fetchSingleProductForEdit (product): " . $mysqli->error);
        return null;
    }
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();

    if (!$product) {
        return null;
    }

    // Fetch variants
    $product['variants'] = [];
    $variantSql = "SELECT id as variant_id, variant_name, price as variant_price, sku as variant_sku FROM product_variants WHERE product_id = ? ORDER BY id";
    $stmtVar = $mysqli->prepare($variantSql);
    if (!$stmtVar) {
        error_log("Prepare failed for fetchSingleProductForEdit (variants): " . $mysqli->error);
        return $product; // Return product without variants if query fails
    }
    $stmtVar->bind_param("i", $productId);
    $stmtVar->execute();
    $variantResult = $stmtVar->get_result();
    while ($row = $variantResult->fetch_assoc()) {
        $product['variants'][] = $row;
    }
    $stmtVar->close();

    return $product;
}


/**
 * Fetches all categories for use in forms.
 * @param mysqli $mysqli
 * @return array
 */
function fetchAllCategoriesForForm(mysqli $mysqli): array
{
    $categories = [];
    $result = $mysqli->query("SELECT id, name FROM categories ORDER BY display_order, name");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
        $result->free();
    } else {
        error_log("Failed to fetch categories for form: " . $mysqli->error);
    }
    return $categories;
}

/**
 * Adds a new product with its variants.
 * @param mysqli $mysqli
 * @param array $productData
 * @param array $variantsData
 * @return int|false The new product ID on success, false on failure.
 */
function addProductWithVariants(mysqli $mysqli, array $productData, array $variantsData)
{
    $mysqli->begin_transaction();
    try {
        // Columns match the provided schema for `products`
        $stmtProd = $mysqli->prepare("INSERT INTO products (name, description, image_url, category_id, display_order) VALUES (?, ?, ?, ?, ?)");
        if (!$stmtProd)
            throw new Exception("Product prepare failed: " . $mysqli->error);

        $displayOrder = isset($productData['display_order']) && is_numeric($productData['display_order']) ? (int) $productData['display_order'] : 0;
        $categoryId = isset($productData['category_id']) && is_numeric($productData['category_id']) ? (int) $productData['category_id'] : null;
        $imageUrl = $productData['image_url'] ?? 'https://placehold.co/150x150/E8D4C5/6B4F4F?text=Item'; // Default from schema

        $stmtProd->bind_param(
            "sssis",
            $productData['name'],
            $productData['description'],
            $imageUrl,
            $categoryId,
            $displayOrder
        );
        if (!$stmtProd->execute())
            throw new Exception("Product execute failed: " . $stmtProd->error);
        $productId = $mysqli->insert_id;
        $stmtProd->close();

        if (!empty($variantsData)) {
            // Columns match the provided schema for `product_variants`
            $stmtVar = $mysqli->prepare("INSERT INTO product_variants (product_id, variant_name, price, sku) VALUES (?, ?, ?, ?)");
            if (!$stmtVar)
                throw new Exception("Variant prepare failed: " . $mysqli->error);

            foreach ($variantsData as $variant) {
                $price = isset($variant['price']) && is_numeric($variant['price']) ? (float) $variant['price'] : 0.00;
                $sku = $variant['sku'] ?? null; // SKU is nullable in schema
                $stmtVar->bind_param(
                    "isds",
                    $productId,
                    $variant['name'],
                    $price,
                    $sku
                );
                if (!$stmtVar->execute())
                    throw new Exception("Variant execute failed: " . $stmtVar->error . " for variant: " . $variant['name']);
            }
            $stmtVar->close();
        }
        $mysqli->commit();
        return $productId;
    } catch (Exception $e) {
        $mysqli->rollback();
        error_log("addProductWithVariants failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Updates an existing product and its variants.
 * @param mysqli $mysqli
 * @param int $productId
 * @param array $productData
 * @param array $variantsData
 * @return bool True on success, false on failure.
 */
function updateProductWithVariants(mysqli $mysqli, int $productId, array $productData, array $variantsData): bool
{
    $mysqli->begin_transaction();
    try {
        // Update product details - columns match the schema
        $stmtProd = $mysqli->prepare("UPDATE products SET name = ?, description = ?, image_url = ?, category_id = ?, display_order = ? WHERE id = ?");
        if (!$stmtProd)
            throw new Exception("Update product prepare failed: " . $mysqli->error);

        $displayOrder = isset($productData['display_order']) && is_numeric($productData['display_order']) ? (int) $productData['display_order'] : 0;
        $categoryId = isset($productData['category_id']) && is_numeric($productData['category_id']) ? (int) $productData['category_id'] : null;
        $imageUrl = $productData['image_url'] ?? 'https://placehold.co/150x150/E8D4C5/6B4F4F?text=Item'; // Default from schema

        $stmtProd->bind_param(
            "sssisi",
            $productData['name'],
            $productData['description'],
            $imageUrl,
            $categoryId,
            $displayOrder,
            $productId
        );
        if (!$stmtProd->execute())
            throw new Exception("Update product execute failed: " . $stmtProd->error);
        $stmtProd->close();

        // Manage variants
        $existingVariantIds = [];
        $res = $mysqli->query("SELECT id FROM product_variants WHERE product_id = $productId");
        if ($res) {
            while ($row = $res->fetch_assoc())
                $existingVariantIds[] = $row['id'];
            $res->free();
        }

        $submittedVariantIds = [];

        $stmtUpdateVar = $mysqli->prepare("UPDATE product_variants SET variant_name = ?, price = ?, sku = ? WHERE id = ? AND product_id = ?");
        if (!$stmtUpdateVar)
            throw new Exception("Variant update prepare failed: " . $mysqli->error);

        $stmtInsertVar = $mysqli->prepare("INSERT INTO product_variants (product_id, variant_name, price, sku) VALUES (?, ?, ?, ?)");
        if (!$stmtInsertVar)
            throw new Exception("Variant insert prepare failed: " . $mysqli->error);

        foreach ($variantsData as $variant) {
            $variantId = isset($variant['id']) && !empty($variant['id']) && is_numeric($variant['id']) ? (int) $variant['id'] : null;
            $price = isset($variant['price']) && is_numeric($variant['price']) ? (float) $variant['price'] : 0.00;
            $sku = $variant['sku'] ?? null;

            if ($variantId && in_array($variantId, $existingVariantIds)) { // Existing variant: Update
                $stmtUpdateVar->bind_param("sdsii", $variant['name'], $price, $sku, $variantId, $productId);
                if (!$stmtUpdateVar->execute())
                    throw new Exception("Variant update execute failed: " . $stmtUpdateVar->error);
                $submittedVariantIds[] = $variantId;
            } else { // New variant: Insert
                $stmtInsertVar->bind_param("isds", $productId, $variant['name'], $price, $sku);
                if (!$stmtInsertVar->execute())
                    throw new Exception("Variant insert execute failed: " . $stmtInsertVar->error . " for variant: " . $variant['name']);
                // If you need the ID of the newly inserted variant: $newVariantId = $mysqli->insert_id;
            }
        }
        $stmtUpdateVar->close();
        $stmtInsertVar->close();

        $variantsToDelete = array_diff($existingVariantIds, $submittedVariantIds);
        if (!empty($variantsToDelete)) {
            $deleteIdsStr = implode(',', array_map('intval', $variantsToDelete));
            if (!$mysqli->query("DELETE FROM product_variants WHERE product_id = $productId AND id IN ($deleteIdsStr)")) {
                throw new Exception("Variant delete failed: " . $mysqli->error);
            }
        }

        $mysqli->commit();
        return true;
    } catch (Exception $e) {
        $mysqli->rollback();
        error_log("updateProductWithVariants failed for product ID $productId: " . $e->getMessage());
        return false;
    }
}


/**
 * Deletes a product and its associated variants (variants deleted by ON DELETE CASCADE).
 * @param mysqli $mysqli
 * @param int $productId
 * @return bool True on success, false on failure.
 */
function deleteProduct(mysqli $mysqli, int $productId): bool
{
    $mysqli->begin_transaction();
    try {
        // Variants are deleted by ON DELETE CASCADE as per schema `product_variants`.
        $stmtProd = $mysqli->prepare("DELETE FROM products WHERE id = ?");
        if (!$stmtProd)
            throw new Exception("Product delete prepare failed: " . $mysqli->error);
        $stmtProd->bind_param("i", $productId);
        if (!$stmtProd->execute())
            throw new Exception("Product delete execute failed: " . $stmtProd->error);
        $affectedRows = $stmtProd->affected_rows;
        $stmtProd->close();

        // No need to throw an exception if product not found for deletion,
        // as the goal (product doesn't exist) is achieved.
        // if ($affectedRows === 0) {
        //     throw new Exception("Product with ID $productId not found for deletion.");
        // }

        $mysqli->commit();
        return true;
    } catch (Exception $e) {
        $mysqli->rollback();
        error_log("deleteProduct failed for product ID $productId: " . $e->getMessage());
        return false;
    }
}

?>