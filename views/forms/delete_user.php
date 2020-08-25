<?php
$username = $_GET["username"];

// Check if user even exists first
if(!$Login->CheckIfUserExists($username)) {
    header("Location: ${config["access_point"]}error?error=INVALID_INPUT");
    die();
}
if(!LOGGED_IN) {
    Redirect("login");
}
// Make sure it's admin who's doing it
if($Login->CheckUserRole(false, $_SESSION["username"]) !== "admin") {
    Redirect("dashboard");
}

// Update user role now
$Login->DeleteUser($username);
// Redirect back to dashboard
header("Location: ${config["access_point"]}manage_users");
die();