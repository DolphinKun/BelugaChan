<?php
$Config = new Config($config);
$username = $_SESSION["username"];
if (!$_SESSION["logged_in"]) {
    Redirect("login");
}

if ($Login->GetUserInfo($username)["role"] !== "admin") {
    Redirect("dashboard");
}

$site_name = isset($_POST["site_name"]) ? $_POST["site_name"] : $config["site_name"];
$access_point = isset($_POST["access_point"]) ? $_POST["access_point"] : $config["access_point"];
$board_creation_open = isset($_POST["board_creation_open"]) ? $_POST["board_creation_open"] : 0;
$custom_css_allowed = isset($_POST["custom_css_allowed"]) ? $_POST["custom_css_allowed"] : 0;
$captcha_enabled = isset($_POST["captcha_enabled"]) ? $_POST["captcha_enabled"] : 0;

if (isset($_POST["update_config"])) {
// Call Update config function to update the config
    $Config->UpdateConfig(
        htmlspecialchars($site_name),
        htmlspecialchars($access_point),
        (int)$board_creation_open,
        (int)$custom_css_allowed,
        (int)$captcha_enabled
        );
Redirect("dashboard");
}
