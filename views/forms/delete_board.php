<?php
// This file updates the board settings
// Board value
$board = $_GET["board"];
// Session username
$username = $_SESSION["username"];

// Check if user owns and is logged in
if(!$_SESSION["logged_in"]) {
    Redirect("login");
}
if(!$Boards->CheckIfUserOwnsBoard($board, $username)) {
    if($Login->GetUserInfo($username)["role"] !== "admin") {
        Redirect("dashboard");
    }
}

// Update the board config now
$Boards->DeleteBoard($board);
// Redirect user now
Redirect("dashboard");