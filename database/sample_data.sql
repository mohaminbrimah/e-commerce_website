-- ============================================================================
-- MAB Shop - Sample Data for Testing
-- ============================================================================

USE mab_shop;

-- Admin user (password: password)
INSERT INTO users (email, password_hash, first_name, last_name, phone, role, email_verified) VALUES
('admin@mabshop.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'MAB', 'Admin', '+233201234567', 'admin', 1),
('customer@mabshop.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Kwame', 'Mensah', '+233241234567', 'customer', 1),
('ama@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ama', 'Osei', '+233551234567', 'customer', 1);

-- Categories
INSERT INTO categories (id, parent_id, name, slug, description, sort_order) VALUES
(1, NULL, 'Electronics', 'electronics', 'Latest gadgets and devices', 1),
(2, NULL, 'Fashion', 'fashion', 'Clothing and accessories', 2),
(3, NULL, 'Footwear', 'footwear', 'Shoes and sneakers', 3),
(4, 1, 'Smartphones', 'smartphones', 'Mobile phones', 1),
(5, 1, 'Laptops', 'laptops', 'Notebooks and laptops', 2),
(6, 2, 'Men', 'men-fashion', 'Men clothing', 1),
(7, 3, 'Sneakers', 'sneakers', 'Casual sneakers', 1);

-- Brands
INSERT INTO brands (name, slug) VALUES
('Samsung', 'samsung'),
('Apple', 'apple'),
('Nike', 'nike'),
('Adidas', 'adidas'),
('HP', 'hp'),
('Sony', 'sony');

-- Products
INSERT INTO products (category_id, brand_id, name, slug, description, short_description, price, compare_price, sku, stock_quantity, color, size, rating_avg, rating_count, sold_count, is_featured) VALUES
(4, 1, 'Samsung Galaxy A54', 'samsung-galaxy-a54', 'Powerful mid-range smartphone with stunning display and camera.', '5G smartphone with 128GB storage', 2499.00, 2799.00, 'SAM-A54-128', 45, 'Black', NULL, 4.50, 28, 120, 1),
(4, 2, 'iPhone 14', 'iphone-14', 'Apple iPhone 14 with A15 Bionic chip and advanced camera system.', 'Premium Apple smartphone', 5999.00, 6499.00, 'APL-IP14-128', 20, 'Blue', NULL, 4.80, 56, 89, 1),
(5, 5, 'HP Pavilion 15', 'hp-pavilion-15', 'Reliable laptop for work and study with Intel Core i5.', '15.6" laptop, 8GB RAM', 4299.00, 4599.00, 'HP-PAV-15', 15, 'Silver', NULL, 4.30, 19, 45, 0),
(7, 3, 'Nike Air Max 90', 'nike-air-max-90', 'Classic Nike sneakers with Air cushioning technology.', 'Iconic black sneakers', 599.00, 699.00, 'NIKE-AM90-BLK', 60, 'Black', '42', 4.60, 42, 200, 1),
(7, 3, 'Nike Revolution 6', 'nike-revolution-6', 'Lightweight running shoes for everyday comfort.', 'Affordable running shoes', 279.00, 349.00, 'NIKE-REV6-WHT', 80, 'White', '43', 4.20, 31, 150, 0),
(7, 4, 'Adidas Ultraboost 22', 'adidas-ultraboost-22', 'Premium running shoes with Boost midsole.', 'High-performance runners', 899.00, 1099.00, 'ADI-UB22-GRY', 35, 'Grey', '44', 4.70, 38, 95, 1),
(6, NULL, 'Men Cotton Polo Shirt', 'men-cotton-polo', 'Comfortable cotton polo shirt for casual wear.', 'Classic fit polo', 89.00, 120.00, 'POLO-M-BLU', 100, 'Blue', 'L', 4.10, 15, 80, 0),
(1, 6, 'Sony WH-1000XM5', 'sony-wh-1000xm5', 'Industry-leading noise cancelling headphones.', 'Premium wireless headphones', 1899.00, 2199.00, 'SONY-XM5-BLK', 25, 'Black', NULL, 4.90, 67, 110, 1);

-- Product images (placeholder paths)
INSERT INTO product_images (product_id, image_path, alt_text, sort_order, is_primary) VALUES
(1, 'assets/images/products/samsung-a54.jpg', 'Samsung Galaxy A54', 0, 1),
(2, 'assets/images/products/iphone-14.jpg', 'iPhone 14', 0, 1),
(3, 'assets/images/products/hp-pavilion.jpg', 'HP Pavilion 15', 0, 1),
(4, 'assets/images/products/nike-air-max.jpg', 'Nike Air Max 90', 0, 1),
(5, 'assets/images/products/nike-revolution.jpg', 'Nike Revolution 6', 0, 1),
(6, 'assets/images/products/adidas-ultraboost.jpg', 'Adidas Ultraboost 22', 0, 1),
(7, 'assets/images/products/polo-shirt.jpg', 'Men Cotton Polo', 0, 1),
(8, 'assets/images/products/sony-headphones.jpg', 'Sony WH-1000XM5', 0, 1);

-- Product specifications
INSERT INTO product_specifications (product_id, spec_key, spec_value, sort_order) VALUES
(4, 'Material', 'Mesh & Leather', 1),
(4, 'Sole', 'Rubber', 2),
(4, 'Closure', 'Lace-up', 3),
(5, 'Material', 'Synthetic', 1),
(5, 'Sole', 'Foam', 2),
(6, 'Material', 'Primeknit', 1),
(6, 'Sole', 'Continental Rubber', 2),
(1, 'Display', '6.4" Super AMOLED', 1),
(1, 'Storage', '128GB', 2),
(1, 'RAM', '8GB', 3),
(2, 'Display', '6.1" OLED', 1),
(2, 'Storage', '128GB', 2);

-- Product bundles / related
INSERT INTO product_bundles (product_id, related_product_id, bundle_type, sort_order) VALUES
(4, 5, 'bought_together', 1),
(4, 6, 'similar', 1),
(5, 4, 'similar', 1),
(1, 2, 'similar', 1),
(1, 8, 'bought_together', 1);

-- Coupons
INSERT INTO coupons (code, description, discount_type, discount_value, min_order_amount, max_uses, expires_at) VALUES
('WELCOME10', '10% off first order', 'percentage', 10.00, 100.00, 1000, DATE_ADD(NOW(), INTERVAL 1 YEAR)),
('SAVE50', 'GH₵50 off orders over GH₵500', 'fixed', 50.00, 500.00, 500, DATE_ADD(NOW(), INTERVAL 6 MONTH)),
('FLASH20', '20% flash sale', 'percentage', 20.00, 200.00, 100, DATE_ADD(NOW(), INTERVAL 30 DAY));

-- FAQs
INSERT INTO faqs (question, answer, category, sort_order) VALUES
('How long does shipping take?', 'Standard delivery takes 2-5 business days within Ghana. Express delivery is available in Accra within 24 hours.', 'Shipping', 1),
('What payment methods do you accept?', 'We accept Mobile Money (MTN, Vodafone, AirtelTigo), Visa, Mastercard, PayPal, and bank transfer.', 'Payments', 2),
('Can I return a product?', 'Yes, you can return unused items within 14 days of delivery for a full refund.', 'Returns', 3),
('Do you offer guest checkout?', 'Yes, you can checkout as a guest without creating an account.', 'Orders', 4);

-- Sample reviews
INSERT INTO reviews (product_id, user_id, rating, title, comment, is_verified_buyer, status) VALUES
(4, 2, 5, 'Great sneakers!', 'Very comfortable and stylish. Perfect fit.', 1, 'approved'),
(4, 3, 4, 'Good quality', 'Nice shoes but runs slightly small.', 1, 'approved'),
(1, 2, 5, 'Excellent phone', 'Great value for money. Camera is amazing.', 1, 'approved');

-- Sample address
INSERT INTO addresses (user_id, label, full_name, phone, address_line1, city, region, country, is_default) VALUES
(2, 'Home', 'Kwame Mensah', '+233241234567', '12 Independence Avenue', 'Accra', 'Greater Accra', 'Ghana', 1);
