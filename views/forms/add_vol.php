<?php
// This file updates the board settings
// Board value
$board = $_POST["board"];
// Session username
$username = $_SESSION["username"];
// Vol to add
$vol_username = $_POST["vol"];
// Check if user owns and is logged in
if(!$Login->CheckUserRole($board, $username) || !LOGGED_IN) {
    Redirect("dashboard");
}
// Check if vol username exists
if(!$Login->CheckIfUserExists($vol_username)) {
    Redirect("error?error=VOL_DOES_NOT_EXIST");
    die();
}
// Add vol
$Boards->AddVol($board, $vol_username);
// Redirect user now
Redirect("dashboard");