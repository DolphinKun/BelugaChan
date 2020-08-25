<?php
// Declare Twig
$loader = new \Twig\Loader\FilesystemLoader($config["templates_dir"]);
$twig = new \Twig\Environment($loader);

$post_id = (int)$_GET["id"];

if(!$post_id) {
Redirect("error?error=INVALID_INPUT");
}

echo $twig->render('report.twig', [
    "config" => $config,
    "post_id" => $post_id,
]);