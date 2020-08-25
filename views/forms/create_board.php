<?php
// This file is responsible for creating the boards
$board = $_POST["board"];
$board = preg_replace('/[^\w]/', '', $board);
$subtitle = $_POST["subtitle"];
$owner = $_SESSION["username"];
if(!LOGGED_IN) {
    Redirect("login");
}

if(isset($_POST["create_board_button"])) {
    if ($config["captcha_enabled"] && $Securimage->check($_POST['captcha_code']) == false) {
        Redirect("error?error=BAD_CAPTCHA");
        die();
    }
    if($Boards->BoardUserCount($username) > $config["max_boards_per_account"]) {
        Redirect("error?error=BOARD_LIMIT");
        die();
    }
    if(trim($board) == '' || trim($owner) == '') {
        Redirect("error?error=INVALID_INPUT");
        die();
    }
    if(in_array($board, $config["blacklisted_board_names"]) || $Boards->CheckIfBoardExists($board)) {
        Redirect("error?error=BOARD_EXISTS");
        die();
    }
    if($Boards->CreateBoard($board, $subtitle, $owner)) {
        Redirect("dashboard");
    } else {
        Redirect("error?error=BOARD_EXISTS");
        die();
    }
}