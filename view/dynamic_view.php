<?php
include __DIR__."/../template/headerAuth.php";
include BASE_DIR."class/Gen.php";

$conn = new Conn();

$moduleID = $_GET['id'] ?? null;
if (!$moduleID) {
    header("Location: crud_list.php");
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
$gen->formTitle = "Manage " . $module['module_name'];
$insertFields = array_keys($fields);
$gen->sql = "INSERT INTO $tableName (" . implode(', ', $insertFields) . ") VALUES (" . implode(', ', array_fill(0, count($insertFields), '?')) . ")";
$gen->param = implode('~', $insertFields);

// Map field types for Gen::form()
foreach ($fields as $key => $val) {
    $gen->fields[$key] = [
        "type" => $val['type'],
        "label" => $val['label'],
        "attributes" => ["required" => "required"]
    ];
}

$isEditing = isset($_GET['edit']) && isset($permissions['allow_edit']);
$isAdding = isset($_GET['add']) && isset($permissions['allow_add']);

echo "<div class='level'>";
echo "<div class='level-left'><h1 class='title'>{$module['module_name']}</h1></div>";
if (isset($permissions['allow_add']) && !$isEditing && !$isAdding) {
    echo "<div class='level-right'><a href='dynamic_view.php?id=$moduleID&add' class='button is-primary'>Add New</a></div>";
}
echo "</div>";

if ($isAdding) {
    echo $gen->form();
} elseif ($isEditing) {
    // 3. Prepare Form Config for Updating
    $editId = $_GET['edit'];
    
    // Fetch current data for the form
    $currentData = $conn->SQLFetchRow("SELECT * FROM $tableName WHERE id = ?", $editId);
    
    // Create the UPDATE query
    $updateFields = array_keys($fields);
    $setClause = implode(' = ?, ', $updateFields);
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
        if (isset($currentData[$key])) {
             $gen->fields[$key]['attributes']['value'] = $currentData[$key];
        }
    }
    
    $gen->formTitle = "Edit " . $module['module_name'];
    echo $gen->form();
    echo "<div class='mt-4'><a href='dynamic_view.php?id=$moduleID' class='button is-light'>Cancel Edit</a></div>";

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
                                $trigger = str_replace('{id}', $row['id'], $action['trigger']);
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
