<?php
// Run this every hour!
include "config.php";
include "autoload.php";
header("Content-Type: text/plain");
if(!$config["webcron"] && php_sapi_name() !== 'cli') {
    die("Run as cron only.");
}

function HTTPRequest($url) {
    global $config;
    if(!empty($config["webring_proxy"])) {
    $main_context = array(
        'http' => array(
            'proxy'           => $config["webring_proxy"],
            'request_fulluri' => true,
        ),
    );
    } else {
        $main_context = [];
    }
    $context = stream_context_create($main_context);

    return file_get_contents($url, False, $context);
}
echo "Updating PPH\r\n";
$Logs = new Logs($config);
$BelugaCloud = new BelugaCloud($config);
$Cache = new Cache($config);
$Boards = new Boards($config, $dbh, $routes, $Logs, $Cache, $BelugaCloud);
$boards = $Boards->GetAllBoards(true);
foreach($boards as $board) {
    $Boards->UpdatePPH($board["name"]);
}

if($config["webring_enabled"]) {
    $webring_json = [];
    $webring_json["name"] = $config["site_name"];
    $webring_json["url"] = $config["website_url"];
    $webring_json["endpoint"] = $config["website_url"] . $config["webring_filename"];
    echo "Updating webring file!\r\n";
    for ($i = 0; $i < count($config["webring_follow_sites"]); $i++) {
        $webring_json["following"][$i] = $config["webring_follow_sites"][$i];
    }
    for ($i = 0; $i < count($config["webring_known_sites"]); $i++) {
        $webring_json["known"][$i] = $config["webring_known_sites"][$i];
    }
    for ($i = 0; $i < count($config["webring_blacklist_sites"]); $i++) {
        $webring_json["blacklist"][$i] = $config["webring_blacklist_sites"][$i];
    }
    foreach($config["webring_follow_sites"] as $site) {
        $json = json_decode(HTTPRequest($site), true);
        foreach($json["known"] as $known_site) {
            if(!in_array($known_site, $webring_json["blacklist"])) {
                $webring_json["known"][] = $known_site;
            }
        }
    }
    $boards = $Boards->GetAllBoards();
    foreach($boards as $board) {
        $webring_json["boards"][] = [
            "uri" => $board["name"],
            "title" => $board["subtitle"],
            "subtitle" => $board["subtitle"],
            "path" => $config["website_url"] . $board["name"] . "/",
            "postsPerHour" => $Boards->BoardPPH($board["name"]),
            "totalPosts" => (int)$Boards->GetBoardTotalPostCount($board["name"]),
            "uniqueUsers" => 0, // sorry, not adding more code to this codebase!
            "nsfw" => false,
            "lastPostTimestamp" => $Boards->BoardTimeStamp($board["name"], "atom"),
        ];
    }
// Let's save the file now
    $webring_json = json_encode($webring_json);
    file_put_contents($config["webring_filename"], $webring_json);
}

echo "Done.\r\n";