<?php
// Check if account registration is open first!
if(!$config["account_registration_enabled"]) {
    die("<h1>Account registration is not open!</h1>");
}
// Declare Twig
$loader = new \Twig\Loader\FilesystemLoader($config["templates_dir"]);
$twig = new \Twig\Environment($loader);

if($_SESSION["logged_in"]) {
    Redirect("dashboard");
}

echo $twig->render('register.twig', [
    "config" => $config,
]);