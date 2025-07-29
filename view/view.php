<?php
include __DIR__."/../template/headerAuth.php";
include BASE_DIR."class/Gen.php";
$gen = new Gen();
if (empty($_GET['code'])) { echo "Key not found! Please contact IT Deptt."; exit(); }
$gen->sql = "SELECT usr_cd, usr_name FROM adm.user_1 WHERE usr_cd = ? ";
$gen->param = $_GET['code'];
$gen->fields = [
    "usr_cd" => "User ID",
    "usr_name" => "User Name"
];
echo $gen->view();
include __DIR__."/../template/footer.php";
