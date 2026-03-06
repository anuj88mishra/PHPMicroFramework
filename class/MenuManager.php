<?php
/**
 * MenuManager Class for dynamic navigation
 */
require_once BASE_DIR . "class/Conn.php";

class MenuManager {
    private $conn;

    public function __construct() {
        $this->conn = new Conn();
    }

    /**
     * Get all active menu items
     */
    public function getMenus() {
        if (empty($_SESSION['ROLE_IDS'])) return [];
        $roleIds = $_SESSION['ROLE_IDS'];
        $placeholders = implode(',', array_fill(0, count($roleIds), '?'));
        $param = implode('~', $roleIds);
        
        return $this->conn->SQLCursor("SELECT DISTINCT m.* FROM menus m JOIN role_menus rm ON m.id = rm.menu_id WHERE m.is_active = 1 AND rm.role_id IN ($placeholders) ORDER BY m.sort_order, m.label", $param);
    }

    /**
     * Render HTML for the menu
     */
    public function renderNavbar() {
        $items = $this->getMenus();
        if (!$items) return "";

        // Fetch CRUD modules that have fast page enabled to do the swap
        $fastModules = $this->conn->SQLCursor("SELECT id FROM crud_modules WHERE use_fast_page = 1");
        $fastIds = [];
        if (is_array($fastModules)) {
            foreach($fastModules as $fm) $fastIds[] = $fm['id'];
        }

        // Build Tree
        $tree = [];
        foreach ($items as $item) {
            // Automatic Fast Page Switching
            if (preg_match('/dynamic_view\.php\?id=(\d+)/', $item['link'], $matches)) {
                $mid = $matches[1];
                if (in_array($mid, $fastIds)) {
                    $item['link'] = str_replace("dynamic_view.php?id=$mid", "fast_mod_$mid.php", $item['link']);
                }
            }

            if ($item['parent_id'] == null) {
                $tree[$item['id']] = $item;
                $tree[$item['id']]['children'] = [];
            }
        }

        foreach ($items as $item) {
            if ($item['parent_id'] != null && isset($tree[$item['parent_id']])) {
                $tree[$item['parent_id']]['children'][] = $item;
            }
        }

        $html = '<div class="navbar-start">';
        foreach ($tree as $main) {
            if (empty($main['children'])) {
                $html .= '<a class="navbar-item" href="'.BASE_URL.$main['link'].'">'.$main['label'].'</a>';
            } else {
                $html .= '<div class="navbar-item has-dropdown is-hoverable">';
                $html .= '<a class="navbar-link">'.$main['label'].'</a>';
                $html .= '<div class="navbar-dropdown">';
                foreach ($main['children'] as $child) {
                    $html .= '<a class="navbar-item" href="'.BASE_URL.$child['link'].'">'.$child['label'].'</a>';
                }
                $html .= '</div></div>';
            }
        }
        $html .= '</div>';
        return $html;
    }
}
