<?php
// This file updates the board settings

// Board value
$board = $_POST["board"];
// Session username
$username = $_SESSION["username"];
// Lock the board?
$board_locked = isset($_POST["locked"]) ? (int)$_POST["locked"] : 0;
// Owner
$board_owner = isset($_POST["owner"]) ? $_POST["owner"] : 0;
// Theme
$board_theme = isset($_POST["theme"]) ? $_POST["theme"] : $config["default_theme"];
// Custom CSS
$board_css = $_POST["custom_css"];
// Enable country flags?
$enable_country_flags = isset($_POST["enable_country_flags"]) ? (int)$_POST["enable_country_flags"] : 0;
// Hide board?
$hide_board = isset($_POST["hide_board"]) ? $_POST["hide_board"] : 0;
// Enable user IDs?
$enable_ids = isset($_POST["enable_ids"]) ? $_POST["enable_ids"] : 0;
// Board password?
$board_password = $_POST["board_password"];
if(empty($board_password)) {
    $board_password = false;
} elseif($board_password == "password") {
    $board_password = $Boards->GetBoardConfig($board)["password"];
}
// Check if user owns and is logged in
if(!LOGGED_IN) {
    Redirect("login");
}
if(!$Login->CheckUserRole($board, $username) || !$Login->CheckUserRole($board, $username) == "admin") {
    Redirect("dashboard");
}
// Check if new owner exists, same with the theme
if(!in_array($board_theme, $config["themes"]) || !$Login->CheckIfUserExists($board_owner)) {
    Redirect("error?error=UPDATE_FAIL");
    die();
}
// Update the board config now
$Boards->UpdateConfig($board, $board_locked, $board_owner, $board_theme, $board_css, $enable_country_flags, $hide_board, $enable_ids, $board_password);
// Redirect user now
Redirect("dashboard");