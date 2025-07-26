<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.7.1/css/bulma.min.css">
        <script defer src="https://use.fontawesome.com/releases/v5.0.7/js/all.js"></script>
    </head>
    <body>
<?php
include __DIR__."/../class/Gen.php";
$gen = new Gen();
if (empty($_GET['code'])) { echo "Key not found! Please contact IT Deptt."; exit(); }
$gen->sql = "SELECT usr_cd, usr_name FROM adm.user_1 WHERE usr_cd = ? ";
$gen->param = $_GET['code'];
$gen->fields = [
    "usr_cd" => "User ID",
    "usr_name" => "User Name"
];
echo $gen->view();
?>
    </body>
</html>