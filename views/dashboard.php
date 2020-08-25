<?php
$username = $_SESSION["username"];

// Declare Twig
$loader = new \Twig\Loader\FilesystemLoader($config["templates_dir"]);
$twig = new \Twig\Environment($loader);

if(!LOGGED_IN) {
    Redirect("login");
}

echo $twig->render('dashboard.twig', [
    "config" => $config,
    "boards_owned" => $Boards->GetBoardsOwnedByUser($username),
    "board_count" => $Boards->BoardUserCount($username),
    "boards_vol" => $Boards->GetAllVolunteeredBoards($username),
    "user_info" => $Login->GetUserInfo($username),
    "gvol" => ($Boards->UserIsVol($username, null) == "gvol" || $Login->CheckUserRole(null, $username) == "admin")
]);