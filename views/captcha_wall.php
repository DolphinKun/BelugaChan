<?php

// Declare Twig
$loader = new \Twig\Loader\FilesystemLoader($config["templates_dir"]);
$twig = new \Twig\Environment($loader);
$error = $_GET["error"];
echo $twig->render('captcha_wall.twig', [
    "config" => $config,
    "error" => $error,
]);