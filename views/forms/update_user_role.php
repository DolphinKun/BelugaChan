<?php
$username = $_POST["username"];
// User role
$user_role = $_POST["role"];

// Check if user role is in config and whether the user exists
if(!in_array($user_role, $config["user_roles"]) || !$Login->CheckIfUserExists($username)) {
    header("Location: ${config["access_point"]}error?error=INVALID_INPUT");
    die();
}
if(!LOGGED_IN) {
    Redirect("login");
}
if($Login->CheckUserRole(false, $_SESSION["username"]) !== "admin") {
    Redirect("dashboard");
}

// Update user role now
$Login->UpdateUserRole($username, $user_role);
// Redirect back to dashboard
header("Location: ${config["access_point"]}manage_users");
die();