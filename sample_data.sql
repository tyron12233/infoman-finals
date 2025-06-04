-- Sample Data for POS System
-- Ensure you have run schema.sql first.

-- 1. Categories
INSERT INTO `categories` (`name`, `display_order`) VALUES
('Coffee', 1),
('Non Coffee', 2),
('Rice Meal', 3),
('Dessert', 4);

-- 2. Products & Variants
-- Coffee
SET @coffee_cat_id = (SELECT id FROM categories WHERE name = 'Coffee');

INSERT INTO `products` (`category_id`, `name`, `image_url`, `description`, `display_order`) VALUES
(@coffee_cat_id, 'Spanish Latte', 'https://placehold.co/150x150/E8D4C5/6B4F4F?text=Spanish+Latte', 'P99 / P109', 1),
(@coffee_cat_id, 'Caramel Macchiato', 'https://placehold.co/150x150/E8D4C5/6B4F4F?text=Caramel+Mac', 'P99 / P109', 2),
(@coffee_cat_id, 'Vanilla Latte', 'https://placehold.co/150x150/E8D4C5/6B4F4F?text=Vanilla+Latte', 'P99 / P109', 3),
(@coffee_cat_id, 'Americano', 'https://placehold.co/150x150/E8D4C5/6B4F4F?text=Americano', 'P80 / P90', 4);

-- Variants for Spanish Latte
SET @spanish_latte_id = (SELECT id FROM products WHERE name = 'Spanish Latte');
INSERT INTO `product_variants` (`product_id`, `variant_name`, `price`, `sku`) VALUES
(@spanish_latte_id, 'Hot', 99.00, 'SL-HOT'),
(@spanish_latte_id, 'Iced', 109.00, 'SL-ICED');

-- Variants for Caramel Macchiato
SET @caramel_mac_id = (SELECT id FROM products WHERE name = 'Caramel Macchiato');
INSERT INTO `product_variants` (`product_id`, `variant_name`, `price`, `sku`) VALUES
(@caramel_mac_id, 'Hot', 99.00, 'CM-HOT'),
(@caramel_mac_id, 'Iced', 109.00, 'CM-ICED');

-- Variants for Vanilla Latte
SET @vanilla_latte_id = (SELECT id FROM products WHERE name = 'Vanilla Latte');
INSERT INTO `product_variants` (`product_id`, `variant_name`, `price`, `sku`) VALUES
(@vanilla_latte_id, 'Hot', 99.00, 'VL-HOT'),
(@vanilla_latte_id, 'Iced', 109.00, 'VL-ICED');

-- Variants for Americano
SET @americano_id = (SELECT id FROM products WHERE name = 'Americano');
INSERT INTO `product_variants` (`product_id`, `variant_name`, `price`, `sku`) VALUES
(@americano_id, 'Hot', 80.00, 'AM-HOT'),
(@americano_id, 'Iced', 90.00, 'AM-ICED');


-- Non Coffee
SET @non_coffee_cat_id = (SELECT id FROM categories WHERE name = 'Non Coffee');
INSERT INTO `products` (`category_id`, `name`, `image_url`, `description`, `display_order`) VALUES
(@non_coffee_cat_id, 'Chocolate Drink', 'https://placehold.co/150x150/D2B48C/6B4F4F?text=Choco', 'P85 / P95', 1),
(@non_coffee_cat_id, 'Matcha Latte', 'https://placehold.co/150x150/90EE90/6B4F4F?text=Matcha', 'P100 / P110', 2);

-- Variants for Chocolate Drink
SET @choco_id = (SELECT id FROM products WHERE name = 'Chocolate Drink');
INSERT INTO `product_variants` (`product_id`, `variant_name`, `price`, `sku`) VALUES
(@choco_id, 'Hot', 85.00, 'CD-HOT'),
(@choco_id, 'Iced', 95.00, 'CD-ICED');

-- Variants for Matcha Latte
SET @matcha_id = (SELECT id FROM products WHERE name = 'Matcha Latte');
INSERT INTO `product_variants` (`product_id`, `variant_name`, `price`, `sku`) VALUES
(@matcha_id, 'Hot', 100.00, 'ML-HOT'),
(@matcha_id, 'Iced', 110.00, 'ML-ICED');

-- Rice Meals (Example)
SET @rice_meal_cat_id = (SELECT id FROM categories WHERE name = 'Rice Meal');
INSERT INTO `products` (`category_id`, `name`, `image_url`, `description`, `display_order`) VALUES
(@rice_meal_cat_id, 'Chicken Adobo Flakes', 'https://placehold.co/150x150/FFD700/6B4F4F?text=Adobo', 'P150', 1);
SET @adobo_id = (SELECT id FROM products WHERE name = 'Chicken Adobo Flakes');
INSERT INTO `product_variants` (`product_id`, `variant_name`, `price`, `sku`) VALUES
(@adobo_id, 'Regular', 150.00, 'RM-ADF');


-- Desserts (Example)
SET @dessert_cat_id = (SELECT id FROM categories WHERE name = 'Dessert');
INSERT INTO `products` (`category_id`, `name`, `image_url`, `description`, `display_order`) VALUES
(@dessert_cat_id, 'Blueberry Cheesecake', 'https://placehold.co/150x150/ADD8E6/6B4F4F?text=Cake', 'P120', 1);
SET @cake_id = (SELECT id FROM products WHERE name = 'Blueberry Cheesecake');
INSERT INTO `product_variants` (`product_id`, `variant_name`, `price`, `sku`) VALUES
(@cake_id, 'Slice', 120.00, 'DS-BCS');

