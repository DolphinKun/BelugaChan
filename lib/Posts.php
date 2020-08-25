<?php

class Posts
{
    protected $config, $dbh, $logs, $login, $postsWebSockets, $securimage, $bans, $belugacloud;

    public function __construct($config, $dbh, Logs $Logs, Login $login, Boards $boards, PostsWebSockets $postsWebSockets, Securimage $Securimage, Bans $bans, BelugaCloud $belugacloud)
    {
        $this->config = $config;
        $this->dbh = $dbh;
        $this->logs = $Logs;
        $this->login = $login;
        $this->boards = $boards;
        $this->postsWebSockets = $postsWebSockets;
        $this->securimage = $Securimage;
        $this->bans = $bans;
        $this->belugacloud = $belugacloud;
    }

    public function CheckIfThreadExists($thread)
    {
        if (!$this->config["online_mode"]) {
            $check = $this->dbh->prepare("SELECT id FROM posts WHERE id=:id AND type='thread'");
            $check->execute(["id" => (int)$thread]);
            if ($check->fetch()) {
                return true;
            }
        } else {
            return $this->belugacloud->HTTPGet(["action" => "thread_exists", "id" => $thread]);
        }
        return false;
    }

    public function GetThreadReplies($thread_id, $api_mode = null)
    {
        if ($api_mode) {
            $replies = $this->dbh->prepare("SELECT id, name, email, subject, message, board, files, post_date FROM posts WHERE thread_id=:id AND type='reply'");
        } elseif (!$this->config["online_mode"]) {
            $replies = $this->dbh->prepare("SELECT * FROM posts WHERE thread_id=:id AND type='reply'");
        } else {
            $all_replies = json_decode($this->belugacloud->HTTPGet(["action" => "thread_replies", "id" => $thread_id]));
        }
        if (!$this->config["online_mode"]) {
            $replies->execute(["id" => (int)$thread_id]);
            $all_replies = $replies->fetchAll(PDO::FETCH_ASSOC);
        }
        return $all_replies ?? false;
    }

    public function MessageParser($message, $board)
    {
        // Stop HTML attacks
        $message = htmlspecialchars($message);
        // Go line by line
        $new_message = "";
        foreach (preg_split("/((\r?\n)|(\r\n?))/", $message) as $message_line) {
            // for replying; essential
            if (strpos($message_line, "&gt;&gt;") !== false) {
                $quote_id = preg_replace('/[^0-9]/', '', $message_line);
                $message_line = '<a href="#' . $quote_id . '">' . $message_line . '</a>';
            }
            foreach ($this->config["text_filters"] as $filter => $filter_value) {
                if (substr($message_line, 0, strlen($filter)) === $filter) {
                    $message_line = sprintf($filter_value, $message_line);
                }
            }
            foreach ($this->GetGlobalFilters() as $filter) {
                if ($filter["ban_on_post"] && strpos($message_line, $filter["filter"]) !== false) {
                    return "GLOBAL_BAN";
                }
                if (@preg_match($filter["filter"], $message_line) === false) {
                    if ($filter["ban_on_post"]) {
                        return "GLOBAL_BAN";
                    }
                    $message_line = str_replace($filter["filter"], $filter["result"], $message_line);
                } else {
                    $message_line = preg_replace($filter["filter"], $filter["result"], $message_line);
                }
            }
            foreach ($this->GetBoardFilters($board) as $filter) {
                if ($filter["ban_on_post"] && strpos($message_line, $filter["filter"]) !== false) {
                    return "BOARD_BAN";
                }
                if (@preg_match($filter["filter"], $message_line) === false) {
                    if ($filter["ban_on_post"]) {
                        return "BOARD_BAN";
                    }
                    $message_line = htmlspecialchars(str_replace($filter["filter"], $filter["result"], $message_line));
                } else {
                    $message_line = htmlspecialchars(preg_replace($filter["filter"], $filter["result"], $message_line));
                }
            }
            // Emotes
            if ($this->config["emotes_enabled"]) {
                foreach ($this->boards->GetAllBoardEmotes($board)[1] as $emote) {
                    $message_line = str_replace($emote["name"], sprintf($this->config["emote_template"], $emote["url"], $emote["name"], $emote["name"]), $message_line);
                }
            }
            foreach ($this->config["global_style_filters"] as $filter => $filter_value) {
                $message_line = preg_replace($filter, $filter_value, $message_line);
            }
            $new_message .= $message_line . "\n";
        }
        return nl2br($new_message);

    }

