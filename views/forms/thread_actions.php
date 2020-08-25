<?php
// Processes banning of users from certain boards
$board = $_GET["board"];
$post_id = (int)$_GET["id"];
$action = $_GET["action"];

if (!LOGGED_IN) {
    Redirect("login");
}
if ($Boards->CheckIfUserOwnsBoard($board, $username) || $Boards->UserIsVol($username, $board)) {
    switch($action) {
        case "pin":
            $Posts->PinThread($post_id, true);
            break;
        case "unpin":
            $Posts->PinThread($post_id, false);
            break;
        case "lock":
            $Posts->LockThread($post_id, true);
            break;
        case "unlock":
            $Posts->LockThread($post_id, false);
            break;
        default:
            die("No action specified");
    }


}
header("Location: " . $config["access_point"] . $board . "/");
die();