<?php
// Declare Twig
$loader = new \Twig\Loader\FilesystemLoader($config["templates_dir"]);
$twig = new \Twig\Environment($loader);
$username = $_SESSION["username"];
if(!$_SESSION["logged_in"]) {
    Redirect("login");
}

if($Login->GetUserInfo($username)["role"] !== "admin") {
    Redirect("dashboard");
}

echo $twig->render('manage_config.twig', [
    "config" => $config,
]);