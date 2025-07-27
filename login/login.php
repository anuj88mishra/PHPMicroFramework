<?php
include __DIR__."/../template/header.php";
include BASE_DIR."class/Gen.php";
$gen = new Gen();
echo "<div class='$gen->cardClass'>";
echo "<div class='$gen->cardHeaderClass'>";
echo "<div class='$gen->cardHeaderTitleClass'>Login to ".SITE_TITLE."</div>";
echo "</div>";
echo "<div class='$gen->cardContentClass'>";
$gen->fields = [
    "usr_cd" => [
        "type" => "text",
        "label" => "Login ID",
        "attributes" => ["required"=>"required", "maxlength"=>10]
    ],
    "usr_passwd" => [
        "type" => "password",
        "label" => "Password",
        "attributes" => ["required"=>"required"]
    ],
    "actions" => [
        "submit" => [
            "type" => "submit",
            "label" => "Submit",
            "class" => "is-link"
        ]
    ]
];
echo $gen->form();
echo "</div>";
echo "</div>";