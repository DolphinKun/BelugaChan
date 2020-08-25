<?php

// Declare Twig
$loader = new \Twig\Loader\FilesystemLoader($config["templates_dir"]);
$twig = new \Twig\Environment($loader);
$username = $_SESSION["username"];
$board = $_GET["board"];
if(!LOGGED_IN) {
    Redirect("login");
}
if(!$Boards->CheckIfUserOwnsBoard($board, $username)) {
    Redirect("dashboard");
}

echo $twig->render('manage_banners.twig', [
    "config" => $config,
    "board" => $board,
    "banners" => $Boards->GetAllBanners($board),
]);