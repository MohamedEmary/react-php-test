-- DROP DATABASE IF EXISTS scandiweb_test;

CREATE DATABASE IF NOT EXISTS scandiweb_test;

USE scandiweb_test;

CREATE TABLE IF NOT EXISTS categories (
    name VARCHAR(100) NOT NULL PRIMARY KEY
);

CREATE TABLE IF NOT EXISTS products (
    id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    brand VARCHAR(100),
    category_name VARCHAR(100),
    in_stock BOOLEAN DEFAULT true,
    FOREIGN KEY (category_name) REFERENCES categories (name)
);

CREATE TABLE IF NOT EXISTS product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id VARCHAR(50),
    image_url TEXT NOT NULL,
    FOREIGN KEY (product_id) REFERENCES products (id)
);

CREATE TABLE IF NOT EXISTS attribute_sets (
    id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type VARCHAR(50) NOT NULL
);

CREATE TABLE IF NOT EXISTS attribute_items (
    id VARCHAR(50),
    attribute_set_id VARCHAR(50),
    product_id VARCHAR(50),
    display_value VARCHAR(100),
    value VARCHAR(100),
    FOREIGN KEY (attribute_set_id) REFERENCES attribute_sets (id),
    FOREIGN KEY (product_id) REFERENCES products (id),
    UNIQUE KEY unique_attribute_item (
        attribute_set_id,
        product_id,
        value
    )
);

CREATE TABLE IF NOT EXISTS currencies (
    label VARCHAR(10) PRIMARY KEY,
    symbol VARCHAR(5) NOT NULL
);

CREATE TABLE IF NOT EXISTS prices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id VARCHAR(50),
    amount DECIMAL(10, 2) NOT NULL,
    currency_label VARCHAR(10),
    FOREIGN KEY (product_id) REFERENCES products (id),
    FOREIGN KEY (currency_label) REFERENCES currencies (label)
);

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY
);

CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id VARCHAR(50) NOT NULL,
    quantity INT DEFAULT 1,
    FOREIGN KEY (user_id) REFERENCES users (id),
    FOREIGN KEY (product_id) REFERENCES products (id)
);

CREATE TABLE IF NOT EXISTS order_attributes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    attribute_set_id VARCHAR(50) NOT NULL,
    selected_value VARCHAR(100) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders (id),
    FOREIGN KEY (attribute_set_id) REFERENCES attribute_sets (id)
);