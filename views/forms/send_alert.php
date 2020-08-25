<?php
$board = $_POST["board"];
$message = htmlspecialchars($_POST["message"]);

if (!LOGGED_IN) {
    Redirect("login");
}
if ($Boards->CheckIfUserOwnsBoard($board, $username) || $Login->CheckUserRole($board, $username) == "admin") {
    $Boards->SendMessage($message, $board);
}
Redirect("dashboard");