<?php
include __DIR__."/../template/headerAuth.php";
include BASE_DIR."class/Gen.php";

$conn = new Conn();

$editID = $_GET['id'] ?? null;
$moduleData = null;
if ($editID) {
    $moduleData = $conn->SQLFetchRow("SELECT * FROM crud_modules WHERE id = ?", $editID);
    if ($moduleData) {
        $tableName = $moduleData['table_name'];
        $moduleConfig = json_decode($moduleData['config'], true);
        $step = 2; // Jump straight to config
    }
}

$step = $_GET['step'] ?? ($step ?? 1);
$tableName = $_GET['table'] ?? ($tableName ?? "");

// Handle Step 3: Saving the Module
if (isset($_POST['save_module'])) {
    $module_name = $_POST['module_name'];
    $config = [
        'fields' => $_POST['fields'],
        'permissions' => $_POST['permissions'] ?? [],
        'custom_actions' => $_POST['custom_actions'] ?? []
    ];
    $config_json = json_encode($config);
    if ($editID) {
        $conn->ExecSQL("UPDATE crud_modules SET module_name = ?, table_name = ?, config = ?, is_compiled = 0 WHERE id = ?", $module_name."~".$tableName."~".$config_json."~".$editID);
        $_SESSION['NOTIFYMESSAGE'] = "CRUD Module '$module_name' updated successfully! (Please re-compile if using fast pages)";
    } else {
        $conn->ExecSQL("INSERT INTO crud_modules (module_name, table_name, config) VALUES (?, ?, ?)", $module_name."~".$tableName."~".$config_json);
        $newModuleID = $conn->getLastInsertId();
        
        // Auto-assign Administrator role to new module
        $adminRole = $conn->SQLFetch("SELECT id FROM roles WHERE role_name = 'Administrator'");
        if ($adminRole) {
            $conn->ExecSQL("INSERT INTO role_modules (role_id, module_id) VALUES (?, ?)", "$adminRole~$newModuleID");
        }
        
        $_SESSION['NOTIFYMESSAGE'] = "CRUD Module '$module_name' created successfully!";
    }
    $_SESSION['NOTIFYCLASS'] = "notification is-success";
    header("Location: crud_list.php");
    exit();
}

?>

<div class="level">
    <div class="level-left"><h1 class="title">CRUD Builder Wizards</h1></div>
</div>

<?php if ($step == 1): ?>
    <!-- SELECT TABLE -->
    <div class="box">
        <h2 class="subtitle">Step 1: Choose a database table</h2>
        <div class="buttons">
            <?php 
            $tables = $conn->getTables();
            foreach ($tables as $t):
                $t_name = $t[array_key_first($t)];
            ?>
                <a href="crud_builder.php?step=2&table=<?= $t_name ?>" class="button is-link is-light is-outlined"><?= $t_name ?></a>
            <?php endforeach; ?>
        </div>
    </div>

