-- SQL Schema for POS System
-- Database: main (as per your db_util.php)

-- 1. Categories Table
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `display_order` INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Products Table
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT,
  `name` VARCHAR(150) NOT NULL COMMENT 'e.g., Spanish Latte',
  `image_url` VARCHAR(255) DEFAULT 'https://placehold.co/150x150/E8D4C5/6B4F4F?text=Item',
  `description` TEXT COMMENT 'e.g., P99 / P109 or general info',
  `display_order` INT DEFAULT 0,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Product Variants Table
CREATE TABLE IF NOT EXISTS `product_variants` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT,
  `variant_name` VARCHAR(50) NOT NULL COMMENT 'e.g., Hot, Iced, Regular',
  `price` DECIMAL(10, 2) NOT NULL,
  `sku` VARCHAR(100) UNIQUE COMMENT 'Optional, but good practice',
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  UNIQUE KEY `product_variant_unique` (`product_id`, `variant_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Orders Table (for future expansion, basic structure)
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_number` VARCHAR(50) UNIQUE,
  `customer_name` VARCHAR(255) NULL,
  `order_type` ENUM('Dine In', 'Take Out') NOT NULL DEFAULT 'Dine In',
  `total_amount` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `status` VARCHAR(50) DEFAULT 'Pending' COMMENT 'Pending, Processing, Completed, Cancelled',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Order Items Table (for future expansion)
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT,
  `product_variant_id` INT,
  `product_name_at_purchase` VARCHAR(200), -- Combined product + variant name
  `quantity` INT NOT NULL DEFAULT 1,
  `price_at_purchase` DECIMAL(10, 2) NOT NULL,
  `notes` TEXT NULL,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants`(`id`) ON DELETE SET NULL ON UPDATE CASCADE -- SET NULL if variant is deleted, or handle differently
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Users Table (for login)
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL COMMENT 'Store hashed passwords only (e.g., using password_hash())',
  `full_name` VARCHAR(100) NULL,
  `role` VARCHAR(50) DEFAULT 'staff' COMMENT 'e.g., admin, staff',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Note: The orders and order_items tables are defined for future use.
-- The initial PHP script will focus on the product display and client-side cart.
-- Actual order placement into these tables would be a subsequent step.

