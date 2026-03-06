<?php
require_once __DIR__.'/../template/headerAuth.php';
require_once BASE_DIR.'class/Gen.php';

$conn = new Conn();
$moduleID = 2;
$gen = new Gen();
$gen->sql = '';
$gen->param = '';
$permissions = array (
  'allow_add' => 'on',
  'allow_edit' => 'on',
  'allow_delete' => 'on',
);
$customActions = array (
  0 => 
  array (
    'label' => 'Alert',
    'trigger' => 'alert(\'{id}\')',
    'type' => 'js',
  ),
);
$isEditing = isset($_GET['edit']) && isset($permissions['allow_edit']);
$isAdding = isset($_GET['add']) && isset($permissions['allow_add']);

$genFields = array (
  'id' => 
  array (
    'label' => 'id',
    'type' => 'text',
    'show_table' => 'on',
  ),
  'usr_cd' => 
  array (
    'label' => 'usr_cd',
    'type' => 'text',
    'show_table' => 'on',
  ),
  'usr_name' => 
  array (
    'label' => 'usr_name',
    'type' => 'text',
    'show_table' => 'on',
  ),
  'user_alias' => 
  array (
    'label' => 'user_alias',
    'type' => 'text',
    'show_table' => 'on',
  ),
  'usr_passwd' => 
  array (
    'label' => 'usr_passwd',
    'type' => 'text',
    'show_table' => 'on',
  ),
  'rec_ind' => 
  array (
    'label' => 'rec_ind',
    'type' => 'text',
    'show_table' => 'on',
  ),
  'created_at' => 
  array (
    'label' => 'created_at',
    'type' => 'text',
    'show_table' => 'on',
  ),
);
foreach ($genFields as $key => $val) {
    if ($val['type'] == 'file') {
        $gen->formEnctype = 'multipart/form-data';
    }
    $gen->fields[$key] = [
        'type' => $val['type'],
        'label' => $val['label'],
        'attributes' => ['required' => 'required']
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
                $optArr = [];
                foreach(explode(',', $options_def) as $e) {
                    $e = trim($e);
                    $optArr[$e] = $e;
                }
                $gen->fields[$key]['options'] = $optArr;
            }
        } else {
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
                $gen->fields[$key]['attributes']['value'] = $def;
                $gen->fields[$key]['attributes']['readonly'] = 'readonly';
            }
        }
    }
}

echo "<div class='level'>";
echo "<div class='level-left'><h1 class='title'>Users</h1></div>";
if (isset($permissions['allow_add']) && !$isEditing && !$isAdding) {
    echo "<div class='level-right'><a href='" . basename(__FILE__) . "?add' class='button is-primary'>Add New</a></div>";
}
echo "</div>";

if ($isAdding) {
    $gen->sql = "INSERT INTO users (id, usr_cd, usr_name, user_alias, usr_passwd, rec_ind, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $gen->param = "id~usr_cd~usr_name~user_alias~usr_passwd~rec_ind~created_at";
    $gen->formTitle = "Manage Users";
    if (isset($_POST['submit'])) {
        foreach ($genFields as $key => $val) {
            if ($val['type'] == 'file' && isset($_FILES[$key]) && $_FILES[$key]['error'] == UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $filename = time() . '_' . basename($_FILES[$key]['name']);
                if (move_uploaded_file($_FILES[$key]['tmp_name'], $uploadDir . $filename)) {
                    $_POST[$key] = 'uploads/' . $filename;
                }
            }
        }
    }
    echo $gen->form();
} elseif ($isEditing) {
    $editId = $_GET['edit'];
    $currentData = $conn->SQLFetchRow("SELECT * FROM users WHERE id = ?", $editId);
    
    $gen->sql = "UPDATE users SET id = ?, usr_cd = ?, usr_name = ?, user_alias = ?, usr_passwd = ?, rec_ind = ?, created_at WHERE id = ?";
    $gen->param = "id~usr_cd~usr_name~user_alias~usr_passwd~rec_ind~created_at~edit_id";
    $gen->fields['edit_id'] = [
        'type' => 'text',
        'label' => 'ID (Hidden)',
        'attributes' => ['value' => $editId, 'readonly' => 'readonly', 'style' => 'display:none;']
    ];

    foreach ($genFields as $key => $val) {
        if (!isset($gen->fields[$key]['attributes']['readonly']) && isset($currentData[$key])) {
            $gen->fields[$key]['attributes']['value'] = $currentData[$key];
        }
    }
    
    if (isset($_POST['submit'])) {
        foreach ($genFields as $key => $val) {
            if ($val['type'] == 'file') {
                if (isset($_FILES[$key]) && $_FILES[$key]['error'] == UPLOAD_ERR_OK) {
                    $uploadDir = __DIR__ . '/../uploads/';
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                    $filename = time() . '_' . basename($_FILES[$key]['name']);
                    if (move_uploaded_file($_FILES[$key]['tmp_name'], $uploadDir . $filename)) {
                        $_POST[$key] = 'uploads/' . $filename;
                    }
                } else {
                    $_POST[$key] = $currentData[$key] ?? '';
                }
            }
        }
    }
    
    $gen->formTitle = "Edit Users";
    echo $gen->form();
    echo "<div class='mt-4'><a href='" . basename(__FILE__) . "' class='button is-light'>Cancel Edit</a></div>";
} else {
    $selectSql = "SELECT id, usr_cd, usr_name, user_alias, usr_passwd, rec_ind, created_at FROM users";
    $tableData = $conn->SQLCursor($selectSql);
    $gen->sql = $selectSql;
    $gen->fields = [];
    $selectFields = array (
  0 => 'id',
  1 => 'usr_cd',
  2 => 'usr_name',
  3 => 'user_alias',
  4 => 'usr_passwd',
  5 => 'rec_ind',
  6 => 'created_at',
);
    foreach ($selectFields as $sf) {
        $gen->fields[$sf] = $genFields[$sf]['label'] ?? $sf;
    }

    if ($tableData === 0) $tableData = [];
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
                                <a href="<?= basename(__FILE__) ?>?edit=<?= $row['id'] ?>" class="button is-small is-link is-light">Edit</a>
                            <?php endif; ?>
                            
                            <?php if (isset($permissions['allow_delete'])): ?>
                                <a href="<?= basename(__FILE__) ?>?delete=<?= $row['id'] ?>" class="button is-small is-danger is-light" onclick="return confirm('Delete record?')">Del</a>
                            <?php endif; ?>

                            <?php foreach ($customActions as $action): 
                                if (empty($action['label'])) continue;
                                $trigger = $action['trigger'];
                                foreach ($row as $col_name => $col_val) {
                                    $trigger = str_replace('{'.$col_name.'}', $col_val, $trigger);
                                }
                                if ($action['type'] == 'js'):
                            ?>
                                <button class="button is-small is-info is-light" onclick="<?= htmlspecialchars($trigger) ?>"><?= $action['label'] ?></button>
                            <?php else: ?>
                                <a href="<?= htmlspecialchars($trigger) ?>" class="button is-small is-info is-light"><?= $action['label'] ?></a>
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