    public function CreateThread($name = null, $email = null, $subject = null, $message, $board, $files = null, $password, $salted_ip, $country_iso = null)
    {
        // Parse message through parser!
        $message = $this->MessageParser($message, $board);
        if (!$this->AddMessageBan($message, $board, $salted_ip)) {
            return "BAN";
        }
        $password = password_hash($password, PASSWORD_DEFAULT);
        if ($this->config["online_mode"]) {
            $thread_id = $this->belugacloud->HTTPPost([
                "action" => "create_thread",
                "name" => $name,
                "email" => $email,
                "subject" => $subject,
                "message" => $message,
                "board" => $board,
                "files" => $files,
                "post_date" => time(),
                "password" => $password,
                "ip" => $salted_ip,
                "country_iso" => $country_iso
            ]);
        } else {
            $add_thread = $this->dbh->prepare("INSERT INTO posts (name, email, subject, message, board, type, files, post_date, password, ip, country_iso) VALUES (:name, :email, :subject, :message, :board, 'thread', :files, :post_date, :password, :ip, :country_iso)");
            $add_thread->execute(["name" => $name, "email" => $email, "subject" => $subject, "message" => $message, "board" => $board, "files" => $files, "post_date" => time(), "password" => $password, "ip" => $salted_ip, "country_iso" => $country_iso]);
            $thread_id = $this->dbh->lastInsertId();
        }
        return $thread_id;
    }

    public function UpdateThreadReplyCount($thread_id, $remove = null)
    {
        if ($this->config["online_mode"]) {
            $reply_count = $this->belugacloud->HTTPGet(["action" => "get_thread_reply_count", "id" => $thread_id]);
        } else {
            $get_count = $this->dbh->prepare("SELECT reply_count FROM posts WHERE type='thread' AND id=:id");
            $get_count->execute(["id" => $thread_id]);
            $reply_count = $get_count->fetch()["reply_count"];
        }
        if ($remove) {
            $count = (int)$reply_count - 1;
        } else {
            $count = (int)$reply_count + 1;
        }
        // Update count now
        if ($this->config["online_mode"]) {
            $this->belugacloud->HTTPGet(["action" => "update_reply_post_count", "count" => $count, "id" => $thread_id]);
        } else {
            $update_count = $this->dbh->prepare("UPDATE posts SET reply_count=:reply_count WHERE id=:id");
            $update_count->execute(["id" => $thread_id, "reply_count" => $count]);
        }
        return true;
    }

