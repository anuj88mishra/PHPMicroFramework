<?php
require_once BASE_DIR . "class/Conn.php";

class Compiler {
    private $conn;

    public function __construct() {
        $this->conn = new Conn();
    }

    public function compile($moduleID) {
        $module = $this->conn->SQLFetchRow("SELECT * FROM crud_modules WHERE id = ?", $moduleID);
        if (!$module) {
            return ["status" => false, "message" => "Module not found."];
        }

        $configData = json_decode($module['config'], true);
        $fields = $configData['fields'] ?? [];
        $permissions = $configData['permissions'] ?? [];
        $customActions = $configData['custom_actions'] ?? [];
        $tableName = $module['table_name'];
        $moduleName = $module['module_name'];

        $colMetadata = $this->conn->getTableColumns($tableName);
        $autoCols = [];
        foreach ($colMetadata as $col) {
            if (isset($col['Extra']) && stripos($col['Extra'], 'auto_increment') !== false) {
                $autoCols[] = $col['Field'];
            } elseif (isset($col['Key']) && $col['Key'] == 'PRI' && stripos($col['Type'], 'int') !== false) {
                $autoCols[] = $col['Field'];
            }
        }

        $selectFields = array_keys(array_filter($fields, function($f) { return isset($f['show_table']); }));
        if (!in_array('id', $selectFields)) { $queryFields = array_merge(['id'], $selectFields); } else { $queryFields = $selectFields; }
        $selectSql = "SELECT " . implode(', ', $queryFields) . " FROM $tableName";

        $filteredFields = array_filter(array_keys($fields), function($k) use ($autoCols) {
            return !in_array($k, $autoCols);
        });

        $insertSql = "INSERT INTO $tableName (" . implode(', ', $filteredFields) . ") VALUES (" . implode(', ', array_fill(0, count($filteredFields), '?')) . ")";
        $insertParam = implode('~', $filteredFields);

        $setClause = implode(' = ?, ', $filteredFields) . ' = ?';
        $updateSql = "UPDATE $tableName SET $setClause WHERE id = ?";
        $updateParam = implode('~', $filteredFields) . "~edit_id";

        $varAutoCols = var_export($autoCols, true);
        $generatedCode = "<?php
require_once __DIR__.'/../template/headerAuth.php';
require_once BASE_DIR.'class/Gen.php';

\$conn = new Conn();
\$moduleID = " . (int)$moduleID . ";
\$autoCols = $varAutoCols;
\$gen = new Gen();
\$gen->sql = '';
\$gen->param = '';
\$permissions = " . var_export($permissions, true) . ";
\$customActions = " . var_export($customActions, true) . ";
\$isEditing = isset(\$_GET['edit']) && isset(\$permissions['allow_edit']);
\$isAdding = isset(\$_GET['add']) && isset(\$permissions['allow_add']);

// RBAC Check: Ensure user has a role that is mapped to this module
if (empty(\$_SESSION['ROLE_IDS'])) {
    \$_SESSION['NOTIFYMESSAGE'] = \"Access Denied: No roles assigned.\";
    \$_SESSION['NOTIFYCLASS'] = \"notification is-danger\";
    header(\"Location: \" . BASE_URL . \"index.php\");
    exit();
}
\$roleIds = \$_SESSION['ROLE_IDS'];
\$placeholders = implode(',', array_fill(0, count(\$roleIds), '?'));
\$authCheck = \$conn->SQLFetch(\"SELECT count(1) FROM role_modules WHERE module_id = ? AND role_id IN (\$placeholders)\", \$moduleID . \"~\" . implode('~', \$roleIds));

if (\$authCheck == 0) {
    \$_SESSION['NOTIFYMESSAGE'] = \"Unauthorized access to this module.\";
    \$_SESSION['NOTIFYCLASS'] = \"notification is-danger\";
    header(\"Location: \" . BASE_URL . \"index.php\");
    exit();
}

\$genFields = " . var_export($fields, true) . ";
foreach (\$genFields as \$key => \$val) {
    if (in_array(\$key, \$autoCols)) continue;
    if (\$val['type'] == 'file') {
        \$gen->formEnctype = 'multipart/form-data';
    }
    \$gen->fields[\$key] = [
        'type' => \$val['type'],
        'label' => \$val['label'],
        'attributes' => ['required' => 'required']
    ];
    \$options_def = \$val['options'] ?? '';
    if (!empty(\$options_def)) {
        if (\$val['type'] == 'select') {
            if (stripos(\$options_def, 'select') === 0) {
                // Execute SQL query for options
                \$optData = \$conn->SQLCursor(\$options_def);
                \$optArr = [];
                if (is_array(\$optData)) {
                    foreach(\$optData as \$row) {
                        \$keys = array_keys(\$row);
                        if (count(\$keys) >= 2) {
                            \$optArr[\$row[\$keys[0]]] = \$row[\$keys[1]];
                        } else {
                            \$optArr[\$row[\$keys[0]]] = \$row[\$keys[0]];
                        }
                    }
                }
                \$gen->fields[\$key]['options'] = \$optArr;
            } else {
                \$optArr = [];
                foreach(explode(',', \$options_def) as \$e) {
                    \$e = trim(\$e);
                    \$optArr[\$e] = \$e;
                }
                \$gen->fields[\$key]['options'] = \$optArr;
            }
        } else {
            \$def = \$options_def;
            if (\$def === '{USER}' || \$def === '{UPDATE_USER}') \$def = \$_SESSION['USER'] ?? '';
            elseif (\$def === '{DATE}' || \$def === '{UPDATE_DATE}') \$def = date('Y-m-d');
            elseif (\$def === '{DATETIME}' || \$def === '{UPDATE_DATETIME}') \$def = date('Y-m-d H:i:s');
            
            if (\$isAdding) {
                \$gen->fields[\$key]['attributes']['value'] = \$def;
                if (stripos(\$options_def, '{UPDATE_') !== false || stripos(\$options_def, '{USER}') !== false || stripos(\$options_def, '{DATE}') !== false) {
                    \$gen->fields[\$key]['attributes']['readonly'] = 'readonly';
                }
            } elseif (\$isEditing && stripos(\$options_def, '{UPDATE_') !== false) {
                \$gen->fields[\$key]['attributes']['value'] = \$def;
                \$gen->fields[\$key]['attributes']['readonly'] = 'readonly';
            }
        }
    }
}

echo \"<div class='level'>\";
echo \"<div class='level-left'><h1 class='title'>" . addslashes($moduleName) . "</h1></div>\";
if (isset(\$permissions['allow_add']) && !\$isEditing && !\$isAdding) {
    echo \"<div class='level-right'><a href='\" . basename(__FILE__) . \"?add' class='button is-primary'>Add New</a></div>\";
}
echo \"</div>\";

if (\$isAdding) {
    \$gen->sql = \"" . addslashes($insertSql) . "\";
    \$gen->param = \"" . addslashes($insertParam) . "\";
    \$gen->formTitle = \"Manage " . addslashes($moduleName) . "\";
    if (isset(\$_POST['submit'])) {
        foreach (\$genFields as \$key => \$val) {
            if (\$val['type'] == 'file' && isset(\$_FILES[\$key]) && \$_FILES[\$key]['error'] == UPLOAD_ERR_OK) {
                \$uploadDir = __DIR__ . '/../uploads/';
                if (!is_dir(\$uploadDir)) mkdir(\$uploadDir, 0755, true);
                \$filename = time() . '_' . basename(\$_FILES[\$key]['name']);
                if (move_uploaded_file(\$_FILES[\$key]['tmp_name'], \$uploadDir . \$filename)) {
                    \$_POST[\$key] = 'uploads/' . \$filename;
                }
            }
        }
    }
    echo \$gen->form();
    echo \"<div class='mt-4'><a href='\" . basename(__FILE__) . \"' class='button is-light'>Back to List</a></div>\";
} elseif (\$isEditing) {
    \$editId = \$_GET['edit'];
    \$currentData = \$conn->SQLFetchRow(\"SELECT * FROM " . addslashes($tableName) . " WHERE id = ?\", \$editId);
    
    \$gen->sql = \"" . addslashes($updateSql) . "\";
    \$gen->param = \"" . addslashes($updateParam) . "\";
    \$gen->fields['edit_id'] = [
        'type' => 'text',
        'label' => 'ID (Hidden)',
        'attributes' => ['value' => \$editId, 'readonly' => 'readonly', 'style' => 'display:none;']
    ];

    foreach (\$genFields as \$key => \$val) {
        if (in_array(\$key, \$autoCols)) continue;
        if (!isset(\$gen->fields[\$key]['attributes']['readonly']) && isset(\$currentData[\$key])) {
            \$gen->fields[\$key]['attributes']['value'] = \$currentData[\$key];
        }
    }
    
    if (isset(\$_POST['submit'])) {
        foreach (\$genFields as \$key => \$val) {
            if (\$val['type'] == 'file') {
                if (isset(\$_FILES[\$key]) && \$_FILES[\$key]['error'] == UPLOAD_ERR_OK) {
                    \$uploadDir = __DIR__ . '/../uploads/';
                    if (!is_dir(\$uploadDir)) mkdir(\$uploadDir, 0755, true);
                    \$filename = time() . '_' . basename(\$_FILES[\$key]['name']);
                    if (move_uploaded_file(\$_FILES[\$key]['tmp_name'], \$uploadDir . \$filename)) {
                        \$_POST[\$key] = 'uploads/' . \$filename;
                    }
                } else {
                    \$_POST[\$key] = \$currentData[\$key] ?? '';
                }
            }
        }
    }
    
    \$gen->formTitle = \"Edit " . addslashes($moduleName) . "\";
    echo \$gen->form();
    echo \"<div class='mt-4'><a href='\" . basename(__FILE__) . \"' class='button is-light'>Cancel & Back to List</a></div>\";
} else {
    \$selectSql = \"" . addslashes($selectSql) . "\";
    \$tableData = \$conn->SQLCursor(\$selectSql);
    \$gen->sql = \$selectSql;
    \$gen->fields = [];
    \$selectFields = " . var_export($selectFields, true) . ";
    foreach (\$selectFields as \$sf) {
        \$gen->fields[\$sf] = \$genFields[\$sf]['label'] ?? \$sf;
    }

    if (\$tableData === 0) \$tableData = [];
?>
    <table class=\"table is-fullwidth is-striped is-hoverable\">
        <thead>
            <tr>
                <?php foreach (\$gen->fields as \$f): ?><th><?= is_array(\$f) ? \$f['label'] : \$f ?></th><?php endforeach; ?>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach (\$tableData as \$row): ?>
                <tr>
                    <?php foreach (\$selectFields as \$sf): ?>
                        <td><?= \$row[\$sf] ?></td>
                    <?php endforeach; ?>
                    <td>
                        <div class=\"buttons\">
                            <?php if (isset(\$permissions['allow_edit'])): ?>
                                <a href=\"<?= basename(__FILE__) ?>?edit=<?= \$row['id'] ?>\" class=\"button is-small is-link is-light\">Edit</a>
                            <?php endif; ?>
                            
                            <?php if (isset(\$permissions['allow_delete'])): ?>
                                <a href=\"<?= basename(__FILE__) ?>?delete=<?= \$row['id'] ?>\" class=\"button is-small is-danger is-light\" onclick=\"return confirm('Delete record?')\">Del</a>
                            <?php endif; ?>

                            <?php foreach (\$customActions as \$action): 
                                if (empty(\$action['label'])) continue;
                                \$trigger = \$action['trigger'];
                                foreach (\$row as \$col_name => \$col_val) {
                                    \$trigger = str_replace('{'.\$col_name.'}', \$col_val, \$trigger);
                                }
                                if (\$action['type'] == 'js'):
                            ?>
                                <button class=\"button is-small is-info is-light\" onclick=\"<?= htmlspecialchars(\$trigger) ?>\"><?= \$action['label'] ?></button>
                            <?php else: ?>
                                <a href=\"<?= htmlspecialchars(\$trigger) ?>\" class=\"button is-small is-info is-light\"><?= \$action['label'] ?></a>
                            <?php endif; endforeach; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php
}

require_once __DIR__.'/../template/footer.php';
";

        $filename = "fast_mod_" . $moduleID . ".php";
        if (file_put_contents(BASE_DIR . "view/" . $filename, $generatedCode)) {
            $this->conn->ExecSQL("UPDATE crud_modules SET is_compiled = 1, use_fast_page = 1 WHERE id = ?", $moduleID);
            return ["status" => true, "filename" => $filename];
        } else {
            return ["status" => false, "message" => "Failed to write file."];
        }
    }
}
