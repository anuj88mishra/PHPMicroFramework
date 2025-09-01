<?php 
session_start();
include __DIR__."/../config.php";
include BASE_DIR."class/HtmlClass.php";
date_default_timezone_set("Asia/Kolkata");
// header("Cache-Control: no-cache, must-revalidate");
// header("Expires: -1");
if (SECURE) {
    header("X-XSS-Protection: 1; mode=block");
    // header('Access-Control-Allow-Headers: *');
    header('X-Content-Type-Options: nosniff');
    header("Access-Control-Allow-Methods: POST, GET");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");  // Allowed headers
    header('Access-Control-Allow-Headers: Content-Type');
    header("Access-Control-Expose-Headers: Content-Disposition");
    header_remove("X-Powered-By");
}
if (DEBUG) {
    error_reporting(E_ALL); ini_set('display_errors', '1');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?=SITE_DESC?>" >
    <title><?=SITE_TITLE?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" integrity="sha512-DxV+EoADOkOygM4IR9yXP8Sb2qwgidEmeqAEmDKIOfPRQZOWbXCzLC6vjbZyy0vPisbH2SyW27+ddLVCN+OMzQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/1.0.4/css/bulma.min.css" integrity="sha512-yh2RE0wZCVZeysGiqTwDTO/dKelCbS9bP2L94UvOFtl/FKXcNAje3Y2oBg/ZMZ3LS1sicYk4dYVGtDex75fvvA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script defer src="https://use.fontawesome.com/releases/v5.0.7/js/all.js"></script>
</head>
<body>
    <!-- NAV -->
<div class="container">
    <?php 
    if (empty($_SESSION['USER'])) {
        $_SESSION['NOTIFYMESSAGE'] = "Please login to access this resource";
        $_SESSION['NOTIFYCLASS'] = "notification is-danger is-light";
        header("location:".BASE_URL."login/login.php");
    }
    if (isset($_SESSION['NOTIFYMESSAGE'])) {
        $htmlClass = new HtmlClass();
        $htmlClass->notifyClass = $_SESSION['NOTIFYCLASS'];
        $htmlClass->notifyMessage = $_SESSION['NOTIFYMESSAGE']; 
        echo "<div class='$htmlClass->notifyClass'><button class='delete'></button>$htmlClass->notifyMessage</div>"; 
        unset($_SESSION['NOTIFYCLASS']);
        unset($_SESSION['NOTIFYMESSAGE']);
    }
    // Error Handling
    // Fatal Errors on part of dev which would require further processing to stop
    if (isset($_SESSION['FERROR'])) { 
        $htmlClass = new HtmlClass();
        $htmlClass->notifyClass = "notification is-danger";
        $htmlClass->notifyMessage = $_SESSION['FERROR']; 
        echo "<div class='$htmlClass->notifyClass'><button class='delete'></button>$htmlClass->notifyMessage</div>"; 
        $_SESSION['FERROR'] = null; 
        exit();
    }
    // Non-Fatal Errors/ Warnings to assist the user, further processing shall continue
    if (isset($_SESSION['NFERROR'])) { 
        $htmlClass = new HtmlClass();
        $htmlClass->notifyClass = "notification is-danger is-light";
        $htmlClass->notifyMessage = $_SESSION['NFERROR']; 
        echo "<div class='$htmlClass->notifyClass'><button class='delete'></button>$htmlClass->notifyMessage</div>"; 
        $_SESSION['NFERROR'] = null; 
    }