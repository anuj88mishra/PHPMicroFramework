# PHPMicroFramework - Development Continuation Document

This document serves as a checkpoint to summarize the progress made so far and outline the specific features to be implemented in the upcoming development sessions.

## 🏆 What We Have Accomplished

We have significantly upgraded the `PHPMicroFramework` from a basic template into a robust, low-code rapid application development platform.

### 1. Framework Enhancements & Bug Fixes
* **Database Agnosticism**: Parameterized the PDO driver (`DB_DRIVER`) in `Conn.php` and adapted the auto-search logic in `Gen.php` to dynamically switch between PostgreSQL (`::varchar ILIKE`) and MySQL (`CAST(... AS CHAR) LIKE`).
* **Security & Utility Refinement**: Enhanced SQL validation regex (`\b`) in `Util.php` to avoid false positives (e.g., allowing `LAST_INSERT_ID()`). Addressed redirect vulnerabilities by enforcing `exit()` after headers.
* **Component Loading**: Replaced fragile `include` statements with `require_once` across all core files to unequivocally resolve fatal "Cannot declare class" errors during complex module loading.
* **Frontend Interactivity**: Added standard Bulma JS triggers (Burger menu toggle, Notification dismissal) to `js/app.js`.

### 2. Graphical Menu System
* **Menu Engine**: Created a recursive `MenuManager.php` to render multi-level navigation trees.
* **Menu Editor UI (`view/menu_editor.php`)**: Built a graphical interface for administrators to add, edit, link, and reorganize parent/child navigation links without touching HTML.

### 3. Rapid CRUD Builder Engine
* **Setup Script (`init_db.php`)**: An automated deployment script that constructs the necessary meta-tables (`users`, `menus`, `crud_modules`) and creates standard default admin credentials.
* **Schema Introspection**: Programmed `Conn.php` to analyze database tables and columns natively via `SHOW COLUMNS` (MySQL) or `information_schema` (PostgreSQL).
* **Builder Wizard (`view/crud_builder.php`)**: A graphical setup tool letting admins point to any database table and declare field mappings, UI labels, HTML input types, summary table visibility, Add/Edit/Delete permissions, and custom row actions (Buttons executing JS or routing to PHP scripts).
* **Dynamic Content Renderer (`view/dynamic_view.php`)**: An intelligent view script that reads a saved JSON configuration and automatically assembles fully functioning List grids, Add forms, and securely parameterized Edit forms on-the-fly.

---

## 🚀 Upcoming Features (Productivity Roadmap)

The following 4 features have been requested and will be the primary focus of the next session:

### 1. Default Values Configuration
* **Goal**: Provide the option to specify default values during module definition.
* **Execution**: Fields like `created_at`, `updated_at`, `created_by`, or `last_edited_by` will automatically hook into system constants or Session data (`$_SESSION['USER']`) during the Add/Edit processes inside the dynamic forms.

### 2. Select Type Value Specification
* **Goal**: Allow dynamic drop-downs in auto-generated forms.
* **Execution**: Enhance the CRUD Builder to capture mapping data for `select` types. This could accept comma-separated manual values (e.g., `Active, Inactive, Pending`) or foreign key lookups (e.g., `SELECT id, name FROM categories`).

### 3. File Upload Facilitation
* **Goal**: Support attachments and image uploads natively.
* **Execution**: 
    1. Introduce a "File/Upload" input type in the CRUD Builder.
    2. Upgrade `Gen::form()` to support `enctype="multipart/form-data"`.
    3. Program `dynamic_view.php` to handle secure `$_FILES` parsing, moving the file to an upload directory, and recording the file path string in the target table row.

### 4. Code Generation (Fast Cached Pages)
* **Goal**: Eliminate runtime database configuration queries for production environments.
* **Execution**: Introduce a "Compile Module" feature. Once a CRUD page acts exactly as desired dynamically, the System Admin can hit a button to generate a physical, dedicated PHP file (e.g., `view/compiled_moduleName.php`). This script will have all arrays and queries completely hardcoded, bypassing JSON decoding and framework overhead for maximum performance.

### 5. Extended Custom Action Parameters
* **Goal**: Allow custom actions to reference any column data from the row, not just the primary key (`id`).
* **Execution**: Upgrade the string replacement logic in `dynamic_view.php`. Instead of just replacing `{id}`, the script will loop through all `$selectFields` and replace `{column_name}` placeholders in the custom action trigger with the actual `$row['column_name']` values (e.g., `print.php?id={id}&status={status}`).
