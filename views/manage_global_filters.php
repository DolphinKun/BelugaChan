<?php
$username = $_SESSION["username"];
// Declare Twig
$loader = new \Twig\Loader\FilesystemLoader($config["templates_dir"]);
$twig = new \Twig\Environment($loader);

if(!LOGGED_IN) {
    Redirect("login");
}
if($Login->CheckUserRole($board, $username) !== "admin") {
    Redirect("dashboard");
}

echo $twig->render('manage_global_filters.twig', [
    "config" => $config,
    "global_filters" => $Posts->GetGlobalFilters(),
]);