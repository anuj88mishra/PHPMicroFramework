<?php 
require_once BASE_DIR . "class/MenuManager.php";
$menuManager = new MenuManager();

$isLoggedIn = !empty($_SESSION['USER']);
?>
<nav class="navbar is-primary mb-5" role="navigation" aria-label="main navigation">
  <div class="container">
    <div class="navbar-brand">
      <a class="navbar-item has-text-weight-bold is-size-4" href="<?=BASE_URL?>">
        <?=SITE_TITLE?>
      </a>

      <a role="button" class="navbar-burger" aria-label="menu" aria-expanded="false" data-target="navbarMain">
        <span aria-hidden="true"></span>
        <span aria-hidden="true"></span>
        <span aria-hidden="true"></span>
      </a>
    </div>

    <div id="navbarMain" class="navbar-menu">
      <div class="navbar-start">
        <?php if ($isLoggedIn): ?>
            <?= $menuManager->renderNavbar() ?>
        <?php else: ?>
            <a class="navbar-item" href="<?=BASE_URL?>">Home</a>
        <?php endif; ?>
      </div>

      <div class="navbar-end">
        <div class="navbar-item">
          <div class="buttons">
            <?php if ($isLoggedIn): ?>
              <span class="tag is-info is-light mr-3">User: <?= $_SESSION['USER'] ?></span>
              <a class="button is-light" href="<?=BASE_URL?>login/index.php?logout">
                Logout
              </a>
            <?php else: ?>
              <a class="button is-light" href="<?=BASE_URL?>login/">
                Log in
              </a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</nav>
