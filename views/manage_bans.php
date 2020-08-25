<?php

// Declare Twig
$loader = new \Twig\Loader\FilesystemLoader($config["templates_dir"]);
$twig = new \Twig\Environment($loader);
$username = $_SESSION["username"];
$board = $_GET["board"];
$global_bans = $_GET["global_bans"];
if(!LOGGED_IN) {
    Redirect("login");
}
if(!$global_bans && !$board) {
    Redirect("dashboard");
}
if($global_bans == true && ($Boards->UserIsVol($username, $board) == "gvol" || $Login->CheckUserRole($board, $username) == "admin")) {
    $bans = $Bans->GetAllGlobalBans();
} else {
    if(!$Login->CheckUserRole($board, $username)) {
        Redirect("dashboard");
    }
    $bans = $Boards->GetAllBans($board);
}
echo $twig->render('manage_bans.twig', [
    "config" => $config,
    "board_info" => $Boards->GetBoardInfo($board),
    "board" => $board,
    "board_bans" => $bans,
    "is_global" => $global_bans,
]);