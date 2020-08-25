<?php
include "config.php";
include "autoload.php";
include "vendor/autoload.php";
include "securimage/securimage.php";
$Securimage = new Securimage();
$Logs = new Logs($config);
$Cache = new Cache($config);
$Boards = new Boards($config, $dbh, $routes, $Logs, $Cache);
$Login = new Login($config, $dbh, $Boards);
$PostsWebSockets = new PostsWebSockets($config, $dbh);
$Bans = new Bans($config, $dbh);
$Posts = new Posts($config, $dbh, $Logs, $Login, $Boards, $PostsWebSockets, $Securimage, $Bans);
echo "Migrating...\n";
$site_url = "https://julay.world";
$boards = [
    "cow" => "cow"
];
$old_posts = [];
function CreateBoardDir($board) {
    global $config;
    if (!is_dir($config["file_dir"] . $board)) {
        mkdir($config["file_dir"] . $board);
        echo "Board dir not found; creating...\n";
    }
}
function AddThreadReplies($new_thread_id, $thread_id, $board, $new_board) {
    global $config;
    global $site_url;
    global $Posts;
    $thread_data = json_decode(file_get_contents($site_url . "/" . $board . "/res/" . $thread_id . ".json"), true);
    foreach($thread_data["posts"] as $post) {
        $files = null;
        if ($post["files"]) {
            foreach ($post["files"] as $file) {
                $thumbnail = explode("/", $file["thumb"])[2];
                $file_name = explode("/", $file["path"])[2];
                $file_ext = explode("/", $file["mime"])[1];
                $files[] = [
                    "original_file_name" => $file["originalName"],
                    "file_name" => $file_name,
                    "file_size" => number_format($file["size"] / 1048576, 2),
                    "thumbnail" => $thumbnail . "." . $file_ext,
                ];
                file_put_contents($config["file_dir"] . $new_board . "/" . $thumbnail . "." . $file_ext, file_get_contents($site_url . $file["thumb"]));
                file_put_contents($config["file_dir"] . $new_board . "/" . $file_name, file_get_contents($site_url . $file["path"]));
            }
        }
        $files = json_encode($files);
        global $old_posts;
        global $dbh;
        $Posts->ReplyToThread($new_thread_id, $post["name"], $post["email"], $post["subject"], $post["message"], $new_board, $files, null, null);
        $old_posts[$dbh->lastInsertId()] = $post["postId"];
        echo "Added thread reply for thread #${new_thread_id}!\n";
    }
}

// Loop through boards
foreach ($boards as $board => $new_board) {
    echo "Migrating ${board}...\n";
    CreateBoardDir($new_board);
    // Get JSON data
    $catalog_data = json_decode(file_get_contents($site_url . "/" . $board . "/catalog.json"), true);
    foreach ($catalog_data as $item) {
        // Import thread
        $thread_data = json_decode(file_get_contents($site_url . "/" . $board . "/res/" . $item["threadId"] . ".json"), true);
        $files = null;
        if ($thread_data["files"]) {
            $files = [];
            foreach ($thread_data["files"] as $file) {
                $thumbnail = explode("/", $file["thumb"])[2];
                $file_name = explode("/", $file["path"])[2];
                $file_ext = explode("/", $file["mime"])[1];
                $files[] = [
                    "original_file_name" => $file["originalName"],
                    "file_name" => $file_name,
                    "file_size" => number_format($file["size"] / 1048576, 2),
                    "thumbnail" => $thumbnail . "." . $file_ext,
                ];
                // Download file
                file_put_contents($config["file_dir"] . $new_board . "/" . $thumbnail . "." . $file_ext, file_get_contents($site_url . $file["thumb"]));
                file_put_contents($config["file_dir"] . $new_board . "/" . $file_name, file_get_contents($site_url . $file["path"]));
            }
        }
        $files = json_encode($files);
        $Posts->CreateThread($config["default_poster_name"], null, $item["subject"], $item["message"], $new_board, $files, null, null);
        $old_posts[$dbh->lastInsertId()] = $item["threadId"];
        $thread_id = $dbh->lastInsertId();
        echo "Added thread #${thread_id}!\n";
        AddThreadReplies($dbh->lastInsertId(), $item["threadId"], $board, $new_board);
    }
}
echo "Fixing >>s now!\n";
$posts = $dbh->query("SELECT message, id FROM posts")->fetchAll();
    foreach($posts as $post) {
        $update = $dbh->prepare("UPDATE posts SET message=:message WHERE id=:id");
        foreach($old_posts as $new_id => $old_id) {
            $message = str_replace($old_id, $new_id, $post["message"]);
        }
        $update->execute(["message" => $message, "id" => $post["id"]]);
}