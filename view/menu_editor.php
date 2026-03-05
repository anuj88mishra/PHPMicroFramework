<?php
include __DIR__."/../template/headerAuth.php";
include BASE_DIR."class/Gen.php";

$gen = new Gen();
$conn = new Conn();

// Handle Form Submission for adding menu items
if (isset($_POST['label'])) {
    $parent = !empty($_POST['parent_id']) ? $_POST['parent_id'] : "NULL";
    if (isset($_GET['edit'])) {
        $conn->ExecSQL("UPDATE menus SET label = ?, link = ?, parent_id = ?, sort_order = ? WHERE id = ?", $_POST['label']."~".$_POST['link']."~".$parent."~".$_POST['sort_order']."~".$_GET['edit']);
        $_SESSION['NOTIFYMESSAGE'] = "Menu Item Updated";
    } else {
        $conn->ExecSQL("INSERT INTO menus (label, link, parent_id, sort_order) VALUES (?, ?, ?, ?)", $_POST['label']."~".$_POST['link']."~".$parent."~".$_POST['sort_order']);
        $_SESSION['NOTIFYMESSAGE'] = "Menu Item Added";
    }
    $_SESSION['NOTIFYCLASS'] = "notification is-success";
    header("Location: menu_editor.php");
    exit();
}

$editData = null;
if (isset($_GET['edit'])) {
    $editData = $conn->SQLFetchRow("SELECT * FROM menus WHERE id = ?", $_GET['edit']);
}

// Fetch Data for List
$data = $conn->SQLCursor("SELECT m.id, m.label, m.link, COALESCE(p.label, 'Root') as parent, m.sort_order FROM menus m LEFT JOIN menus p ON m.parent_id = p.id ORDER BY m.sort_order, m.label");

?>

<div class="level">
    <div class="level-left"><h1 class="title">Menu Editor</h1></div>
</div>

<div class="columns">
    <div class="column is-4">
        <div class="card">
            <header class="card-header"><p class="card-header-title"><?= $editData ? 'Edit' : 'Add' ?> Menu Item</p></header>
            <div class="card-content">
                <form method="POST">
                    <div class="field">
                        <label class="label">Label</label>
                        <div class="control"><input class="input" type="text" name="label" value="<?= $editData['label'] ?? '' ?>" required></div>
                    </div>
                    <div class="field">
                        <label class="label">Link (Relative to BASE_URL)</label>
                        <div class="control"><input class="input" type="text" name="link" value="<?= $editData['link'] ?? '' ?>" placeholder="view/list.php"></div>
                    </div>
                    <div class="field">
                        <label class="label">Parent Item</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select name="parent_id">
                                    <option value="">None (Top Level)</option>
                                    <?php 
                                    $parents = $conn->SQLCursor("SELECT id, label FROM menus WHERE parent_id IS NULL");
                                    foreach ($parents as $p) {
                                        $sel = (isset($editData['parent_id']) && $editData['parent_id'] == $p['id']) ? 'selected' : '';
                                        echo "<option value='{$p['id']}' $sel>{$p['label']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">Sort Order</label>
                        <dir class="control"><input class="input" type="number" name="sort_order" value="<?= $editData['sort_order'] ?? '0' ?>"></dir>
                    </div>
                    <button type="submit" class="button is-primary is-fullwidth"><?= $editData ? 'Update' : 'Save' ?></button>
                    <?php if($editData): ?>
                        <a href="menu_editor.php" class="button is-light is-fullwidth mt-2">Cancel</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    <div class="column is-8">
        <div class="table-container">
            <table class="table is-fullwidth is-striped is-hoverable">
                <thead>
                    <tr><th>Order</th><th>Label</th><th>Link</th><th>Parent</th><th>Action</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $row): ?>
                        <tr>
                            <td><?= $row['sort_order'] ?></td>
                            <td><?= $row['label'] ?></td>
                            <td><code><?= $row['link'] ?></code></td>
                            <td><?= $row['parent'] ?></td>
                            <td><a href="menu_editor.php?edit=<?= $row['id'] ?>" class="button is-small is-link">Edit</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__."/../template/footer.php"; ?>
