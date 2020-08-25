<?php
$board = $_GET["board"];
// Declare Twig
$loader = new \Twig\Loader\FilesystemLoader($config["templates_dir"]);
$twig = new \Twig\Environment($loader);

if (!LOGGED_IN) {
    Redirect("login");
}
if ($Boards->CheckIfUserOwnsBoard($board, $username) || $Login->CheckUserRole($board, $username) == "admin") {

    echo $twig->render('send_alert.twig', [
        "config" => $config,
        "board" => $board,
    ]);
}