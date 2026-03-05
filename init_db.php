<?php
/**
 * PHPMicroFramework - Database Initialization Script
 * 
 * Instructions:
 * 1. Configure your .env or config.php with database credentials.
 * 2. Run this script once: php init_db.php
 * 
 * Default Credentials:
 * Admin: admin / admin123
 */

// Define BASE_DIR for the framework classes
require_once BASE_DIR . "config.php";
require_once BASE_DIR . "class/Conn.php";

$conn = new Conn();

echo "Starting Database Initialization...\n";

try {
    // 1. Create Users table (Example schema based on standard framework use)
    // Note: This matches the query in login/index.php
    $createTable = "
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usr_cd VARCHAR(50) NOT NULL UNIQUE,
        usr_name VARCHAR(100) NOT NULL,
        user_alias VARCHAR(100),
        usr_passwd VARCHAR(32) NOT NULL,
        rec_ind CHAR(1) DEFAULT 'A',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    // Adjust for PostgreSQL if needed
    if (defined('DB_DRIVER') && DB_DRIVER == 'pgsql') {
        $createTable = str_replace('INT AUTO_INCREMENT PRIMARY KEY', 'SERIAL PRIMARY KEY', $createTable);
    }

    $conn->ExecSQL($createTable);
    echo "[OK] Users table verified.\n";

    // 2. Create Menus Table
    $createMenus = "
    CREATE TABLE IF NOT EXISTS menus (
        id INT AUTO_INCREMENT PRIMARY KEY,
        label VARCHAR(100) NOT NULL,
        link VARCHAR(255),
        parent_id INT DEFAULT NULL,
        sort_order INT DEFAULT 0,
        icon VARCHAR(50),
        is_active TINYINT DEFAULT 1
    )";
    if (defined('DB_DRIVER') && DB_DRIVER == 'pgsql') {
        $createMenus = str_replace('INT AUTO_INCREMENT PRIMARY KEY', 'SERIAL PRIMARY KEY', $createMenus);
        $createMenus = str_replace('TINYINT', 'SMALLINT', $createMenus);
    }
    $conn->ExecSQL($createMenus);
    echo "[OK] Menus table verified.\n";

    // 3. Create CRUD Modules Table
    $createModules = "
    CREATE TABLE IF NOT EXISTS crud_modules (
        id INT AUTO_INCREMENT PRIMARY KEY,
        module_name VARCHAR(100) NOT NULL UNIQUE,
        table_name VARCHAR(100) NOT NULL,
        config TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    if (defined('DB_DRIVER') && DB_DRIVER == 'pgsql') {
        $createModules = str_replace('INT AUTO_INCREMENT PRIMARY KEY', 'SERIAL PRIMARY KEY', $createModules);
    }
    $conn->ExecSQL($createModules);
    echo "[OK] CRUD Modules table verified.\n";

    // 4. Sample Data for Menus
    $menuData = [
        ['Dashboard', 'index.php', null, 1],
        ['System Admin', '#', null, 100],
        ['Menu Editor', 'view/menu_editor.php', 2, 101],
        ['CRUD Builder', 'view/crud_builder.php', 2, 102],
        ['CRUD Modules', 'view/crud_list.php', 2, 103],
        ['User Management', 'view/list.php', null, 50]
    ];
    foreach ($menuData as $m) {
        $exists = $conn->SQLFetch("SELECT count(1) FROM menus WHERE label = ?", $m[0]);
        if ($exists == 0) {
            $conn->ExecSQL("INSERT INTO menus (label, link, parent_id, sort_order) VALUES (?, ?, ?, ?)", implode('~', $m));
        }
    }
    echo "[OK] Initial navigation links created.\n";

    // 5. Check if admin exists
    $adminExists = $conn->SQLFetch("SELECT count(1) FROM users WHERE usr_cd = 'admin'");

    if ($adminExists == 0) {
        $sql = "INSERT INTO users (usr_cd, usr_name, user_alias, usr_passwd, rec_ind) 
                VALUES ('admin', 'System Administrator', 'admin@example.com', md5('admin123'), 'A')";
        
        $conn->ExecSQL($sql);
        echo "[OK] Default credentials created: admin / admin123\n";
    } else {
        echo "[INFO] Admin user already exists. Skipping insertion.\n";
    }

    echo "\nDatabase setup complete. You can now log in at login/index.php\n";

} catch (Exception $e) {
    echo "[ERROR] Initialization failed: " . $e->getMessage() . "\n";
}
