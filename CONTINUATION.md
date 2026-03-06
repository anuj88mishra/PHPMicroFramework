# PHPMicroFramework - Development Continuation Document

This document summarizes the platform's evolution into a high-performance, enterprise-ready low-code engine.

## 🏆 Current Platform State

The `PHPMicroFramework` has reached a milestone where it can handle sophisticated database management with minimal manual coding.

### 1. Framework Core & Security
*   **RBAC (Role-Based Access Control)**: Implemented a robust security layer including `roles`, `user_roles`, `role_modules`, and `role_menus`.
*   **Enforcement**: 
    *   **Login**: Now tracks user ID, Name, and assigned Roles in the session.
    *   **Authorization**: Every access to a dynamic or compiled CRUD module is now verified against the user's roles.
    *   **Admin Protection**: Core utility pages (Builder, Compiler, Menu Editor) are strictly restricted to the "Administrator" role.
*   **Responsive UI**: Updated the navbar to display the logged-in user's full name and their active roles.

### 2. Intelligent Data Management
*   **Default Values**: Supported `{USER}`, `{DATE}`, and `{DATETIME}` placeholders for automated auditing. Added `{UPDATE_...}` variants to ensure data integrity during edits.
*   **Select/Dropdown Types**: Fully functional `select` fields supporting both manual CSV lists and dynamic SQL lookups (`SELECT id, name FROM table`).
*   **File Uploads**: Native support for binary uploads. The system automatically handles form encoding, directory creation, unique file naming, and path storage.

### 3. Performance & Compilation
*   **The Compiler Class**: A dedicated engine that transforms dynamic JSON-driven modules into high-speed, static PHP files.
*   **Hybrid Delivery**: A toggle system allows administrators to alternate between "Dynamic Mode" (for live configuration changes) and "Fast Mode" (static cached execution) without changing code.
*   **Smart Routing**: The `MenuManager` and `CRUD List` now automatically route users to compiled files if "Fast Mode" is enabled.

### 4. CRUD Builder Refinements
*   **Module Editing**: You can now "Edit Definition" for any existing module. The wizard pre-populates all field settings, permissions, and custom actions.
*   **Custom Action Placeholders**: Extended placeholder replacement allows any column in the row to be referenced in a button trigger (e.g., `process.php?ref={order_no}`).

---

## 🚀 Future Roadmap & Potential Enhancements

While the core functionality is complete, the following areas can be explored for further framework growth:

### 1. Unified Security Management UI
*   **Goal**: Create a graphical dashboard to manage roles and assignments.
*   **Execution**: Use the existing CRUD Builder to generate interfaces for the RBAC tables (`roles`, `user_roles`, etc.), allowing non-developers to manage system access.

### 2. Multi-Table Joins in Builder
*   **Goal**: Allow the CRUD builder to join related tables in the list view.
*   **Execution**: Enhance the "Step 2" configuration to allow specifying a "Display Column" from a linked table (e.g., showing `category_name` instead of `category_id`).

### 3. Client-Side Validation
*   **Goal**: Add real-time form validation.
*   **Execution**: Extend the field configuration to include regex patterns or common validation types (email, telephone, numeric ranges) that generate HTML5 validation attributes.

### 4. Theming Engine
*   **Goal**: Allow users to switch between different Bulma-based color schemes.
*   **Execution**: Use a configuration variable to switch CSS imports and update the UI components accordingly.
   
### 5. Sorting & Searching in modules
*   **Goal**: Allow users to sort and search in modules.
*   **Execution**: Add sorting and searching functionality to the CRUD modules.

### 6. Even faster pages
* **Goal**: Make the pages even faster.
* **Execution**: Update the compiler to generate PHP code bypassing the arrays and Gen class being used (a temporary file may be used while compiling), and directly generate the HTML code. PHP will be used only for RBAC, fetching and updating data by utilizing the Conn class.
   
### 7. API
* **Goal**: Create an API for the framework.
* **Subgoal**: To use the framework as a headless CMS by having an init_cms.php script.
* **Execution**: Create an API folder consisting of API endpoints for the modules.

### 8. Reports
* **Goal**: Option to create sortable/searchable reports from SQL queries.
* **Execution**: Create a reports sub-folder under view consisting any custom reports generated using SQL queries this would also include and custom actions per record as provided in the module.

### 9. Modules menu in the menu bar
* **Goal**: Add a menu in the menu bar to display all the modules available to the user. 

### 10. Any and all errors should use the ERR array constant.

### 11. A dedicated public/upload folder 
* **Goal**: Organised Uploads
* **Execution**: The file name should utilise the module_id_{last_insert_id}_filename.ext format to prevent accidental overwrites. The upload folder should be included in the gitignore file and the application should autogenerate and utilise YYYY/MM folders under uploads to keep uploaded files organised.

### 12. Documentation
* **Goal**: Create a comprehensive documentation for the framework.
* **Execution**: Create a docs folder consisting of interlinked Markdown files for guiding developers to use the module.

### 13. Custom Values for existing columns and option for additional custom columns
* **Goal**: Allow users to specify custom values for columns in list and view pages.
* **Execution**: Add functionality to the CRUD Builder to allow users to specify custom values for columns e.g. <a href="thatpage.php?id={id}">{name}</a>.

---

## Known Bugs

1. Blank type for control! - When adding/ editing a record in module 3 the above message is displayed.
2. no values in dropdown for selecting Parent Item in Menu Editor.

