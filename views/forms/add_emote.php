<?php
// Get board name
$board = $_POST["board"];
$name = $_POST["name"];
$url = filter_input(INPUT_POST, "url", FILTER_SANITIZE_URL);
// Check if user owns board!
$username = $_SESSION["username"];

if(!$Login->CheckUserRole($board, $username) || !$Login->CheckUserRole($board, $username) == "admin" || !LOGGED_IN) {
    Redirect("");
    die();
}

// Check if user pressed the add emote button first
if ($config["emotes_enabled"] && isset($_POST["add_emote_btn"]) && trim($name) !== '') {
    if ($Boards->CheckIfEmoteExists($name, $board)) {
        Redirect("manage_emotes?board=${board}&error=EMOTE_EXISTS");
    }
    if(!filter_var($url, FILTER_VALIDATE_URL)) {
        Redirect("error?error=INVALID_INPUT");
    }
    $Boards->AddBoardEmote($name, $url, $board);
}
header("Location: " . $config["access_point"] . "manage_emotes?board=" . $board);
die();