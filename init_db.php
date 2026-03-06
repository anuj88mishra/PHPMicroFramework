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
require_once __DIR__ . "/config.php";
require_once BASE_DIR . "class/Conn.php";

$conn = new Conn();

echo "Starting Database Initialization...\n";

try {
    $idType = (defined('DB_DRIVER') && DB_DRIVER == 'pgsql') ? 'SERIAL PRIMARY KEY' : 'INT AUTO_INCREMENT PRIMARY KEY';

    // 1. Create Users table
    $createTable = "
    CREATE TABLE IF NOT EXISTS users (
        id $idType,
        usr_cd VARCHAR(50) NOT NULL UNIQUE,
        usr_name VARCHAR(100) NOT NULL,
        user_alias VARCHAR(100),
        usr_passwd VARCHAR(32) NOT NULL,
        rec_ind CHAR(1) DEFAULT 'A',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->ExecSQL($createTable);
    echo "[OK] Users table verified.\n";

    // 2. Create Menus Table
    $createMenus = "
    CREATE TABLE IF NOT EXISTS menus (
        id $idType,
        label VARCHAR(100) NOT NULL,
        link VARCHAR(255),
        parent_id INT DEFAULT NULL,
        sort_order INT DEFAULT 0,
        icon VARCHAR(50),
        is_active TINYINT DEFAULT 1
    )";
    if (defined('DB_DRIVER') && DB_DRIVER == 'pgsql') {
        $createMenus = str_replace('TINYINT', 'SMALLINT', $createMenus);
    }
    $conn->ExecSQL($createMenus);
    echo "[OK] Menus table verified.\n";

    // 3. Create CRUD Modules Table
    $createModules = "
    CREATE TABLE IF NOT EXISTS crud_modules (
        id $idType,
        module_name VARCHAR(100) NOT NULL UNIQUE,
        table_name VARCHAR(100) NOT NULL,
        config TEXT NOT NULL,
        is_compiled INT DEFAULT 0,
        use_fast_page INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->ExecSQL($createModules);
    echo "[OK] CRUD Modules table verified.\n";

    // 4. Create RBAC Tables
    $conn->ExecSQL("CREATE TABLE IF NOT EXISTS roles (
        id $idType,
        role_name VARCHAR(50) NOT NULL UNIQUE
    )");

    $conn->ExecSQL("CREATE TABLE IF NOT EXISTS user_roles (
        user_id INT,
        role_id INT,
        PRIMARY KEY (user_id, role_id)
    )");

    $conn->ExecSQL("CREATE TABLE IF NOT EXISTS role_modules (
        role_id INT,
        module_id INT,
        PRIMARY KEY (role_id, module_id)
    )");

    $conn->ExecSQL("CREATE TABLE IF NOT EXISTS role_menus (
        role_id INT,
        menu_id INT,
        PRIMARY KEY (role_id, menu_id)
    )");
    echo "[OK] RBAC Tables verified.\n";

    // 5. Sample Data for Menus
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

    // 6. Default Admin User
    $adminUserId = $conn->SQLFetch("SELECT id FROM users WHERE usr_cd = 'admin'");
    if (!$adminUserId) {
        $sql = "INSERT INTO users (usr_cd, usr_name, user_alias, usr_passwd, rec_ind) 
                VALUES ('admin', 'System Administrator', 'admin@example.com', md5('admin123'), 'A')";
        $conn->ExecSQL($sql);
        $adminUserId = $conn->SQLFetch("SELECT id FROM users WHERE usr_cd = 'admin'");
        echo "[OK] Default credentials created: admin / admin123\n";
    }

    // 7. Seed Administrator Role
    $adminRoleId = $conn->SQLFetch("SELECT id FROM roles WHERE role_name = 'Administrator'");
    if (!$adminRoleId) {
        $conn->ExecSQL("INSERT INTO roles (role_name) VALUES ('Administrator')");
        $adminRoleId = $conn->SQLFetch("SELECT id FROM roles WHERE role_name = 'Administrator'");
    }
    echo "[OK] Administrator role verified.\n";

    // 8. Assign Admin User to Administrator Role
    if ($adminUserId && $adminRoleId) {
        $conn->ExecSQL("INSERT INTO user_roles (user_id, role_id) SELECT ?, ? WHERE NOT EXISTS (SELECT 1 FROM user_roles WHERE user_id = ? AND role_id = ?)", "$adminUserId~$adminRoleId~$adminUserId~$adminRoleId");
        echo "[OK] Admin user assigned to Administrator role.\n";
    }

    // 9. Grant Administrator Role access to ALL current menus and modules
    $menus = $conn->SQLCursor("SELECT id FROM menus");
    if (is_array($menus)) {
        foreach ($menus as $m) {
            $conn->ExecSQL("INSERT INTO role_menus (role_id, menu_id) SELECT ?, ? WHERE NOT EXISTS (SELECT 1 FROM role_menus WHERE role_id = ? AND menu_id = ?)", "$adminRoleId~{$m['id']}~$adminRoleId~{$m['id']}");
        }
    }

    $modules = $conn->SQLCursor("SELECT id FROM crud_modules");
    if (is_array($modules)) {
        foreach ($modules as $mod) {
            $conn->ExecSQL("INSERT INTO role_modules (role_id, module_id) SELECT ?, ? WHERE NOT EXISTS (SELECT 1 FROM role_modules WHERE role_id = ? AND module_id = ?)", "$adminRoleId~{$mod['id']}~$adminRoleId~{$mod['id']}");
        }
    }
    echo "[OK] Administrator permissions synced.\n";

    echo "\nDatabase setup complete. You can now log in at login/index.php\n";

} catch (Exception $e) {
    echo "[ERROR] Initialization failed: " . $e->getMessage() . "\n";
}
