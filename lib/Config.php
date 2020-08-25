<?php

class Config
{
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function UpdateConfig(
        $site_name,
        $access_point,
        $board_creation_open,
        $custom_css_allowed,
        $captcha_enabled
    )
    {
        $append_to_config = '
$config["access_point"] = "' . $access_point . '";
$config["site_name"] = "' . $site_name . '";
$config["board_creation_open"] = ' . $board_creation_open . ';
$config["custom_css_allowed"] = ' . $custom_css_allowed . ';
$config["captcha_enabled"] = ' . $captcha_enabled . ';
';
        file_put_contents("config.php", "\n// Web config edit: \n\r" . $append_to_config, FILE_APPEND);
        copy("config.php", "config.backup.php");
        return true;
    }

}