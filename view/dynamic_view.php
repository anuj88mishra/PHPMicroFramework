<?php
include __DIR__."/../template/headerAuth.php";
include BASE_DIR."class/Gen.php";

$conn = new Conn();

$moduleID = $_GET['id'] ?? null;
if (!$moduleID) {
    header("Location: crud_list.php");
    exit();
}

// RBAC Check: Ensure user has a role that is mapped to this module
if (empty($_SESSION['ROLE_IDS'])) {
    $_SESSION['NOTIFYMESSAGE'] = "You do not have any roles assigned. Access denied.";
    $_SESSION['NOTIFYCLASS'] = "notification is-danger";
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$roleIds = $_SESSION['ROLE_IDS'];
$placeholders = implode(',', array_fill(0, count($roleIds), '?'));
$authCheck = $conn->SQLFetch("SELECT count(1) FROM role_modules WHERE module_id = ? AND role_id IN ($placeholders)", $moduleID . "~" . implode('~', $roleIds));

if ($authCheck == 0) {
    $_SESSION['NOTIFYMESSAGE'] = "Unauthorized: You do not have permission to access this module.";
    $_SESSION['NOTIFYCLASS'] = "notification is-danger";
    header("Location: " . BASE_URL . "index.php");
    exit();
}

// Fetch Module Configuration
$module = $conn->SQLFetchRow("SELECT * FROM crud_modules WHERE id = ?", $moduleID);
if (!$module) {
    echo "Module not found.";
    include __DIR__."/../template/footer.php";
    exit();
}

$configData = json_decode($module['config'], true);
$fields = $configData['fields'] ?? [];
$permissions = $configData['permissions'] ?? [];
$customActions = $configData['custom_actions'] ?? [];
$tableName = $module['table_name'];

$gen = new Gen();

// 1. Prepare Table Query
$selectFields = array_keys(array_filter($fields, function($f) { return isset($f['show_table']); }));
// We need the primary key for actions, assuming 'id' if not selected
if (!in_array('id', $selectFields)) { $queryFields = array_merge(['id'], $selectFields); } else { $queryFields = $selectFields; }

$selectSql = "SELECT " . implode(', ', $queryFields) . " FROM $tableName";
$gen->sql = $selectSql;
$gen->fields = [];
foreach ($selectFields as $sf) {
    $gen->fields[$sf] = $fields[$sf]['label'] ?? $sf;
}

// 2. Prepare Form Config (If adding/editing)
$colMetadata = $conn->getTableColumns($tableName);
$autoCols = [];
foreach ($colMetadata as $col) {
    // For MySQL: 'Extra' contains 'auto_increment'
    // For PostgreSQL: Type might be 'SERIAL' or Default might have 'nextval'
    if (isset($col['Extra']) && stripos($col['Extra'], 'auto_increment') !== false) {
        $autoCols[] = $col['Field'];
    } elseif (isset($col['Key']) && $col['Key'] == 'PRI' && stripos($col['Type'], 'int') !== false) {
        // Fallback for some drivers: If PRI and INT, usually we don't want to input it manually
        $autoCols[] = $col['Field'];
    }
}

$gen->formTitle = "Manage " . $module['module_name'];
$insertFields = array_filter(array_keys($fields), function($k) use ($autoCols) {
    return !in_array($k, $autoCols);
});

$gen->sql = "INSERT INTO $tableName (" . implode(', ', $insertFields) . ") VALUES (" . implode(', ', array_fill(0, count($insertFields), '?')) . ")";
$gen->param = implode('~', $insertFields);

// Map field types for Gen::form()
$isEditing = isset($_GET['edit']) && isset($permissions['allow_edit']);
$isAdding = isset($_GET['add']) && isset($permissions['allow_add']);

foreach ($fields as $key => $val) {
    if (in_array($key, $autoCols)) continue;
    
    if ($val['type'] == 'file') {
        $gen->formEnctype = "multipart/form-data";
    }

    $gen->fields[$key] = [
        "type" => $val['type'],
        "label" => $val['label'],
        "attributes" => ["required" => "required"]
    ];

    $options_def = $val['options'] ?? '';
    if (!empty($options_def)) {
        if ($val['type'] == 'select') {
            if (stripos($options_def, 'select') === 0) {
                // Execute SQL query for options
                $optData = $conn->SQLCursor($options_def);
                $optArr = [];
                if (is_array($optData)) {
                    foreach($optData as $row) {
                        $keys = array_keys($row);
                        if (count($keys) >= 2) {
                            $optArr[$row[$keys[0]]] = $row[$keys[1]];
                        } else {
                            $optArr[$row[$keys[0]]] = $row[$keys[0]];
                        }
                    }
                }
                $gen->fields[$key]['options'] = $optArr;
            } else {
                // CSV options
                $optArr = [];
                foreach(explode(',', $options_def) as $e) {
                    $e = trim($e);
                    $optArr[$e] = $e;
                }
                $gen->fields[$key]['options'] = $optArr;
            }
        } else {
            // Text defaults
            $def = $options_def;
            if ($def === '{USER}' || $def === '{UPDATE_USER}') $def = $_SESSION['USER'] ?? '';
            elseif ($def === '{DATE}' || $def === '{UPDATE_DATE}') $def = date('Y-m-d');
            elseif ($def === '{DATETIME}' || $def === '{UPDATE_DATETIME}') $def = date('Y-m-d H:i:s');
            
            if ($isAdding) {
                $gen->fields[$key]['attributes']['value'] = $def;
                if (stripos($options_def, '{UPDATE_') !== false || stripos($options_def, '{USER}') !== false || stripos($options_def, '{DATE}') !== false) {
                    $gen->fields[$key]['attributes']['readonly'] = 'readonly';
                }
            } elseif ($isEditing && stripos($options_def, '{UPDATE_') !== false) {
                // Force update value on edit
                $gen->fields[$key]['attributes']['value'] = $def;
                $gen->fields[$key]['attributes']['readonly'] = 'readonly';
            }
        }
    }
}

echo "<div class='level'>";
echo "<div class='level-left'><h1 class='title'>{$module['module_name']}</h1></div>";
if (isset($permissions['allow_add']) && !$isEditing && !$isAdding) {
    echo "<div class='level-right'><a href='dynamic_view.php?id=$moduleID&add' class='button is-primary'>Add New</a></div>";
}
echo "</div>";

if ($isAdding) {
    if (isset($_POST['submit'])) {
        foreach ($fields as $key => $val) {
            if ($val['type'] == 'file') {
                if (isset($_FILES[$key]) && $_FILES[$key]['error'] == UPLOAD_ERR_OK) {
                    $uploadDir = __DIR__ . "/../uploads/";
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                    $filename = time() . "_" . basename($_FILES[$key]['name']);
                    if (move_uploaded_file($_FILES[$key]['tmp_name'], $uploadDir . $filename)) {
                        $_POST[$key] = "uploads/" . $filename;
                    }
                }
            }
        }
    }
    echo $gen->form();
    echo "<div class='mt-4'><a href='dynamic_view.php?id=$moduleID' class='button is-light'>Back to List</a></div>";
} elseif ($isEditing) {
    // 3. Prepare Form Config for Updating
    $editId = $_GET['edit'];
    
    // Fetch current data for the form
    $currentData = $conn->SQLFetchRow("SELECT * FROM $tableName WHERE id = ?", $editId);
    
    // Create the UPDATE query
    $updateFields = $insertFields; // Use the same filtered fields as insertion
    $setClause = implode(' = ?, ', $updateFields) . ' = ?';
    $gen->sql = "UPDATE $tableName SET $setClause WHERE id = ?";
    
    // The param string tells Gen.php which $_POST fields to map to the ? placeholders
    $gen->param = implode('~', $updateFields) . "~edit_id";

    // Add hidden field for the ID so it is submitted with the form
    $gen->fields['edit_id'] = [
        "type" => "text",
        "label" => "ID (Hidden)",
        "attributes" => ["value" => $editId, "readonly" => "readonly", "style" => "display:none;"]
    ];

    // Pre-populate the form attributes with current data
    foreach ($fields as $key => $val) {
        if (in_array($key, $autoCols)) continue;
        if (!isset($gen->fields[$key]['attributes']['readonly'])) {
            if (isset($currentData[$key])) {
                 $gen->fields[$key]['attributes']['value'] = $currentData[$key];
            }
        }
    }
    
    // File upload intercept for editing
    if (isset($_POST['submit'])) {
        foreach ($fields as $key => $val) {
            if ($val['type'] == 'file') {
                if (isset($_FILES[$key]) && $_FILES[$key]['error'] == UPLOAD_ERR_OK) {
                    $uploadDir = __DIR__ . "/../uploads/";
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                    $filename = time() . "_" . basename($_FILES[$key]['name']);
                    if (move_uploaded_file($_FILES[$key]['tmp_name'], $uploadDir . $filename)) {
                        $_POST[$key] = "uploads/" . $filename;
                    }
                } else {
                    // Retain old file path if no new file is uploaded
                    $_POST[$key] = $currentData[$key] ?? '';
                }
            }
        }
    }
    
    $gen->formTitle = "Edit " . $module['module_name'];
    echo $gen->form();
    echo "<div class='mt-4'><a href='dynamic_view.php?id=$moduleID' class='button is-light'>Cancel & Back to List</a></div>";

} else {
    // Generate custom table to include actions
    $tableData = $conn->SQLCursor($selectSql);
    if ($tableData === 0) $tableData = []; // Fallback if query fails
    ?>
    <table class="table is-fullwidth is-striped is-hoverable">
        <thead>
            <tr>
                <?php foreach ($gen->fields as $f): ?><th><?= is_array($f) ? $f['label'] : $f ?></th><?php endforeach; ?>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tableData as $row): ?>
                <tr>
                    <?php foreach ($selectFields as $sf): ?>
                        <td><?= $row[$sf] ?></td>
                    <?php endforeach; ?>
                    <td>
                        <div class="buttons">
                            <?php if (isset($permissions['allow_edit'])): ?>
                                <a href="dynamic_view.php?id=<?= $moduleID ?>&edit=<?= $row['id'] ?>" class="button is-small is-link is-light">Edit</a>
                            <?php endif; ?>
                            
                            <?php if (isset($permissions['allow_delete'])): ?>
                                <a href="dynamic_view.php?id=<?= $moduleID ?>&delete=<?= $row['id'] ?>" class="button is-small is-danger is-light" onclick="return confirm('Delete record?')">Del</a>
                            <?php endif; ?>

                            <?php foreach ($customActions as $action): 
                                if (empty($action['label'])) continue;
                                $trigger = $action['trigger'];
                                foreach ($row as $col_name => $col_val) {
                                    $trigger = str_replace('{'.$col_name.'}', $col_val, $trigger);
                                }
                                if ($action['type'] == 'js'):
                            ?>
                                <button class="button is-small is-info is-light" onclick="<?= $trigger ?>"><?= $action['label'] ?></button>
                            <?php else: ?>
                                <a href="<?= $trigger ?>" class="button is-small is-info is-light"><?= $action['label'] ?></a>
                            <?php endif; endforeach; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php
}

include __DIR__."/../template/footer.php";
?>
