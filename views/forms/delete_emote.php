<?php
// Get board name
$board = $_GET["board"];
$name = $_GET["name"];
// Check if user owns board!
$username = $_SESSION["username"];

if(!$Login->CheckUserRole($board, $username) || !$Login->CheckUserRole($board, $username) == "admin" || !LOGGED_IN) {
    Redirect("");
    die();
}

// Check if user pressed the add emote button first
if ($config["emotes_enabled"] && isset($name)) {
    if (!$Boards->CheckIfEmoteExists($name, $board)) {
        Redirect("manage_emotes?board=${board}&error=EMOTE_DOES_NOT_EXIST");
    }
    $Boards->DeleteBoardEmote($name, $board);
}
header("Location: " . $config["access_point"] . "manage_emotes?board=" . $board);
die();