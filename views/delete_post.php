<?php
// Declare Twig
$loader = new \Twig\Loader\FilesystemLoader($config["templates_dir"]);
$twig = new \Twig\Environment($loader);

$board = filter_input(INPUT_GET, "board", FILTER_SANITIZE_STRING);
$id = (int)$_GET["id"];
echo $twig->render('delete_post.twig', [
    "config" => $config,
    "id" => $id,
    "board" => $board,
]);