<?php

// Declare Twig
$loader = new \Twig\Loader\FilesystemLoader($config["templates_dir"]);
$twig = new \Twig\Environment($loader);
$username = $_SESSION["username"];
$board = $_GET["board"];
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$emotes_per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : $config["emotes_per_page"];
$emote_pagination = ($page > 1) ? ($page * $emotes_per_page) - $emotes_per_page : 0;
if(!LOGGED_IN) {
    Redirect("login");
}
if(!$Boards->CheckIfUserOwnsBoard($board, $username)) {
    Redirect("dashboard");
}

$emotes = $Boards->GetAllBoardEmotes($board, [$emote_pagination, $emotes_per_page]);
$paginated_pages = ceil($emotes[0] / $emotes_per_page);
for($x = 1; $x <= $paginated_pages; $x++):
    $pages[] = $x;
endfor;
$error = $_GET["error"];
echo $twig->render('manage_emotes.twig', [
    "config" => $config,
    "board" => $board,
    "emotes" => $emotes[1],
    "paginated_pages" => $pages,
    "per_page" => $emotes_per_page,
    "error" => $error,
]);