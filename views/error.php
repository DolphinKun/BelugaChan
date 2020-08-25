<?php

// Declare Twig
$loader = new \Twig\Loader\FilesystemLoader($config["templates_dir"]);
$twig = new \Twig\Environment($loader);

$error = $_GET["error"];

$ip = $Posts->GetUserIP()[0];
$board = $_GET["board"];
switch($error) {
    case "THREAD_DOES_NOT_EXIST":
        header("HTTP/1.0 404 Not Found");
        break;
    case "BANNED":
        $ban_info = $Bans->GetBanInfo($ip, $board);
        break;
}
if($error == "BANNED" && !$Boards->CheckIfBoardExists($board)) {
    die("Board/ban does not exist.");
}
echo $twig->render('error.twig', [
    "config" => $config,
    "error" => $error,
    "reason" => $ban_info["reason"],
    "ip" => $ip,
    "is_global" => $ban_info["is_global"],
    "board" => $board,
    "ban_info" => $ban_info,
]);