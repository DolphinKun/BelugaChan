<?php

class SystemStatus
{
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function GetStatus()
    {
        // This array will store the error codes
        $errors_found = [];
        // Check PHP memory limit
        $memory_limit = ini_get('memory_limit');
        preg_match('/^(\d+)(.)$/', $memory_limit, $matches);
        if ($matches[1] < 512) {
            $errors_found["memory_limit"] = $matches[1];
        }
        // Check SQL user password length
        if ($this->config["database_type"] !== "sqlite3" && strlen($this->config["database_password"]) < 6) {
            $errors_found["sql_password"] = true;
        }
        // Check if sqlite is being used
        if ($this->config["database_type"] == "sqlite3") {
            $errors_found["sqlite"] = true;
        }
        // Check if geoIP country DB exists
        if ($this->config["allow_country_flags"] && !is_file($this->config["geoip_database"])) {
            $errors_found["geoip_file"] = true;
        }
        return $errors_found;
    }

}