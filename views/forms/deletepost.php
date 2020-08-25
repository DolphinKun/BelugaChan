<?php
// Processes deletion of posts (threads, replies, etc)

$board = $_GET["board"];
$post_id = $_GET["id"];
$username = $_SESSION["username"];
$password = $_GET["password"];
if($Boards->CheckIfUserOwnsBoard($board, $username) || $Boards->UserIsVol($username, $board)) {
    $Posts->DeletePost($board, $post_id);
    $Posts->UpdatePostCount($board);
    header("Location: " . $config["access_point"] . $board . "/");
    die();
} elseif(isset($_GET["delete_btn"])) {
    if(trim($password) == '') {
        Redirect("error?error=BAD_PASSWORD");
        die();
    }
    if($Posts->DeletePost($board, $post_id, $password)) {
        $Posts->UpdatePostCount($board);
        header("Location: " . $config["access_point"] . $board . "/");
        die();
    } else {
        Redirect("error?error=BAD_PASSWORD");
        die();
    }
}

header("Location: ${config["access_point"]}${board}");
die();