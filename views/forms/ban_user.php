<?php
// Processes banning of users from certain boards
$board = $_POST["board"];
$post_id = $_POST["id"];
$username = $_SESSION["username"];
$reason = $_POST["reason"];
$delete_post = $_POST["delete_post"];
$shadow_ban = $_POST["shadow_ban"];
if (!LOGGED_IN) {
    Redirect("login");
}
if (isset($_POST["ban_btn"]) && $Boards->CheckIfUserOwnsBoard($board, $username) || $Boards->UserIsVol($username, $board)) {
    if (isset($shadow_ban)) {
        $reason = "SHADOW_BAN";
    }
    if ($Posts->CheckIfUserIsBanned($Bans->GetIPFromPost($post_id), null)) {
        header("Location: ${config["access_point"]}error?error=ALREADY_BANNED");
        die();
    }
    $Posts->BanIP($post_id, $board, $reason);
    if (isset($delete_post)) {
        if ($Posts->CheckIfThreadExists($post_id)) {
            $Posts->DeletePost($board, $post_id);
        }
    }
}
if (isset($_POST["ban_global_btn"]) && ($Boards->UserIsVol($username, null) == "gvol" || $Login->CheckUserRole(null, $username) == "admin")) {
    if (isset($shadow_ban)) {
        $reason = "SHADOW_BAN";
    }
    if ($Posts->CheckIfUserIsBanned($Bans->GetIPFromPost($post_id), null)) {
        header("Location: ${config["access_point"]}error?error=ALREADY_BANNED");
        die();
    }
    $Bans->GloballyBanIP($post_id, $reason);
    if (isset($delete_post)) {
        if ($Posts->CheckIfThreadExists($post_id)) {
            $Posts->DeletePost($board, $post_id);
        }
    }
}
header("Location: " . $config["access_point"] . $board . "/");
die();