<?php
include __DIR__."/../template/headerAuth.php";
include BASE_DIR."class/Gen.php";
$gen = new Gen();
$gen->sql = "SELECT id, usr_cd, usr_name, user_alias FROM users";
$gen->fields = [
    "id" => "ID",
    "usr_cd" => "Login ID",
    "usr_name" => "Name",
    "user_alias" => "Alias/Email"
];
echo $gen->table();
include __DIR__."/../template/footer.php";
