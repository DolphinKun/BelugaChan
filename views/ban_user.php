<?php
// Processes banning of users from certain boards
$board = $_GET["board"];
$username = $_SESSION["username"];
$post_id = $_GET["post_id"];
// Declare Twig
$loader = new \Twig\Loader\FilesystemLoader($config["templates_dir"]);
$twig = new \Twig\Environment($loader);

if(!LOGGED_IN) {
Redirect("login");
}

if ($Login->CheckUserRole($board, $username)) {
    if ($Posts->CheckIfUserIsBanned($Bans->GetIPFromPost($post_id), null)) {
        header("Location: ${config["access_point"]}error?error=ALREADY_BANNED");
        die();
    }
    echo $twig->render('ban_user.twig', [
        "config" => $config,
        "board" => $board,
        "post_id" => $post_id,
        "gvol" => ($Boards->UserIsVol($username, $board) == "gvol" || $Login->CheckUserRole($board, $username) == "admin")
    ]);
}