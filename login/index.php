<?php
include __DIR__."/../template/header.php";
include BASE_DIR."class/Gen.php";
/* Logout */
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    Util::C_REDIRECT(BASE_URL."login/login.php");
}
/* End Logout */
final class Login extends Gen {
    public function __construct() {
        parent::__construct();
    }
    public function __destruct() {
        parent::__destruct();
    }
    public function preTran() {
        $this->ret = $this->conn->SQLFetchRow("SELECT usr_name, user_alias as email FROM adm.user_1 WHERE usr_cd = UPPER(?) AND usr_passwd = md5(?) AND COALESCE(rec_ind,'A') <> 'X'", $_POST['usr_cd']."~".$_POST['usr_passwd']);
        if ($this->ret) {
            $_SESSION['NOTIFYMESSAGE'] = "Login Successful";
            $_SESSION['NOTIFYCLASS'] = "notification is-success is-light";
            $_SESSION['USER'] = $_POST['usr_cd'];
            $_SESSION['EMAIL'] = $_POST['email'];
            Util::C_REDIRECT(BASE_URL."view/list.php");
        } else {
            $this->notifyMessage = "Username/ Password Incorrect";
            $this->notifyClass = "notification is-danger is-light";
        }
    }
}
$login = new Login();
$login->formTitle = "Login to ".SITE_TITLE;
$login->fields = [
    "usr_cd" => [
        "type" => "text",
        "label" => "Login ID",
        "attributes" => ["required"=>"required", "maxlength"=>10, "value" => isset($_POST['usr_cd'])?$_POST['usr_cd']:""]
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
echo $login->form();
include __DIR__."/../template/footer.php";