<?php elseif ($step == 2): ?>
    <!-- CONFIGURE FIELDS -->
    <div class="box">
        <h2 class="subtitle">Step 2: Configure fields for table <strong><?= $tableName ?></strong></h2>
        <form method="POST">
            <div class="field">
                <label class="label">Module Public Name (Internal alias)</label>
                <div class="control"><input class="input" type="text" name="module_name" placeholder="User Management" value="<?= $moduleData['module_name'] ?? '' ?>" required></div>
            </div>

            <div class="field">
                <label class="label">Permissions</label>
                <div class="control">
                    <label class="checkbox mr-3"><input type="checkbox" name="permissions[allow_add]" <?= (!isset($moduleConfig) || isset($moduleConfig['permissions']['allow_add'])) ? 'checked' : '' ?>> Allow Add</label>
                    <label class="checkbox mr-3"><input type="checkbox" name="permissions[allow_edit]" <?= (!isset($moduleConfig) || isset($moduleConfig['permissions']['allow_edit'])) ? 'checked' : '' ?>> Allow Edit</label>
                    <label class="checkbox"><input type="checkbox" name="permissions[allow_delete]" <?= (!isset($moduleConfig) || isset($moduleConfig['permissions']['allow_delete'])) ? 'checked' : '' ?>> Allow Delete</label>
                </div>
            </div>

            <hr>
            <h3 class="subtitle is-5">Field Configuration</h3>
            <table class="table is-fullwidth is-striped">
                <thead>
                    <tr><th>Column</th><th>Display Label</th><th>Input Type</th><th>Default/Options</th><th>Show in Table?</th></tr>
                </thead>
                <tbody>
                    <?php 
                    $columns = $conn->getTableColumns($tableName);
                    foreach ($columns as $c):
                        $cName = $c['Field'];
                        $fCfg = $moduleConfig['fields'][$cName] ?? null;
                    ?>
                        <tr>
                            <td>
                                <?= $cName ?>
                                <?php if (isset($c['Extra']) && stripos($c['Extra'], 'auto_increment') !== false): ?>
                                    <br><span class="tag is-info is-light is-small">Auto-Inc</span>
                                <?php elseif (isset($c['Key']) && $c['Key'] == 'PRI'): ?>
                                    <br><span class="tag is-info is-light is-small">Primary Key</span>
                                <?php endif; ?>
                            </td>
                            <td><input class="input is-small" type="text" name="fields[<?= $cName ?>][label]" value="<?= $fCfg['label'] ?? $cName ?>"></td>
                            <td>
                                <div class="select is-small">
                                    <select name="fields[<?= $cName ?>][type]">
                                        <?php 
                                        $types = ["text", "number", "date", "password", "textarea", "select", "file"];
                                        foreach($types as $t): 
                                            $sel = (isset($fCfg['type']) && $fCfg['type'] == $t) ? 'selected' : '';
                                        ?>
                                            <option value="<?= $t ?>" <?= $sel ?>><?= ucfirst($t) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </td>
                            <td><input class="input is-small" type="text" name="fields[<?= $cName ?>][options]" value="<?= $fCfg['options'] ?? '' ?>" placeholder="e.g. {USER}, {DATETIME}, or A,B,C" title="For text: default value. For select: csv options or SQL query"></td>
                            <td><label class="checkbox"><input type="checkbox" name="fields[<?= $cName ?>][show_table]" <?= (!isset($fCfg) || isset($fCfg['show_table'])) ? 'checked' : '' ?>> Yes</label></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <hr>
            <h3 class="subtitle is-5">Custom Row Actions (Optional)</h3>
            <p class="is-size-7 mb-2">Actions can trigger JS (e.g., <code>alert('{id}')</code>) or point to PHP scripts (e.g., <code>process.php?id={id}&status={status}</code>). You can use any column name inside curly braces <code>{column_name}</code> as a placeholder.</p>
            <div id="custom-actions-container">
                <?php 
                $cActions = $moduleConfig['custom_actions'] ?? [[]]; 
                foreach($cActions as $idx => $act):
                ?>
                <div class="field is-grouped <?= $idx > 0 ? 'mt-2' : '' ?>">
                    <div class="control is-expanded"><input class="input is-small" type="text" name="custom_actions[<?= $idx ?>][label]" value="<?= $act['label'] ?? '' ?>" placeholder="Action Label (e.g., Print)"></div>
                    <div class="control is-expanded"><input class="input is-small" type="text" name="custom_actions[<?= $idx ?>][trigger]" value="<?= $act['trigger'] ?? '' ?>" placeholder="JS or URL (e.g., print.php?id={id})"></div>
                    <div class="control">
                        <div class="select is-small">
                            <select name="custom_actions[<?= $idx ?>][type]">
                                <option value="url" <?= (isset($act['type']) && $act['type'] == 'url') ? 'selected' : '' ?>>URL/Link</option>
                                <option value="js" <?= (isset($act['type']) && $act['type'] == 'js') ? 'selected' : '' ?>>JavaScript</option>
                            </select>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="button is-small is-info is-light mt-2" onclick="addCustomAction()">+ Add Another Action</button>

            <script>
            let actionCount = <?= count($cActions) ?>;
            function addCustomAction() {
                const container = document.getElementById('custom-actions-container');
                const div = document.createElement('div');
                div.className = 'field is-grouped mt-2';
                div.innerHTML = `
                    <div class="control is-expanded"><input class="input is-small" type="text" name="custom_actions[${actionCount}][label]" placeholder="Label"></div>
                    <div class="control is-expanded"><input class="input is-small" type="text" name="custom_actions[${actionCount}][trigger]" placeholder="Trigger"></div>
                    <div class="control">
                        <div class="select is-small">
                            <select name="custom_actions[${actionCount}][type]">
                                <option value="url">URL/Link</option>
                                <option value="js">JavaScript</option>
                            </select>
                        </div>
                    </div>
                `;
                container.appendChild(div);
                actionCount++;
            }
            </script>

            <div class="buttons mt-6">
                <button type="submit" name="save_module" class="button is-primary is-fullwidth"><?= $editID ? 'Update Module Definition' : 'Save and Generate CRUD Module' ?></button>
                <a href="<?= $editID ? 'crud_list.php' : 'crud_builder.php?step=1' ?>" class="button is-white is-fullwidth mt-2">Back</a>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php include __DIR__."/../template/footer.php"; ?>
