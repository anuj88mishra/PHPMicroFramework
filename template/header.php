<?php 
include __DIR__."/../config.php";
date_default_timezone_set("Asia/Kolkata");
// header("Cache-Control: no-cache, must-revalidate");
// header("Expires: -1");
if (SECURE) {
    header("X-XSS-Protection: 1; mode=block");
    header('Access-Control-Allow-Headers: *');
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
    <meta name="description" content="<?php echo "TEST".SITE_DESC;?>" >
    <title><?=SITE_TITLE?></title>
    <link rel="stylesheet" href="https://bulma.io/vendor/fontawesome-free-6.5.2-web/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.7.1/css/bulma.min.css">
    <script defer src="https://use.fontawesome.com/releases/v5.0.7/js/all.js"></script>
</head>
<body>
<div class="container">