<?php
// 数据库初始化脚本
require_once __DIR__ . '/../config/database.php';

try {
    // 创建数据库连接 (不指定数据库)
    $pdo = new PDO("mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 创建数据库
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "数据库创建成功<br>";
    
    // 选择数据库
    $pdo->exec("USE " . DB_NAME);
    
    // 创建用户表
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            user_type ENUM('admin', 'buyer', 'supplier', 'engineer') NOT NULL,
            real_name VARCHAR(100),
            phone VARCHAR(20),
            company VARCHAR(200),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_username (username),
            INDEX idx_user_type (user_type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "用户表创建成功<br>";
    
    // 创建订单表
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            buyer_id INT NOT NULL,
            company_name VARCHAR(200) NOT NULL,
            purchase_quantity INT NOT NULL DEFAULT 0,
            install_quantity INT NOT NULL DEFAULT 0,
            install_address VARCHAR(500),
            install_latitude DECIMAL(10, 7),
            install_longitude DECIMAL(10, 7),
            order_contact VARCHAR(100),
            order_phone VARCHAR(20),
            receiver_name VARCHAR(100),
            receiver_address VARCHAR(500),
            receiver_phone VARCHAR(20),
            install_contact VARCHAR(100),
            install_phone VARCHAR(20),
            is_shipped TINYINT(1) DEFAULT 0,
            tracking_number VARCHAR(100),
            is_received TINYINT(1) DEFAULT 0,
            received_quantity INT DEFAULT 0,
            remarks TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_buyer_id (buyer_id),
            INDEX idx_company_name (company_name),
            INDEX idx_is_shipped (is_shipped),
            INDEX idx_is_received (is_received)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "订单表创建成功<br>";
    
    // 创建安装需求清单表
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS install_requirements (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            vehicle_plate VARCHAR(50) NOT NULL,
            terminal_number VARCHAR(50) NOT NULL,
            device_serial VARCHAR(100),
            sim_serial VARCHAR(100),
            installed TINYINT(1) DEFAULT 0,
            install_time DATETIME,
            engineer_id INT,
            engineer_name VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (engineer_id) REFERENCES users(id) ON DELETE SET NULL,
            INDEX idx_order_id (order_id),
            INDEX idx_installed (installed),
            INDEX idx_vehicle_plate (vehicle_plate)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "安装需求清单表创建成功<br>";
    
    // 插入默认管理员账号
    $stmt = $pdo->prepare("
        INSERT IGNORE INTO users (username, password, user_type, real_name) 
        VALUES ('admin', :password, 'admin', '系统管理员')
    ");
    $stmt->execute(['password' => password_hash('admin', PASSWORD_DEFAULT)]);
    echo "默认管理员账号创建成功 (用户名: admin, 密码: admin)<br>";
    
    echo "<br><strong>数据库初始化完成!</strong><br>";
    echo "<a href='../login.html'>前往登录页面</a>";
    
} catch (PDOException $e) {
    die("安装失败: " . $e->getMessage());
}
?>
