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
include_once("dbconn.php");
echo genForm("INSERT INTO adm.user_1 (usr_cd, usr_name) VALUES (?, ?) ", "usr_cd~usr_name", 
[
    "usr_cd"=>[
        "type"=>"text",
        "label" => "User ID",
        "label_class" => "",
        "control_class" => "",
        "attributes" => ["required"=>"required", "maxlength"=>10, "onclick"=>'alert("hello")']
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
], '', '');
?>
</body>
</html>

<?php
function preTran() {
    // echo "Test PreTran";
}
function genForm($sql, $param, $fields, $formId = "", $formClass = "") {
    if (empty($sql) || empty($param) || empty($fields)) { return "Mandatory parameters missing!"; }
    // Process submission
    if (isset($_POST['submit'])) {
        if (function_exists("preTran")) {
            preTran();
        }
        $param = explode('~', $param);
        foreach ($param as $key => $value) {
            $param[$key] = $_POST[$value];
        }
        // exit(implode('~',$param));
        $ret = ExecSQL($sql, implode('~',$param));
        $notifyClass = ""; $message = "";
        if ($ret) { 
            $notifyClass = "is-success ";
            $message = "Add/ Update successful";
        } else {
            $notifyClass = "is-danger ";
            $message = "Add/ Update failed";
        }
        if (function_exists("preNotify")) {
            preNotify();
        }
        echo "<div class='notification $notifyClass'><button class='delete'></button>".$message;
        echo "</div>";
        if (function_exists("postTran")) {
            postTran();
        }
    }
    // Generate Form
    $html = "";
    $html .= "<form class='".$formClass."' id='".$formId."' method='POST' action='".(isset($fields['actions']['submit']['link'])?$fields['actions']['submit']['link']:"#")."'>";
    foreach ($fields as $key => $value) {
        if ($key == "actions") continue;
        if (empty($value['type'])) { $html .= "<div class='field'>Blank type for control!</div>"; continue; }
        if (!in_array($value['type'],["text", "email", "date", "datetime-local", "password", "month", "week", "number", "textarea", "select"])) { $html .= "<div class='field'>Type of control $value[type] not yet implemented!</div>"; continue; }
        if ($value['type'] == "datetime") $value["type"] = "datetime-local";
        if (empty($value['label_class'])) $value['label_class'] = "";
        if (empty($value['control_class'])) $value['control_class'] = "";
        $html .= "<div class='field'><label class='label $value[label_class]' for='$key'>".(isset($value['label'])?$value['label']:$key).(isset($value['req'])?'*':'')."</label><div class='control'>";
        if (in_array($value['type'],["text", "email", "date", "datetime-local", "password", "month", "week", "number"])) {
            $html .= "<input class='input $value[control_class]' type='".$value['type']."' id='$key' name='$key' ";
            if (!empty($value['attributes'])) {
                foreach ($value['attributes'] as $attKey => $attValue) {
                    $html .= "$attKey= '$attValue' ";
                }
            }
            $html .= " />";
        } elseif ($value['type'] == "textarea") {
            $html .= "<textarea class='input $value[control_class]' type='".$value['type']."' id='$key' name='$key' ";
            if (!empty($value['attributes'])) {
                foreach ($value['attributes'] as $attKey => $attValue) {
                    $html .= "$attKey= '$attValue' ";
                }
            }
            $html .= "/>";
        } elseif ($value['type'] == "select" ) {
            $html .= "<select class='input $value[control_class]' id='$key' name='$key' ";
            if (!empty($value['attributes'])) {
                foreach ($value['attributes'] as $attKey => $attValue) {
                    $html .= "$attKey= '$attValue' ";
                }
            }
            $html .= ">";
            if (empty($value['options'])) continue;
            foreach ($value['options'] as $sKey => $sVal) {
                $html .= "<option value='$sKey' ";
                if (is_array($sVal)) { 
                    $html .= "selected='selected' >".$sVal[0]; 
                } else $html .= ">".$sVal; 
                "</option>";
            }
            $html .= "</select>";
        }
        $html .= "</div></div>";
    }
    if (isset($fields['actions'])) {
        $html .= "<div class='field is-grouped'>";
        foreach ($fields['actions'] as $key => $value) {
            if (empty($value['type'])) { $html .= "<div class='control'>Blank type for control!</div>"; continue; }
            if (!in_array($value['type'],["submit", "link", "button"])) { $html .= "<div class='control'>Type of control $value[type] not yet implemented!</div>"; continue; }
            $html .= "<div class='control'>";
            if ($value['type'] == "submit") {
                $html .= "<input class='button ".(isset($value['class'])?$value['class']:"")."' type='".$value['type']."' id='$key' name='$key' value='$value[label]'/>";
            } elseif ($value['type'] == "link") {
                $html .= "<a class='button ".(isset($value['class'])?$value['class']:"")."' href='$value[link]'>$value[label]</a>";
            } elseif ($value['type'] == "button") {
                $html .= "<button class='button ".(isset($value['class'])?$value['class']:"")."' onclick='".(isset($value['proc'])?$value['proc']:"")."'>$value[label]</button>";
            }
            $html .= "</div>";
        }
        $html .= "</div>";
    }
    $html .= "</form>";
    return $html;
}
?>