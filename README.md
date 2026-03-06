# PHPMicroFramework: The Low-Code Engine for PHP

PHPMicroFramework is a high-performance, developer-friendly "low-code" framework designed to transform any database schema into a fully functional web application in minutes. It eliminates repetitive boilerplate by providing graphical tools for Menu management and CRUD (Create, Read, Update, Delete) generation.

---

## 🚀 Getting Started

### 1. Installation & Database Setup
1. Clone the repository to your local server.
2. Configure your database credentials in `config.php`.
3. Run the automated initialization script:
   ```bash
   php init_db.php
   ```
   *This script creates the internal meta-tables (`users`, `menus`, `crud_modules`) and sets up default admin credentials.*

### 2. Default Credentials
- **Username:** `admin`
- **Password:** `admin123`
- **Login URL:** `login/index.php`

---

## 🛠 Core Features

### 1. Graphical Menu Editor
Located at `/view/menu_editor.php`, this tool allows you to manage application navigation without touching HTML.
- **Recursive Navigation**: Supports parent-child links for nested dropdowns.
- **Sort Order**: Drag-and-drop logic via integer-based sorting.
- **Automatic Routing**: If a menu item points to a compiled "Fast Page," the system automatically routes to the optimized script.

### 2. CRUD Builder Wizard
Located at `/view/crud_builder.php`, the builder allows you to point to any database table and generate a complete management interface.
- **Fields Configuration**: Map column names to human-readable labels and UI input types.
- **Permissions**: Granularly enable or disable Add, Edit, and Delete operations for each module.
- **Custom Actions**: Add custom buttons per row that trigger JavaScript (e.g., `alert('{id}')`) or route to custom PHP scripts (e.g., `print_invoice.php?inv={id}`).

---

## 💎 Smart Placeholders & Default Values

The CRUD Builder supports **Smart Placeholders** in the "Default/Options" column to automate auditing and session-based fields.

| Placeholder         | Context        | Description                                                            |
| :------------------ | :------------- | :--------------------------------------------------------------------- |
| `{USER}`            | **Add Only**   | Auto-fills the current session user ID and marks as Readonly.          |
| `{DATE}`            | **Add Only**   | Auto-fills with the current server Date (YYYY-MM-DD).                  |
| `{DATETIME}`        | **Add Only**   | Auto-fills with current server Timestamp (YYYY-MM-DD HH:MM:SS).        |
| `{UPDATE_USER}`     | **Add & Edit** | Always replaces the value with the current session user on every save. |
| `{UPDATE_DATE}`     | **Add & Edit** | Always replaces the value with the current server date on every save.  |
| `{UPDATE_DATETIME}` | **Add & Edit** | Always replaces the current server timestamp on every save.            |

---

## 🔗 Advanced Field Types

### 1. Select Dropdowns
Dropdowns can be configured in two ways via the "Default/Options" field:
- **Static CSV**: Enter comma-separated values (e.g., `Active, Inactive, Suspended`).
- **Dynamic SQL**: Enter a full SQL query (e.g., `SELECT id, name FROM categories ORDER BY name`). The first column is used as the `value`, and the second as the `label`.

### 2. File & Image Uploads
- Select **File/Upload** as the input type.
- The system automatically adds `enctype="multipart/form-data"` to the form.
- Uploaded files are securely stored in the `/uploads/` directory with a timestamped unique name.
- The database stores the relative path (e.g., `uploads/162839129_photo.jpg`), which can be used for rendering.

### 3. Custom Action Placeholders
In addition to `{id}`, you can reference **any column** in your row within a custom action trigger using `{column_name}`.
- Example URL: `report.php?id={id}&status={status}&user={usr_cd}`
- Example JS: `showProfile('{user_alias}', {id})`

---

## 🚄 Performance Model: Dynamic vs. Fast Pages

PHPMicroFramework offers a unique hybrid delivery model to balance development speed with production performance.

### Dynamic View (`dynamic_view.php`)
The default discovery mode. It reads the module configuration from the JSON database on every request. **Best for active development.**

### Fast Cached Pages (`Compiler.php`)
For production environments, you can "Compile" a module from the **CRUD List** page.
- **The Compiler**: Generates a physical `.php` file (e.g., `view/fast_mod_1.php`) with hardcoded SQL, fields, and permissions.
- **Speed**: Bypasses multiple database lookups and JSON decoding per request.
- **Toggle System**: You can toggle "Fast Mode" on or off per module. When ON, the Navbar and Sidebar links automatically update to use the cached file instead of the dynamic renderer.

---

## 🔒 Security
- **Parameterization**: All database interactions use PDO prepared statements to prevent SQL Injection.
- **SQL Cleanup**: Integrated `Util::C_CLEAN_UPDATE` validation ensures no malicious query fragments enter the execution engine.
- **Session Validation**: Views are protected by `headerAuth.php` to ensure only logged-in administrators can access the builder and modules.
