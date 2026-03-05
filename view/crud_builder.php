<?php
include __DIR__."/../template/headerAuth.php";
include BASE_DIR."class/Gen.php";

$conn = new Conn();

$step = $_GET['step'] ?? 1;
$tableName = $_GET['table'] ?? "";

// Handle Step 3: Saving the Module
if (isset($_POST['save_module'])) {
    $module_name = $_POST['module_name'];
    $config = [
        'fields' => $_POST['fields'],
        'permissions' => $_POST['permissions'] ?? [],
        'custom_actions' => $_POST['custom_actions'] ?? []
    ];
    $config_json = json_encode($config);
    $conn->ExecSQL("INSERT INTO crud_modules (module_name, table_name, config) VALUES (?, ?, ?)", $module_name."~".$tableName."~".$config_json);
    $_SESSION['NOTIFYMESSAGE'] = "CRUD Module '$module_name' created successfully!";
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
        <div class="list is-hoverable">
            <?php 
            $tables = $conn->getTables();
            foreach ($tables as $t):
                $t_name = $t[array_key_first($t)];
            ?>
                <a href="crud_builder.php?step=2&table=<?= $t_name ?>" class="list-item"><?= $t_name ?></a>
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
                <div class="control"><input class="input" type="text" name="module_name" placeholder="User Management" required></div>
            </div>

            <div class="field">
                <label class="label">Permissions</label>
                <div class="control">
                    <label class="checkbox mr-3"><input type="checkbox" name="permissions[allow_add]" checked> Allow Add</label>
                    <label class="checkbox mr-3"><input type="checkbox" name="permissions[allow_edit]" checked> Allow Edit</label>
                    <label class="checkbox"><input type="checkbox" name="permissions[allow_delete]" checked> Allow Delete</label>
                </div>
            </div>

            <hr>
            <h3 class="subtitle is-5">Field Configuration</h3>
            <table class="table is-fullwidth is-striped">
                <thead>
                    <tr><th>Column</th><th>Display Label</th><th>Input Type</th><th>Show in Table?</th></tr>
                </thead>
                <tbody>
                    <?php 
                    $columns = $conn->getTableColumns($tableName);
                    foreach ($columns as $c):
                    ?>
                        <tr>
                            <td><?= $c['Field'] ?></td>
                            <td><input class="input is-small" type="text" name="fields[<?= $c['Field'] ?>][label]" value="<?= $c['Field'] ?>"></td>
                            <td>
                                <div class="select is-small">
                                    <select name="fields[<?= $c['Field'] ?>][type]">
                                        <option value="text">Text</option>
                                        <option value="number">Number</option>
                                        <option value="date">Date</option>
                                        <option value="password">Password</option>
                                        <option value="textarea">Textarea</option>
                                        <option value="select">Select</option>
                                    </select>
                                </div>
                            </td>
                            <td><label class="checkbox"><input type="checkbox" name="fields[<?= $c['Field'] ?>][show_table]" checked> Yes</label></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <hr>
            <h3 class="subtitle is-5">Custom Row Actions (Optional)</h3>
            <p class="is-size-7 mb-2">Actions can trigger JS (e.g., <code>alert('{id}')</code>) or point to PHP scripts (e.g., <code>process.php?id={id}</code>). Use <code>{id}</code> as a placeholder for the row's primary key.</p>
            <div id="custom-actions-container">
                <div class="field is-grouped">
                    <div class="control is-expanded"><input class="input is-small" type="text" name="custom_actions[0][label]" placeholder="Action Label (e.g., Print)"></div>
                    <div class="control is-expanded"><input class="input is-small" type="text" name="custom_actions[0][trigger]" placeholder="JS or URL (e.g., print.php?id={id})"></div>
                    <div class="control">
                        <div class="select is-small">
                            <select name="custom_actions[0][type]">
                                <option value="url">URL/Link</option>
                                <option value="js">JavaScript</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <button type="button" class="button is-small is-info is-light mt-2" onclick="addCustomAction()">+ Add Another Action</button>

            <script>
            let actionCount = 1;
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
                <button type="submit" name="save_module" class="button is-primary is-fullwidth">Save and Generate CRUD Module</button>
                <a href="crud_builder.php?step=1" class="button is-white is-fullwidth mt-2">Back</a>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php include __DIR__."/../template/footer.php"; ?>
