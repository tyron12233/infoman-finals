<?php
// public/api/manage_product.php

header('Content-Type: application/json');
header('Accept: application/json');

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__, 2)); // Path from public/api/ to project root
}

require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/config/db_connect.php'; // $mysqli
require_once ROOT_PATH . '/includes/auth_functions.php';
require_once ROOT_PATH . '/includes/product_functions.php';

startSecureSession();

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Fetch all products or a single product for editing
    $productId = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;
    if ($productId) {
        $product = fetchSingleProductForEdit($mysqli, $productId);
        if ($product) {
            echo json_encode(['success' => true, 'product' => $product]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Product not found.']);
        }
    } else {
        $products = fetchAllProductsWithDetails($mysqli);
        $categories = fetchAllCategoriesForForm($mysqli); // Also send categories for add/edit forms
        echo json_encode(['success' => true, 'products' => $products, 'categories' => $categories]);
    }
} elseif ($method === 'POST') {
    // Handles Add, Update, Delete based on an 'action' parameter
    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, TRUE);

    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON payload.']);
        exit;
    }

    $action = $input['action'] ?? null;
    $productData = $input['product'] ?? [];
    $variantsData = $input['variants'] ?? [];
    $productId = isset($input['product_id']) ? filter_var($input['product_id'], FILTER_VALIDATE_INT) : null;


    switch ($action) {
        case 'add':
            if (empty($productData['name']) || empty($variantsData)) { // Require at least one variant for a new product
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Product name and at least one variant are required to add a product.']);
                exit;
            }
            $newProductId = addProductWithVariants($mysqli, $productData, $variantsData);
            if ($newProductId) {
                echo json_encode(['success' => true, 'message' => 'Product added successfully.', 'product_id' => $newProductId]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to add product.']);
            }
            break;

        case 'update':
            if (!$productId || empty($productData['name'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Product ID and name are required for update.']);
                exit;
            }
            if (updateProductWithVariants($mysqli, $productId, $productData, $variantsData)) {
                echo json_encode(['success' => true, 'message' => 'Product updated successfully.']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update product.']);
            }
            break;

        case 'delete':
            if (!$productId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Product ID is required for deletion.']);
                exit;
            }
            if (deleteProduct($mysqli, $productId)) {
                echo json_encode(['success' => true, 'message' => 'Product deleted successfully.']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to delete product. It might be in use or not found.']);
            }
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action specified.']);
            break;
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
}

if (isset($mysqli) && $mysqli instanceof mysqli) {
    $mysqli->close();
}
?>