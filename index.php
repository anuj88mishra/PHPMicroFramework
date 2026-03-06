<?php
require_once __DIR__ . '/template/headerAuth.php';
?>

<div class="box">
    <h1 class="title">Welcome to <?= SITE_TITLE ?></h1>
    <p class="subtitle mt-3">Hello, <strong><?= htmlspecialchars($_SESSION['USER_NAME']) ?></strong>!</p>
    
    <div class="content mt-5">
        <p>You are currently logged in with the following roles: 
        <span class="tag is-info is-light"><?= implode('</span> <span class="tag is-info is-light">', $_SESSION['ROLES']) ?></span></p>
    </div>

    <div class="columns mt-6">
        <div class="column is-4">
            <div class="notification is-link is-light">
                <h3 class="title is-5"><i class="fas fa-plus-circle mr-2"></i> Quick Start</h3>
                <p>Ready to build? Head over to the <strong>CRUD Builder</strong> to start generating modules from your database tables.</p>
                <a href="view/crud_builder.php" class="button is-link is-small mt-3">Open Builder</a>
            </div>
        </div>
        <div class="column is-4">
            <div class="notification is-success is-light">
                <h3 class="title is-5"><i class="fas fa-list mr-2"></i> Manage Modules</h3>
                <p>View, edit, or compile your existing CRUD modules to optimized fast-loading pages.</p>
                <a href="view/crud_list.php" class="button is-success is-small mt-3">View Modules</a>
            </div>
        </div>
        <div class="column is-4">
            <div class="notification is-warning is-light">
                <h3 class="title is-5"><i class="fas fa-bars mr-2"></i> Navigation</h3>
                <p>Customize the application's navigation structure and dropdowns with the graphical editor.</p>
                <a href="view/menu_editor.php" class="button is-warning is-small mt-3">Edit Menus</a>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/template/footer.php'; ?>
