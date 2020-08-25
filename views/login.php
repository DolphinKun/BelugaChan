<?php

// Declare Twig
$loader = new \Twig\Loader\FilesystemLoader($config["templates_dir"]);
$twig = new \Twig\Environment($loader);

if(LOGGED_IN) {
    Redirect("dashboard");
}

echo $twig->render('login.twig', [
    "config" => $config,
]);