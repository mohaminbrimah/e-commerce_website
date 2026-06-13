-- Update product images to use the real files in assets/images
-- Run this in phpMyAdmin if you prefer fixing the DB rows directly.
USE mab_shop;

UPDATE product_images SET image_path = 'assets/images/galaxyA54.jpg', alt_text = 'Samsung Galaxy A54' WHERE product_id = 1;
UPDATE product_images SET image_path = 'assets/images/iphone14.jpg', alt_text = 'iPhone 14' WHERE product_id = 2;
UPDATE product_images SET image_path = 'assets/images/hp_pavillion15.jpg', alt_text = 'HP Pavilion 15' WHERE product_id = 3;
UPDATE product_images SET image_path = 'assets/images/nike_air_max90.jpg', alt_text = 'Nike Air Max 90' WHERE product_id = 4;
UPDATE product_images SET image_path = 'assets/images/nike_revolution6.jpg', alt_text = 'Nike Revolution 6' WHERE product_id = 5;
UPDATE product_images SET image_path = 'assets/images/adidas_ultraboost22.jpg', alt_text = 'Adidas Ultraboost 22' WHERE product_id = 6;
UPDATE product_images SET image_path = 'assets/images/cotton_polo_shirt.jpg', alt_text = 'Men Cotton Polo Shirt' WHERE product_id = 7;
UPDATE product_images SET image_path = 'assets/images/sony_wh-1000xm5.jpg', alt_text = 'Sony WH-1000XM5' WHERE product_id = 8;
