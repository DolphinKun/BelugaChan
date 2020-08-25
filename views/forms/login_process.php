<?php
// This file process the login info
$username = filter_input(INPUT_POST, "username", FILTER_SANITIZE_STRING);
$password = $_POST["password"];


if(isset($_POST["login_btn"])) {
if(trim($password) == '' || trim($username) == '') {
Redirect("error?error=INVALID_INPUT");
die();
}
if($Login->CheckLogin($username, $password)) {
    $_SESSION["logged_in"] = true;
    $_SESSION["username"] = $username;
    Redirect("dashboard");
} else {
    Redirect("error?error=BAD_LOGIN");
    die();
}
}