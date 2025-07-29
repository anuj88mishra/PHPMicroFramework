<?php
include __DIR__."/../template/headerAuth.php";
include BASE_DIR."class/Gen.php";
$gen = new Gen();
$gen->sql = "INSERT INTO adm.user_1 (usr_cd, usr_name) VALUES (?, ?)";
$gen->param = "usr_cd~usr_name";
$gen->fields = [
    "usr_cd"=>[
        "type"=>"text",
        "label" => "User ID",
        "label_class" => "",
        "control_class" => "",
        "attributes" => ["required"=>"required", "maxlength"=>10,
            // "onclick"=>'alert("hello")'
        ]
    ], "usr_name"=>[
        "type" => "text",
        "label" => "Name",
        "label_class" => "",
        "control_class" => "",
        // "options" => ["1"=>"One", "2" => ["Two","selected"]],
    ], "actions" => [
        "submit" => [
            "type" => "submit",
            "label" => "Submit",
            "class" => ""
        ],
        "link1" => [
            "type" => "link",
            "label" => "Back",
            "link" =>"test.php"
        ],
        "button1" => [
            "type" => "button",
            "label" => "TestButton",
            "proc" => "ClickMe()"
        ]
    ]
];
echo $gen->form();
include __DIR__."/../template/footer.php";
