<?php

class Cache
{
    protected $config, $redis;

    public function __construct($config)
    {
        $this->config = $config;
        if ($this->config["redis_host"] && $this->config["redis_port"]) {
            $this->redis = new Redis();
            if (!$this->redis->connect($this->config["redis_host"], $this->config["redis_port"])) {
                die("Redis connection failure.");
            }
        }
    }

    public function CacheVariable($variable, $cache_name, $is_array = null)
    {
        if ($is_array) {
            $variable = serialize($variable);
        }
        switch ($this->config["cache_store"]) {
            case "redis":
                $this->redis->set($cache_name, $variable);
                break;
            case "apcu":
                apcu_add($cache_name, $variable);
                break;
        }
    }

    public function GetVariableFromCache($variable_name, $is_array)
    {
        switch ($this->config["cache_store"]) {
            case "redis":
                $contents = $this->redis->get($variable_name);
                break;
            case "apcu":
                $contents = apcu_fetch($variable_name);
                break;
        }
        if ($is_array) {
            $contents = unserialize($contents);
        }
        return $contents ?: false;
    }

    public function DeleteVariableFromCache($variable_name)
    {
        switch ($this->config["cache_store"]) {
            case "redis":
                $this->redis->del($variable_name);
                break;
            case "apcu":
                apcu_delete($variable_name);
                break;

        }
        return true;
    }

    public function CheckIfVariableIsCached($variable_name)
    {
        switch ($this->config["cache_store"]) {
            case "redis":
                $check = $this->redis->exists($variable_name);
                break;
            case "apcu":
                $check = apcu_exists($variable_name);
                break;
        }
        return $check;
    }
}