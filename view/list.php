<?php
include __DIR__."/../template/headerAuth.php";
include BASE_DIR."class/Gen.php";
$gen = new Gen();
$gen->sql = "SELECT * FROM adm.user_1 WHERE 1=1";
$gen->fields = [
    "usr_cd" => "User ID",
    "usr_name" => "User Name",
    "usr_passwd" => "Password"
];
echo $gen->table();
include __DIR__."/../template/footer.php";