    public function PushReplyToWebSocket($reply_data)
    {
        $context = new ZMQContext();
        $socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'Reply');
        $socket->connect("tcp://localhost:" . $this->config["mzq_port"]);
        $socket->send($reply_data);
    }

    public function AddMessageBan($message, $board, $salted_ip)
    {
        switch ($message) {
            case "BOARD_BAN":
                if ($this->config["online_mode"]) {
                    $this->belugacloud->HTTPPost(["action" => "ban_ip", "board" => $board, "reason" => false, "ip" => $salted_ip]);
                } else {
                    $ban = $this->dbh->prepare("INSERT INTO bans (ip, reason, board) VALUES (:ip, false, :board)");
                    $ban->execute(["ip" => $salted_ip, "board" => $board]);
                }
                return false;
                break;
            case "GLOBAL_BAN":
                if ($this->config["online_mode"]) {
                    $this->belugacloud->HTTPGet(["action" => "global_ban_ip", "ip" => $salted_ip, "reason" => false]);
                } else {
                    $ban = $this->dbh->prepare("INSERT INTO bans (ip, reason, is_global) VALUES (:ip, false, true)");
                    $ban->execute(["ip" => $salted_ip]);
                }
                return false;
                break;
        }
        return true;
    }

    public function ReplyToThread($thread_id, $name = null, $email = null, $subject = null, $message, $board, $files = null, $password, $salted_ip, $country_iso = null)
    {
        // Parse message through parser!
        $message = $this->MessageParser($message, $board);
        if (!$this->AddMessageBan($message, $board, $salted_ip)) {
            return "BAN";
        }
        $password = password_hash($password, PASSWORD_DEFAULT);
        if ($this->config["online_mode"]) {
            $id = $this->belugacloud->HTTPPost([
                "action" => "reply_to_thread",
                "thread_id" => $thread_id,
                "name" => $name,
                "email" => $email,
                "subject" => $subject,
                "message" => $message,
                "board" => $board,
                "files" => $files,
                "post_date" => time(),
                "password" => $password,
                "ip" => $salted_ip,
                "country_iso" => $country_iso
            ]);
        } else {
            $add_thread = $this->dbh->prepare("INSERT INTO posts (name, email, subject, message, board, type, files, post_date, thread_id, password, ip, country_iso) VALUES (:name, :email, :subject, :message, :board, 'reply', :files, :post_date, :thread_id, :password, :ip, :country_iso)");
            $add_thread->execute(["thread_id" => $thread_id, "name" => $name, "email" => $email, "subject" => $subject, "message" => $message, "board" => $board, "files" => $files, "post_date" => time(), "password" => $password, "ip" => $salted_ip, "country_iso" => $country_iso]);
            $id = $this->dbh->lastInsertId();
        }
        // Update thread reply count
        $this->UpdateThreadReplyCount($thread_id);
        switch ($this->config["auto_updating_method"]) {
            case "websockets":
                $reply_data = [
                    "thread_id" => $thread_id,
                    "id" => $id,
                    "name" => $name,
                    "email" => $email,
                    "subject" => $subject,
                    "message" => $message,
                    "board" => $board,
                    "files" => $files,
                    "post_date" => date("Y-m-d", time()),
                    "country_iso" => $country_iso,
                    "poster_id" => substr($salted_ip, 30, 11),
                    "action" => "reply",
                ];
                $reply_data = json_encode($reply_data);
                $this->PushReplyToWebSocket($reply_data);
                break;
        }
        return true;
    }

    public function CheckPostBeforePosting($board_info, $message, $poster_name, $file_count, $board, $Boards, $files = null)
    {

        if ($board_info["locked"]) {
            header("Location: " . $this->config["access_point"] . "error?error=BOARD_LOCKED");
            die();
        }
        // Check to see if everything is just whitespace.
        if (trim($poster_name) == '' || trim($message == '') && !$files) {
            header("Location: " . $this->config["access_point"] . "error?error=INVALID_INPUT");
            die();
        }
        // If captcha is enabled, check captcha
        if ($this->config["captcha_enabled"] && $this->securimage->check($_POST['captcha_code']) == false) {
            header("Location: " . $this->config["access_point"] . "error?error=BAD_CAPTCHA");
            die();
        }
        // Check if file uploading is allowed and that the file count doesn't exceed the max limit
        if ($file_count > $this->config["max_file_count"]) {
            header("Location: " . $this->config["access_point"] . "error?error=MAX_FILE_SIZE");
            die();
        }
        // Check if board exists first
        if (!$this->boards->CheckIfBoardExists($board)) {
            header("Location: " . $this->config["access_point"] . "error?error=INVALID_INPUT");
            die();
        }
    }

    public function UseTrip($poster_name, $board, $username)
    {
        $trip = explode("##", $poster_name);
        if (substr($trip[1], 0, 1) == "#") {
            $trip = explode("###", $poster_name);
        } elseif ($trip[0] == $poster_name) {
            $trip = explode("#", $poster_name);
        }
        switch ($trip[1]) {
            case "admin":
                if ($this->login->CheckUserRole($board, $username) == "admin") {
                    $name = "## Administrator";
                }
                break;
            case "boardowner":
                if ($this->boards->CheckIfUserOwnsBoard($board, $username)) {
                    $name = "## Board Owner";
                }
                break;
            case "gvol":
                if ($this->boards->UserIsVol($username, $board) == "gvol") {
                    $name = "## Global Volunteer";
                }
                break;
            case "vol":
                if ($this->boards->UserIsVol($username, $board) == "vol") {
                    $name = "## Board Volunteer";
                }
                break;
        }
        if (!$name) {
            // SHA512-based ### ultra-secure trip
            if (strpos($poster_name, '###') !== false) {
                $hashed_trip = hash("sha512", $trip[1]);
                $name = $trip[0] . "###" . substr($hashed_trip, 0, 14);
                return $name;
            }
            // SHA1-based ## secure trip
            if (strpos($poster_name, '##') !== false) {
                $hashed_trip = sha1($trip[1]);
                $name = $trip[0] . "##" . substr($hashed_trip, 0, 14);
                return $name;
            }
            // MD5 insecure # trip
            if (strpos($poster_name, '#') !== false) {
                $hashed_trip = md5($trip[1]);
                $name = $trip[0] . "#" . substr($hashed_trip, 0, 14);
                return $name;
            }
        }
        if (!$name) {
            $name = $poster_name;
        }

        return $name;
    }

    public function GetThreadIDFromPost($post_id)
    {
        if($this->config["online_mode"]) {
            $thread_id = $this->belugacloud->HTTPGet(["action" => "get_thread_id", "id" => $post_id]);
        } else {
            $get_thread_id = $this->dbh->prepare("SELECT thread_id FROM posts WHERE id=:id");
            $get_thread_id->execute(["id" => $post_id]);
            $thread_id = $get_thread_id->fetch()["thread_id"];
        }
        if (!$thread_id) {
            $thread_id = $post_id;
        }
        return $thread_id;
    }

    public function GetPostInfo($post_id, $api_mode = null)
    {
        if ($api_mode) {
            if($this->config["online_mode"]) {
                $post_info = json_decode($this->belugacloud->HTTPGet(["action" => "api_post_info", "id" => $post_id]), true);
            } else {
                $post_info = $this->dbh->prepare("SELECT id, name, message, files, pinned, locked FROM posts WHERE id=:id");
                $post_info->execute(["id" => $post_id]);
                $post_info = $post_info->fetch(PDO::FETCH_ASSOC);
            }
        } else {
            if($this->config["online_mode"]) {
                $post_info = json_decode($this->belugacloud->HTTPGet(["action" => "post_info", "id" => $post_id]), true);
            } else {
                $post_info = $this->dbh->prepare("SELECT id, name, ip, files, country_iso, pinned, locked FROM posts WHERE id=:id");
                $post_info->execute(["id" => $post_id]);
                $post_info = $post_info->fetch(PDO::FETCH_ASSOC);
            }
        }
        return $post_info;
    }

    public function UpdatePostCount($board, $add = null)
    {
        if ($this->config["online_mode"]) {
            $count = $this->belugacloud->HTTPGet(["action" => "get_board_post_count", "board" => $board]);
        } else {
            $get_count = $this->dbh->prepare("SELECT post_count FROM boards WHERE name=:board");
            $get_count->execute(["board" => $board]);
            $count = $get_count->fetch()["post_count"];
        }
        if (!(int)$count || $count == -1) {
            $count = 0;
        }
        if ($add) {
            $count = $count + 1;
        } else {
            $count = $count - 1;
        }
        if ($this->config["online_mode"]) {
            $this->belugacloud->HTTPGet(["action" => "update_board_post_count", "count" => $count, "board" => $board]);
        } else {
            $update_count = $this->dbh->prepare("UPDATE boards SET post_count=:count WHERE name=:board");
            $update_count->execute(["board" => $board, "count" => (int)$count]);
        }
    }

    public function DeleteThreadReplies($thread_id)
    {
        $replies = $this->GetThreadReplies($thread_id);
        foreach ($replies as $reply) {
            $delete = $this->dbh->prepare("DELETE FROM posts WHERE thread_id=:id");
            $delete->execute(["id" => $reply["thread_id"]]);
            $this->UpdatePostCount($reply["board"]);
            // Delete files
            $files = json_decode($reply["files"], true);
            foreach ($files as $file) {
                $file_path = $this->config["file_dir"] . $reply["board"] . "/" . $file["file_name"];
                if (is_file($file_path)) {
                    unlink($file_path);
                }
                $thumbnail_path = $this->config["file_dir"] . $reply["board"] . "/" . $file["thumbnail"];
                if (is_file($thumbnail_path)) {
                    unlink($thumbnail_path);
                }
            }
        }
    }

    public function DeletePostFiles($post_id, $board)
    {
        $files = json_decode($this->GetPostInfo($post_id)["files"], true);
        foreach ($files as $file) {
            $file_path = $this->config["file_dir"] . $board . "/" . $file["file_name"];
            if (is_file($file_path)) {
                unlink($file_path);
            }
            $thumbnail_path = $this->config["file_dir"] . $board . "/" . $file["thumbnail"];
            if (is_file($thumbnail_path)) {
                unlink($thumbnail_path);
            }
        }
    }

    public function DeletePost($board, $post_id, $password = null)
    {
        $thread_id = $this->GetThreadIDFromPost($post_id);
        if ($password) {
            $hashed_password = $this->GetPostInfo($post_id)["password"];
            // Check password now
            if (password_verify($password, $hashed_password)) {
                // Delete files
                $this->DeletePostFiles($post_id, $board);

                if ($this->CheckIfThreadExists($post_id)) {
                    $this->DeleteThreadReplies($post_id);
                }

                if ($post_id !== $thread_id) {
                    if ($this->config["auto_updating_method"] == "websockets") {
                        $this->PushReplyToWebSocket(json_encode(["id" => $post_id, "thread_id" => $thread_id, "action" => "delete"]));
                    }
                    $this->UpdateThreadReplyCount($thread_id, 1);
                }
                if($this->config["online_mode"]) {
                    $this->belugacloud->HTTPGet(["action" => "delete_post", "board" => $board, "id" => $post_id]);
                } else {
                    $delete = $this->dbh->prepare("DELETE FROM posts WHERE board=:board AND id=:id");
                    $delete->execute(["board" => $board, "id" => $post_id]);
                }
                return true;
            }
        } else {
            $this->DeletePostFiles($post_id, $board);

            if ($post_id !== $thread_id) {
                if ($this->config["auto_updating_method"] == "websockets") {
                    $this->PushReplyToWebSocket(json_encode(["id" => $post_id, "thread_id" => $thread_id, "action" => "delete"]));
                }
                $this->UpdateThreadReplyCount($thread_id, 1);
            }
            if ($this->CheckIfThreadExists($post_id)) {
                $this->DeleteThreadReplies($post_id);
            }
            if($this->config["online_mode"]) {
                $this->belugacloud->HTTPGet(["action" => "delete_post", "board" => $board, "id" => $post_id]);
            } else {
                $delete = $this->dbh->prepare("DELETE FROM posts WHERE board=:board AND id=:id");
                $delete->execute(["board" => $board, "id" => $post_id]);
            }
            return true;
        }
        return false;
    }

    public function GetRecentPosts()
    {
        if ($this->config["online_mode"]) {
            return json_decode($this->belugacloud->HTTPGet(["action" => "recent_posts", "limit" => $this->config["recent_post_limit"]]));
        } else {
            $posts = $this->dbh->prepare("SELECT id, thread_id, board, message, files FROM posts ORDER BY id DESC LIMIT 0,:limit");
            $posts->execute(["limit" => $this->config["recent_post_limit"]]);
            return $posts->fetchAll();
        }
    }

    public function GetIPFromPostID($post_id)
    {
        return $this->GetPostInfo($post_id)["ip"];
    }

    public function CheckIfUserIsBanned($ip, $board)
    {
        if ($this->config["online_mode"]) {
            return json_decode($this->belugacloud->HTTPGet([
                "action" => "check_if_user_is_banned",
                "board" => $board,
                "ip" => $ip,
            ]), true);
        } else {
            $check = $this->dbh->prepare("SELECT is_global, reason FROM bans WHERE ip=:ip AND board=:board OR is_global=true");
            $check->execute(["board" => $board, "ip" => $ip]);
            $ban = $check->fetch();
            if ($ban) {
                return [$ban["reason"], $ban["is_global"]];
            }
        }
        return false;
    }

    public function BanIP($post_id, $board, $reason)
    {
        $ip = $this->GetIPFromPostID($post_id);
        foreach ($this->config["unbannable_ips"] as $unbannable_ip) {
            $unbannable_ip = crypt($unbannable_ip, $this->config["salt"]);
            if ($ip === $unbannable_ip) {
                return false;
            }
        }
        // Ban user now
        if ($this->config["online_mode"]) {
            $this->belugacloud->HTTPPost(["action" => "ban_ip", "board" => $board, "reason" => $reason, "ip" => $ip]);
        } else {
            $add_ban = $this->dbh->prepare("INSERT INTO bans (ip, board, reason, date_banned) VALUES (:ip, :board, :reason, :date_banned)");
            $add_ban->execute(["ip" => $ip, "board" => $board, "reason" => $reason, "date_banned" => time()]);
        }
        $this->logs->AddLogEntry("BO/VOL banned user by IP from /${board}/", $board);
        return true;
    }

    public function LiftIPBan($board, $ip)
    {
        if ($this->config["online_mode"]) {
            $this->belugacloud->HTTPGet(["action" => "lift_ban", "board" => $board, "ip" => $ip]);
        } else {
            $remove_ip = $this->dbh->prepare("DELETE FROM bans WHERE board=:board AND ip=:ip");
            $remove_ip->execute(["board" => $board, "ip" => $ip]);
        }
        $this->logs->AddLogEntry("BO/VOL lifted IP ban for /${board}/", $board);
        return true;
    }

    public function PinThread($post_id, $pin = null)
    {
        if ($this->CheckIfThreadExists($post_id)) {
            if ($this->config["online_mode"]) {
                $this->belugacloud->HTTPGet(["action" => "pin_thread", "id" => $post_id, "pin" => $pin]);
            } else {
                $pin_thread = $this->dbh->prepare("UPDATE posts SET pinned=:pin WHERE id=:id");
                $pin_thread->execute(["id" => $post_id, "pin" => $pin]);
            }
        }
        return false;
    }

    public function LockThread($post_id, $lock = null)
    {
        if ($this->CheckIfThreadExists($post_id)) {
            if ($this->config["online_mode"]) {
                $this->belugacloud->HTTPGet(["action" => "lock_thread", "id" => $post_id, "lock" => $lock]);
            } else {
                $pin_thread = $this->dbh->prepare("UPDATE posts SET locked=:lock WHERE id=:id");
                $pin_thread->execute(["id" => $post_id, "lock" => $lock]);
            }
        }
        return false;
    }

    public function GetPreviewRepliesForThread($thread)
    {
        if ($this->config["online_mode"]) {
            $replies = json_decode($this->belugacloud->HTTPGet(["action" => "preview_replies", "id" => $thread, "limit" => $this->config["maximum_preview_replies"]]), true);
        } else {
            $replies = $this->dbh->prepare("SELECT * FROM posts WHERE thread_id=:id AND type='reply' LIMIT 0,:maximum_posts");
            $replies->execute(["id" => $thread, "maximum_posts" => $this->config["maximum_preview_replies"]]);
            $replies = $replies->fetchAll();
        }
        return $replies;
    }

    public function GetGlobalFilters()
    {
        if ($this->config["online_mode"]) {
            return json_decode($this->belugacloud->HTTPGet(["action" => "get_global_filters"]), true);
        } else {
            $get_filters = $this->dbh->query("SELECT ban_on_post, filter, result FROM filters WHERE is_global=true");
            return $get_filters->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    public function GetBoardFilters($board)
    {
        if ($this->config["online_mode"]) {
            return json_decode($this->belugacloud->HTTPGet(["action" => "get_board_filters", "board" => $board]), true);
        } else {
            $get_filters = $this->dbh->prepare("SELECT filter, result, ban_on_post FROM filters WHERE board=:board");
            $get_filters->execute(["board" => $board]);
            return $get_filters->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    public function DeleteGlobalFilter($filter_name)
    {
        if ($this->config["online_mode"]) {
            $this->belugacloud->HTTPGet(["action" => "delete_global_filter", "name" => $filter_name]);
        } else {
            $delete_filter = $this->dbh->prepare("DELETE FROM filters WHERE filter=:filter AND is_global=true");
            $delete_filter->execute(["filter" => $filter_name]);
        }
    }

    public function DeleteBoardFilter($filter_name, $board)
    {
        if ($this->config["online_mode"]) {
            $this->belugacloud->HTTPGet(["action" => "delete_board_filter", "name" => $filter_name, "board" => $board]);
        } else {
            $delete_filter = $this->dbh->prepare("DELETE FROM filters WHERE filter=:filter AND board=:board");
            $delete_filter->execute(["filter" => $filter_name, "board" => $board]);
        }
    }

    public function AddGlobalFilter($filter_text, $replacement_text, $ban_on_post)
    {
        if ($this->config["online_mode"]) {
            $this->belugacloud->HTTPGet(["action" => "add_global_filter", "text" => $filter_text, "replacement_text" => $replacement_text, "ban_on_post" => $ban_on_post]);
        } else {
            $add_filter = $this->dbh->prepare("INSERT INTO filters (filter, result, is_global, ban_on_post) VALUES (:filter_text, :replacement_text, true, :ban_on_post)");
            $add_filter->execute(["filter_text" => $filter_text, "replacement_text" => $replacement_text, "ban_on_post" => $ban_on_post]);
        }
        return true;
    }

    public function AddBoardFilter($filter_text, $replacement_text, $board, $ban_on_post)
    {
        if ($this->config["online_mode"]) {
            $this->belugacloud->HTTPGet(["action" => "add_board_filter", "text" => $filter_text, "replacement_text" => $replacement_text, "board" => $board, "ban_on_post" => $ban_on_post]);
        } else {
            $add_filter = $this->dbh->prepare("INSERT INTO filters (filter, result, board, ban_on_post) VALUES (:filter_text, :replacement_text, :board, :ban_on_post)");
            $add_filter->execute(["filter_text" => $filter_text, "replacement_text" => $replacement_text, "board" => $board, "ban_on_post" => $ban_on_post]);
        }
        return true;
    }

    public function GetUserIP()
    {
        if ($this->config["enable_reverse_proxy"]) {
            $user_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $user_ip = $_SERVER['REMOTE_ADDR'];
        }
        $salted_ip = crypt($user_ip, $this->config["salt"]);
        return [$salted_ip, $user_ip];
    }

}