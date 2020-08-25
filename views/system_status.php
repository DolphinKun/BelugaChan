<?php
$username = $_SESSION["username"];
// Declare Twig
$loader = new \Twig\Loader\FilesystemLoader($config["templates_dir"]);
$twig = new \Twig\Environment($loader);
$SystemStatus = new SystemStatus($config);
if(!LOGGED_IN) {
    Redirect("login");
}
if($Login->CheckUserRole($board, $username) !== "admin") {
    Redirect("dashboard");
}

echo $twig->render('system_status.twig', [
    "config" => $config,
    "system_status" => $SystemStatus->GetStatus(),
]);