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
        return $this->conn->SQLCursor("SELECT * FROM menus WHERE is_active = 1 ORDER BY sort_order, label");
    }

    /**
     * Render HTML for the menu
     */
    public function renderNavbar() {
        $items = $this->getMenus();
        if (!$items) return "";

        // Build Tree
        $tree = [];
        foreach ($items as $item) {
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
