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
$gen->sql = "SELECT * FROM adm.user_1 WHERE 1=1";
$gen->fields = [
    "usr_cd" => "User ID",
    "usr_name" => "User Name",
    "usr_passwd" => "Password"
];
echo $gen->table();
?>
    </body>
</html>