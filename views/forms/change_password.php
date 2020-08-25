<?php
// This file is responsible for changing the currently logged in user's password
$old_password = $_POST["old_password"];
$new_password = $_POST["new_password"];

$username = $_SESSION["username"];
if (!LOGGED_IN) {
    Redirect("login");
}

if (isset($_POST["change_password_button"])) {
    if ($Login->ChangePassword($username, $old_password, $new_password)) {
        // Destroy session to stop currently logged in user from accessing the dashboard until after login
        session_destroy();
        // Redirect to login
        Redirect("login");
    } else {
        Redirect("error?error=BAD_LOGIN");
        die();
    }
}