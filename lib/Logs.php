<?php

class Logs
{
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }
    public function AddLogEntry($message, $board = null, $global = null) {
        if(empty($this->config["logs_folder"]) || !$this->config["enable_logging"]) {
            return false;
        }
        $date = date("Y-m-d");
        if($global) {
            file_put_contents($this->config["logs_folder"] . $date . "_global.txt", $message . "\r\n", FILE_APPEND);
        }
        if($board) {
            file_put_contents($this->config["logs_folder"] . $date . "_${board}.txt", $message . "\r\n", FILE_APPEND);
        }
        return true;
    }
    public function GetAllGlobalLogs() {
        $logs = glob($this->config["logs_folder"] . "*_global.txt");
        // fast way of sorting files in ascending order, e.g. 2020-06-02 through to 2020-06-06 as top item
        return array_reverse($logs);
    }
    public function ReadLog($file) {
        $all_logs = glob($this->config["logs_folder"] . "*.txt");
        if(in_array($this->config["logs_folder"] . $file, $all_logs)) {
            return file_get_contents($this->config["logs_folder"] . $file);
        } else {
            return false;
        }
    }
    public function GetAllBoardLogs($board) {
        $logs = glob($this->config["logs_folder"] . "*_" . $board . ".txt");
        // fast way of sorting files in ascending order, e.g. 2020-06-02 through to 2020-06-06 as top item
        return array_reverse($logs);
    }
}