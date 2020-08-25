<?php

// Declare Twig
$loader = new \Twig\Loader\FilesystemLoader($config["templates_dir"]);
$twig = new \Twig\Environment($loader);


$twig = new \Twig\Environment($loader);

echo $twig->render('boards.twig', [
    "config" => $config,
    "boards" => $Boards->GetAllBoards(),
]);