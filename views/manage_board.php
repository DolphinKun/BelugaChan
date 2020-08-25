<?php

// Declare Twig
$loader = new \Twig\Loader\FilesystemLoader($config["templates_dir"]);
$twig = new \Twig\Environment($loader);
$username = $_SESSION["username"];
$board = $_GET["board"];

if(!LOGGED_IN) {
    Redirect("login");
}
if($Boards->CheckIfUserOwnsBoard($board, $username) || $Login->CheckUserRole($board, $username) == "admin") {
    echo $twig->render('manage_board.twig', [
        "config" => $config,
        "board_config" => $Boards->GetBoardConfig($board),
        "board_info" => $Boards->GetBoardInfo($board),
        "board" => $board,
        "vols" => $Boards->GetVols($board),
        "board_css" => $Boards->GetBoardCSS($board),
    ]);
} else {
    Redirect("dashboard");
}