<?php
$board = $_GET["board"];
// Declare Twig
$loader = new \Twig\Loader\FilesystemLoader($config["templates_dir"]);
$twig = new \Twig\Environment($loader);

if(!LOGGED_IN) {
    Redirect("login");
}
if(!$Boards->CheckIfUserOwnsBoard($board, $username)) {
    Redirect("dashboard");
}

echo $twig->render('manage_board_filters.twig', [
    "config" => $config,
    "board_filters" => $Posts->GetBoardFilters($board),
    "board" => $board,
]);