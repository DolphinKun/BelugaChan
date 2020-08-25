<?php
$is_global = $_GET["is_global"];
$filter_text = $_POST["filter_text"];
$replacement_text = $_POST["replacement"];
$board = $_POST["board"];
$ban_on_post = isset($_POST["ban_on_post"]) ? (int)$_POST["ban_on_post"] : 0;

if(!LOGGED_IN) {
    Redirect("login");
}
// Check if the board is owned by the user
if($board && !$Boards->CheckIfUserOwnsBoard($board, $username)) {
    Redirect("dashboard");
}
// Make sure it's admin who's doing it
if($is_global && $Login->CheckUserRole(false, $username) !== "admin") {
    Redirect("dashboard");
}

if($is_global) {
    $Posts->AddGlobalFilter($filter_text, $replacement_text, $ban_on_post);
    header("Location: ${config["access_point"]}manage_global_filters");
    die();
} else {
    $Posts->AddBoardFilter($filter_text, $replacement_text, $board, $ban_on_post);
}

// Redirect back to dashboard
header("Location: ${config["access_point"]}manage_board_filters?board=${board}");
die();