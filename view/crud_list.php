<?php
include __DIR__."/../template/headerAuth.php";
include BASE_DIR."class/Gen.php";

$conn = new Conn();

// Handle deletion of module
if (isset($_GET['delete'])) {
    $conn->ExecSQL("DELETE FROM crud_modules WHERE id = ?", $_GET['delete']);
    $_SESSION['NOTIFYMESSAGE'] = "Module Deleted";
    $_SESSION['NOTIFYCLASS'] = "notification is-warning";
    header("Location: crud_list.php");
    exit();
}

$data = $conn->SQLCursor("SELECT id, module_name, table_name, created_at FROM crud_modules ORDER BY created_at DESC");

?>

<div class="level">
    <div class="level-left"><h1 class="title">CRUD Modules</h1></div>
    <div class="level-right"><a href="crud_builder.php" class="button is-primary">Create New CRUD</a></div>
</div>

<div class="columns is-multiline">
    <?php if ($data): ?>
        <?php foreach ($data as $row): ?>
            <div class="column is-4">
                <div class="card">
                    <header class="card-header"><p class="card-header-title"><?= $row['module_name'] ?></p></header>
                    <div class="card-content">
                        <p class="subtitle is-6">Target Table: <code><?= $row['table_name'] ?></code></p>
                        <p class="is-size-7">Created: <?= date('d M Y', strtotime($row['created_at'])) ?></p>
                        <hr>
                        <div class="buttons">
                            <a href="dynamic_view.php?id=<?= $row['id'] ?>" class="button is-small is-link">Access View</a>
                            <a href="crud_list.php?delete=<?= $row['id'] ?>" class="button is-small is-danger is-light" onclick="return confirm('Ensure you want to delete this module?')">Delete</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="column is-12 has-text-centered"><p class="box">No modules found. Start by creating one!</p></div>
    <?php endif; ?>
</div>

<?php include __DIR__."/../template/footer.php"; ?>
