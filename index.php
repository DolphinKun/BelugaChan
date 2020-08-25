<?php
session_start();
// Include config and required files
include "config.php";
include "autoload.php";
include "vendor/autoload.php";
include "securimage/securimage.php";
// For captcha, if enabled
$Securimage = new Securimage();
$Logs = new Logs($config);
$BelugaCloud = new BelugaCloud($config);
$Cache = new Cache($config);
$Boards = new Boards($config, $dbh, $routes, $Logs, $Cache, $BelugaCloud);
$Login = new Login($config, $dbh, $Boards, $BelugaCloud);
$PostsWebSockets = new PostsWebSockets($config, $dbh);
$Bans = new Bans($config, $dbh, $BelugaCloud);
$Posts = new Posts($config, $dbh, $Logs, $Login, $Boards, $PostsWebSockets, $Securimage, $Bans, $BelugaCloud);
header("Server: ${config["software_name"]}");
$request_uri = explode('?', $_SERVER['REQUEST_URI'], 2);
$thread = explode("/", $request_uri[0])[2];
$board = $request_uri[0];
// Strip all slashes and remove thread ID from $board, so it will work correctly.
$board = str_replace("/", "", $board);
$board = str_replace($thread, "", $board);
$username = $_SESSION["username"];

function Redirect($page) {
    global $config;
    header("Location: ${config["access_point"]}${page}");
    die();
}

// For captcha wall!
if($request_uri[0] == $config["access_point"] . "forms/captcha_wall") {
    include "views/forms/captcha_wall.php";
    die();
}
if ($config["captcha_wall"] && !$_SESSION["captcha_wall"]) {
include "views/captcha_wall.php";
die();
}

if ($_SESSION["logged_in"]) {
    define("LOGGED_IN", true);
} else {
    define("LOGGED_IN", false);
}
if ($Boards->CheckIfBoardExists($board) || is_int($thread) && $Posts->CheckIfThreadExists($thread)) {
    switch ($thread) {
        case "catalog":
            include __DIR__ . "/views/board_catalog.php";
            break;
        default:
            require __DIR__ . "/views/board.php";
            break;
    }
} else {
    if (isset($routes[$request_uri[0]])) {
        include "views/" . $routes[$request_uri[0]] . ".php";
    } else {
        include "views/error_pages/404.php";
    }
}