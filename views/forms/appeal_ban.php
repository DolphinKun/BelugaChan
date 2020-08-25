<?php
$Bans = new Bans($config, $dbh);
// Salted IP
$ip = $_POST["ip"];
// Appeal reason
$reason = $_POST["appeal_reason"];
if($Bans->CheckIfBanWasAppealed($ip)) {
    Redirect("error?error=BAN_ALREADY_APPEALED");
    die();
}
if(trim($ip) == '' || trim($reason) == '') {
    Redirect("error?error=INVALID_INPUT");
    die();
}
$Bans->AppealBan($ip, $reason);
Redirect("");
die();