<?php

$blacklisted_ips = [

];
if(in_array($_SERVER['REMOTE_ADDR'], $blacklisted_ips)) {
    die("BLACKLISTED");
}
$message = base64_decode($_GET["message"]);
$board = $_GET["board"];
if($_GET["action"] == "decrypt" && isset($message) && isset($board)) {
    foreach(range(1, 100) as $num) {
        if(openssl_decrypt($message,"AES-128-ECB",$board . "_" . $num)) {
            die($board . "_" . $num);
        }
    }
}


die("OK");