<?php
// This file updates the board settings
// Board value
$board = $_GET["board"];
// Session username
$username = $_SESSION["username"];
// Vol to add
$vol_username = $_GET["vol"];
// Check if user owns and is logged in
if(!LOGGED_IN) {
    Redirect("login");
}
if(!$Boards->CheckIfUserOwnsBoard($board, $username)) {
    if($Login->GetUserInfo($username)["role"] !== "admin") {
        Redirect("dashboard");
    }
}
// Add vol
$Boards->RemoveVol($board, $vol_username);
// Redirect user now
Redirect("dashboard");