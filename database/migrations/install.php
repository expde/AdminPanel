<?php

function runMigrations($pdo) {
    // Create users table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            email_verified_at TIMESTAMP NULL,
            password VARCHAR(255) NOT NULL,
            role ENUM('super_admin', 'admin', 'staff') DEFAULT 'staff',
            remember_token VARCHAR(100) NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Create categories table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS categories (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) UNIQUE NOT NULL,
            description TEXT NULL,
            parent_id BIGINT UNSIGNED NULL,
            image VARCHAR(255) NULL,
            is_active BOOLEAN DEFAULT TRUE,
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL,
            FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Create products table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS products (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) UNIQUE NOT NULL,
            description TEXT NULL,
            short_description VARCHAR(500) NULL,
            price DECIMAL(10,2) NOT NULL,
            compare_price DECIMAL(10,2) NULL,
            cost_price DECIMAL(10,2) NULL,
            sku VARCHAR(100) UNIQUE NULL,
            barcode VARCHAR(100) NULL,
            stock_quantity INT DEFAULT 0,
            low_stock_threshold INT DEFAULT 5,
            weight DECIMAL(8,2) NULL,
            dimensions VARCHAR(100) NULL,
            category_id BIGINT UNSIGNED NOT NULL,
            is_active BOOLEAN DEFAULT TRUE,
            is_featured BOOLEAN DEFAULT FALSE,
            meta_title VARCHAR(255) NULL,
            meta_description TEXT NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Create product_images table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS product_images (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            product_id BIGINT UNSIGNED NOT NULL,
            image_path VARCHAR(255) NOT NULL,
            alt_text VARCHAR(255) NULL,
            sort_order INT DEFAULT 0,
            is_primary BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Create product_attributes table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS product_attributes (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            product_id BIGINT UNSIGNED NOT NULL,
            attribute_name VARCHAR(255) NOT NULL,
            attribute_value VARCHAR(255) NOT NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Create customers table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS customers (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            phone VARCHAR(20) NULL,
            address TEXT NULL,
            city VARCHAR(100) NULL,
            state VARCHAR(100) NULL,
            country VARCHAR(100) NULL,
            postal_code VARCHAR(20) NULL,
            date_of_birth DATE NULL,
            gender ENUM('male', 'female', 'other') NULL,
            is_active BOOLEAN DEFAULT TRUE,
            email_verified_at TIMESTAMP NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Create orders table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS orders (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            order_number VARCHAR(50) UNIQUE NOT NULL,
            customer_id BIGINT UNSIGNED NULL,
            customer_name VARCHAR(255) NOT NULL,
            customer_email VARCHAR(255) NOT NULL,
            customer_phone VARCHAR(20) NULL,
            shipping_address TEXT NOT NULL,
            billing_address TEXT NULL,
            subtotal DECIMAL(10,2) NOT NULL,
            tax_amount DECIMAL(10,2) DEFAULT 0,
            shipping_amount DECIMAL(10,2) DEFAULT 0,
            discount_amount DECIMAL(10,2) DEFAULT 0,
            total_amount DECIMAL(10,2) NOT NULL,
            status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded') DEFAULT 'pending',
            payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
            payment_method VARCHAR(50) NULL,
            payment_reference VARCHAR(100) NULL,
            notes TEXT NULL,
            shipped_at TIMESTAMP NULL,
            delivered_at TIMESTAMP NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL,
            FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Create order_items table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS order_items (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            order_id BIGINT UNSIGNED NOT NULL,
            product_id BIGINT UNSIGNED NOT NULL,
            product_name VARCHAR(255) NOT NULL,
            product_sku VARCHAR(100) NULL,
            quantity INT NOT NULL,
            unit_price DECIMAL(10,2) NOT NULL,
            total_price DECIMAL(10,2) NOT NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Create payments table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS payments (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            order_id BIGINT UNSIGNED NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            payment_method VARCHAR(50) NOT NULL,
            payment_reference VARCHAR(100) NULL,
            transaction_id VARCHAR(100) NULL,
            status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
            gateway_response TEXT NULL,
            processed_at TIMESTAMP NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Create settings table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS settings (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            key_name VARCHAR(255) UNIQUE NOT NULL,
            value TEXT NULL,
            type ENUM('text', 'number', 'boolean', 'json', 'file') DEFAULT 'text',
            description TEXT NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Create password_resets table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS password_resets (
            email VARCHAR(255) NOT NULL,
            token VARCHAR(255) NOT NULL,
            created_at TIMESTAMP NULL,
            INDEX password_resets_email_index (email)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Create failed_jobs table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS failed_jobs (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            uuid VARCHAR(255) UNIQUE NOT NULL,
            connection TEXT NOT NULL,
            queue TEXT NOT NULL,
            payload LONGTEXT NOT NULL,
            exception LONGTEXT NOT NULL,
            failed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Insert default categories
    $defaultCategories = [
        ['Gadget Items', 'gadget-items', 'Electronic gadgets and devices', null, 1],
        ['Clothes Items', 'clothes-items', 'Clothing and apparel', null, 2],
        ['Food Items', 'food-items', 'Food and beverages', null, 3],
        ['Mobile', 'mobile', 'Mobile phones and accessories', 1, 1],
        ['Laptop', 'laptop', 'Laptops and computers', 1, 2],
        ['Smart Watch', 'smart-watch', 'Smart watches and wearables', 1, 3],
        ['Accessories', 'accessories', 'Electronic accessories', 1, 4],
        ['T-shirt', 't-shirt', 'T-shirts and casual wear', 2, 1],
        ['Pant', 'pant', 'Pants and trousers', 2, 2],
        ['Polo Shirt', 'polo-shirt', 'Polo shirts', 2, 3],
        ['Panjabi', 'panjabi', 'Traditional panjabi', 2, 4],
        ['Rice', 'rice', 'Rice and grains', 3, 1],
        ['Lentils', 'lentils', 'Lentils and pulses', 3, 2],
        ['Honey', 'honey', 'Natural honey', 3, 3],
        ['Oil', 'oil', 'Cooking oils', 3, 4],
        ['Dates', 'dates', 'Fresh and dried dates', 3, 5]
    ];
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO categories (name, slug, description, parent_id, sort_order) VALUES (?, ?, ?, ?, ?)");
    foreach ($defaultCategories as $category) {
        $stmt->execute($category);
    }
    
    // Insert default settings
    $defaultSettings = [
        ['site_name', 'Expde Shop', 'text', 'Website name'],
        ['site_description', 'Your trusted online shopping destination', 'text', 'Website description'],
        ['site_logo', '', 'file', 'Website logo'],
        ['site_favicon', '', 'file', 'Website favicon'],
        ['contact_email', 'info@expdeshop.com', 'text', 'Contact email'],
        ['contact_phone', '+1234567890', 'text', 'Contact phone'],
        ['contact_address', '123 Main Street, City, Country', 'text', 'Contact address'],
        ['currency', 'USD', 'text', 'Default currency'],
        ['currency_symbol', '$', 'text', 'Currency symbol'],
        ['tax_rate', '10', 'number', 'Default tax rate (%)'],
        ['low_stock_threshold', '5', 'number', 'Low stock alert threshold'],
        ['enable_registration', '1', 'boolean', 'Enable customer registration'],
        ['enable_guest_checkout', '1', 'boolean', 'Enable guest checkout'],
        ['maintenance_mode', '0', 'boolean', 'Maintenance mode']
    ];
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO settings (key_name, value, type, description) VALUES (?, ?, ?, ?)");
    foreach ($defaultSettings as $setting) {
        $stmt->execute($setting);
    }
}
?>
