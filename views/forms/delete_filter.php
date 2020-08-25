<?php
$is_global = $_GET["is_global"];
$filter_name = $_GET["filter_name"];
$board = $_GET["board"];
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
    $Posts->DeleteGlobalFilter($filter_name);
    header("Location: ${config["access_point"]}manage_global_filters");
    die();
} else {
    $Posts->DeleteBoardFilter($filter_name, $board);
}

header("Location: ${config["access_point"]}manage_board_filters?board=${board}");
die();