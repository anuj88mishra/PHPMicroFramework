<?php
require_once __DIR__ . "/../template/headerAuth.php";
require_once BASE_DIR . "class/Compiler.php";

$moduleID = $_GET['id'] ?? null;
if (!$moduleID) {
    die("No module ID provided.");
}

$compiler = new Compiler();
$result = $compiler->compile($moduleID);

if ($result['status']) {
    $_SESSION['NOTIFYMESSAGE'] = "Fast Page '{$result['filename']}' Generated Successfully! <a href='{$result['filename']}' class='has-text-weight-bold'>Access it here</a>";
    $_SESSION['NOTIFYCLASS'] = "notification is-success";
} else {
    $_SESSION['NOTIFYMESSAGE'] = "Compilation failed: " . $result['message'];
    $_SESSION['NOTIFYCLASS'] = "notification is-danger";
}

header("Location: crud_list.php");
exit();
