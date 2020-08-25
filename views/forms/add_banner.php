<?php
// Get board name
$board = $_POST["board"];
// Declare Upload class
$Upload = new Upload($config, $dbh, $BelugaCloud);
// Check if user owns board!
$username = $_SESSION["username"];

if(!$Login->CheckUserRole($board, $username) || !$Login->CheckUserRole($board, $username) == "admin" || !LOGGED_IN) {
    Redirect("");
    die();
}

// Check if user pressed the add banner button first
if (isset($_POST["submit_banner"])) {

    $file = $Upload->UploadBanner($_FILES, $board);
    if (!$file) {
        Redirect("error?error=NO_BANNER");
        die();
    }
    $Boards->AddBanner($board, $file);
    header("Location: " . $config["access_point"] . "manage_banners?board=" . $board);
    die();


}