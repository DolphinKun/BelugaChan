<?php
// Processes unbanning of salted IP
$board = $_GET["board"];
$is_global = $_GET["is_global"];
$ip = $_GET["ip"];
$username = $_SESSION["username"];
if (!LOGGED_IN) {
    Redirect("login");
}
if ($board && $Login->CheckUserRole($board, $username) == "admin" || $Login->CheckUserRole($board, $username) || $Login->CheckUserRole($board, $username) == "vol") {
    $Posts->LiftIPBan($board, $ip);
}
if ($is_global && ($Boards->UserIsVol($username, $board) == "gvol" || $Login->CheckUserRole($board, $username) == "admin")) {
    $Bans->LiftGlobalBan($ip);
}
header("Location: " . $config["access_point"] . "manage_bans?board=${board}&global_bans=${is_global}");
die();