<?php
// This file deletes a board banner!
// Board value
$board = $_GET["board"];
// Session username
$username = $_SESSION["username"];
// Get banner ID
$banner_id = (int)$_GET["banner"];
// Check if user owns board and is logged in
if(!$_SESSION["logged_in"]) {
    Redirect("login");
}
if(!LOGGED_IN) {
    Redirect("login");
}
if(!$Login->CheckUserRole($board, $username) === "admin" || !$Login->CheckUserRole($board, $username)) {
    Redirect("dashboard");
}
if(!$Boards->CheckIfBannerIsForBoard($board, $banner_id)) {
    Redirect("dashboard");
}
// Delete banner now
$Boards->DeleteBanner($banner_id);
// Redirect user now
header("Location: " . $config["access_point"] . "manage_banners?board=" . $board);
die();