<?php

class BelugaCloud
{
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function HTTPGet($data)
    {
            $data["key"] = $this->config["api_key"];
            $data = http_build_query($data);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->config["online_server"] . "?" . $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_USERAGENT, "BelugaChan v" . $this->config["version"]);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_AUTOREFERER, true);
            curl_setopt($ch, CURLOPT_FAILONERROR, true);
            $result = curl_exec($ch);
            if (curl_getinfo($ch) === 0) {
                return false;
            }
            if (curl_error($ch)) {
                return false;
            }
            curl_close($ch);
            if ($result) {
                if(json_decode($result, true)["error"] == "INVALID_KEY") {
                    die("Invalid API key!");
                }
                return $result;
            }
            return false;
    }
    public function HTTPPost($data)
    {
        $data = http_build_query($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->config["online_server"] . "?key=" . $this->config["api_key"]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_USERAGENT, "BelugaChan v" . $this->config["version"]);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        $result = curl_exec($ch);
        if (curl_getinfo($ch) === 0) {
            return false;
        }
        if (curl_error($ch)) {
            return false;
        }
        curl_close($ch);
        if ($result) {
            return $result;
        }
        return false;
    }
}