<?php
try {
    switch ($config["database_type"]) {
        case "sqlite3":
            $dbh = new PDO("sqlite:" . $config["database_file"]);
            break;
        case "mysql":
            $dbh = new PDO("mysql:host=" . $config["database_host"] . ";dbname=" . $config["database_name"], $config["database_user"], $config["database_password"]);
            break;
        default:
            die("Please configure a DB in config.php");
    }
    // emulating is bad; caused issues before, so I DO NOT recommending touching this!
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    if ($config["debug_mode"]) {
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
} catch (PDOException $e) {
    die("A database connection error occurred:<br><code>${e}</code>");
}