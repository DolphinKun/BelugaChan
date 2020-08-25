<?php

class Boards
{

    protected $config, $dbh, $routes, $logs, $cache, $belugacloud;


    public function __construct($config, $dbh, $routes, Logs $logs, Cache $cache = null, BelugaCloud $belugacloud)
    {
        $this->config = $config;
        $this->dbh = $dbh;
        $this->routes = $routes;
        $this->logs = $logs;
        $this->cache = $cache;
        $this->belugacloud = $belugacloud;
    }

    public function rm_dir($src)
    {
        $dir = opendir($src);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                $full = $src . '/' . $file;
                if (is_dir($full)) {
                    $this->rm_dir($full);
                } else {
                    unlink($full);
                }
            }
        }
        closedir($dir);
        rmdir($src);
    }

    public function CheckIfBoardExists($board)
    {
        $exists = $this->cache->GetVariableFromCache("board_exists_${board}", false);
        if ($exists) {
            return true;
        }
        if ($this->config["online_mode"]) {
            return $this->belugacloud->HTTPGet(["action" => "board_exists", "board" => $board]);
        } else {
            $check = $this->dbh->prepare("SELECT id FROM boards WHERE name=:board");
            $check->execute(["board" => $board]);
            if ($check->fetch()) {
                $this->cache->CacheVariable(true, "board_exists_${board}");
                return true;
            } else {
                return false;
            }
        }
    }

    public function GetBoardConfig($board)
    {
        $board_config = $this->cache->GetVariableFromCache("board_config_" . $board, true);
        if ($this->config["cache_store"] && $board_config) {
            return $board_config;
        } else {
            if ($this->config["online_mode"]) {
                $config = json_decode($this->belugacloud->HTTPGet(["action" => "board_config", "board" => $board]), true);
            } else {
                $get_config = $this->dbh->prepare("SELECT * FROM board_config WHERE name=:name");
                $get_config->execute(["name" => $board]);
                $config = $get_config->fetch();
            }
            $this->cache->CacheVariable($config, "board_config_${board}", true);
        }
        return $config;
    }

    public function GetThreads($board, $thread = null, $pagination = null)
    {
        if ($thread) {
            if ($this->config["online_mode"]) {
                return json_decode($this->belugacloud->HTTPGet(["action" => "get_thread_info", "id" => $thread, "board" => $board]), true);
            } else {
                $get_thread = $this->dbh->prepare("SELECT * FROM posts WHERE posts.board=:board AND posts.id=:thread");
                $get_thread->execute(["board" => $board, "thread" => (int)$thread]);
            }
            return $get_thread->fetch(PDO::FETCH_ASSOC);
        } elseif ($pagination) {
            if ($this->config["database_type"] == "mysql") {
                $threads = $this->dbh->prepare("SELECT SQL_CALC_FOUND_ROWS * FROM posts WHERE posts.board=:board AND posts.type='thread' ORDER BY pinned DESC, reply_count DESC LIMIT :start_page, :threads_per_page");
            }
            if (!$this->config["online_mode"]) {
                if (isset($threads)) {
                    $threads->execute(["board" => $board, "start_page" => $pagination[0], "threads_per_page" => $pagination[1]]);
                } else {
                    $threads = $this->dbh->prepare("SELECT * FROM posts WHERE posts.board=:board AND posts.type='thread' ORDER BY pinned DESC, reply_count DESC LIMIT :start_page, :threads_per_page");
                    $threads->execute(["board" => $board, "start_page" => $pagination[0], "threads_per_page" => $pagination[1]]);
                }
                $all_threads = $threads->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $all_threads = json_decode($this->belugacloud->HTTPGet(["action" => "paginated_threads", "start_page" => $pagination[0], "threads_per_page" => $pagination[1], "board" => $board]), true);
            }
            // Pagination array
            // 0 = start page
            // 1 = threads to show per page
            if ($this->config["database_type"] == "mysql") {
                $total = $this->dbh->query("SELECT FOUND_ROWS() as total")->fetch()['total'];
            } elseif (!$this->config["online_mode"]) {
                $get_total = $this->dbh->prepare("SELECT COUNT(id) FROM posts WHERE board=:board AND type='thread'");
                $get_total->execute(["board" => $board]);
                $total = $get_total->fetchColumn();
            } else {
                $total = (int)$this->belugacloud->HTTPGet(["action" => "total_thread_count", "board" => $board]);
            }
            return [$all_threads, $total];
        } else {
            if ($this->config["online_mode"]) {
                return json_decode($this->belugacloud->HTTPGet(["action" => "get_all_threads", "board" => $board]), true);
            } else {
                $threads = $this->dbh->prepare("SELECT * FROM posts WHERE posts.board=:board AND posts.type='thread' ORDER BY pinned DESC, reply_count DESC, post_date DESC");
                $threads->execute(["board" => $board]);
                return $threads->fetchAll(PDO::FETCH_ASSOC);
            }
        }
    }

    public function GetBoardsOwnedByUser($username)
    {
        if ($this->config["online_mode"]) {
            $boards_found = json_decode($this->belugacloud->HTTPGet(["action" => "get_boards_owned_by_user", "username" => $username]), true);
        } else {
            $get_boards = $this->dbh->prepare("SELECT * FROM boards WHERE owner=:username");
            $get_boards->execute(["username" => $username]);
            $boards_found = $get_boards->fetchAll();
        }
        return $boards_found;
    }

    public function CheckIfUserOwnsBoard($board, $username)
    {
        if ($this->GetBoardInfo($board)["owner"] == $username) {
            return true;
        }
        return false;
    }

    public function GetBoardInfo($board)
    {
        $board_info = $this->cache->GetVariableFromCache("board_info_${board}", true);
        if ($board_info) {
            return $board_info;
        }
        if (!$this->config["online_mode"]) {
            $get_board_info = $this->dbh->prepare("SELECT * FROM boards WHERE name=:name");
            $get_board_info->execute(["name" => $board]);
            $board_info = $get_board_info->fetch();
        } else {
            $board_info = json_decode($this->belugacloud->HTTPGet(["action" => "board_info", "board" => $board]), true);
        }
        $this->cache->CacheVariable($board_info, "board_info_${board}", true);
        return $board_info;
    }

    public function UpdateConfig($board, $board_locked, $board_owner, $board_theme, $board_css, $enable_country_flags, $hide_board, $enable_ids, $board_password)
    {
        if ($board_password && $board_password !== $this->GetBoardConfig($board_password)["password"]) {
            $board_password = password_hash($board_password, PASSWORD_DEFAULT);
        }
        $config_data = [
            "locked" => $board_locked,
            "theme" => $board_theme,
            "board_name" => $board,
            "enable_country_flags" => $enable_country_flags,
            "enable_ids" => (int)$enable_ids,
            "password" => $board_password,
        ];
        if ($this->config["online_mode"]) {
            $online_data[] = $config_data;
            $online_data["action"] = "update_board_config";
            $status = $this->belugacloud->HTTPPost($online_data);
        } else {
            $update_config = $this->dbh->prepare("UPDATE board_config SET locked=:locked, theme=:theme, country_flags_enabled=:enable_country_flags, enable_ids=:enable_ids, password=:password WHERE name=:board_name");
            $update_config->execute($config_data);
        }
        // Update CSS
        $update_css = fopen("css/boards/" . $board . ".css", "w");
        $css_header = str_replace("#board", $board, $this->config["custom_css_header"]);
        fwrite($update_css, $css_header . "\n" . $board_css);
        fclose($update_css);
        // Update owner and hidden status
        if ($this->config["online_mode"]) {
            $this->belugacloud->HTTPPost([
                "action" => "update_board_info",
                "owner" => $board_owner,
                "board_name" => $board,
                "hide_board" => $hide_board
            ]);
        } else {
            $update_owner = $this->dbh->prepare("UPDATE boards SET owner=:owner, hidden=:hide_board WHERE name=:board_name");
            $update_owner->execute(["owner" => $board_owner, "board_name" => $board, "hide_board" => $hide_board]);
        }
        $this->cache->DeleteVariableFromCache("board_config_${board}");
        $this->cache->CacheVariable($this->GetBoardConfig($board), "board_config_${board}", true);
        return true;
    }

    public function UserIsVol($username, $board)
    {
        if ($this->config["online_mode"]) {
            $vol = $this->belugacloud->HTTPGet(["action" => "check_if_board_vol", "username" => $username, "board" => $board]);
            $role = $this->belugacloud->HTTPGet(["action" => "get_user_role", "username" => $username]);
        } else {
            $check_if_vol = $this->dbh->prepare("SELECT * FROM board_vols WHERE username=:username AND name=:board");
            $check_if_vol->execute(["board" => $board, "username" => $username]);
            $vol = $check_if_vol->fetch();
            // for gvol
            $get_role = $this->dbh->prepare("SELECT role FROM users WHERE username=:username");
            $get_role->execute(["username" => $username]);
            $role = $get_role->fetch()["role"];
        }
        if ($vol) {
            return "vol";
        } elseif ($role == "gvol") {
            return "gvol";
        }
        return false;
    }

    public function GetAllBoards($show_hidden = null)
    {
        if ($show_hidden) {
            if (!$this->config["online_mode"]) {
                $get_all_boards = $this->dbh->query("SELECT * FROM boards")->fetchAll();
            } else {
                $get_all_boards = json_decode($this->belugacloud->HTTPGet(["action" => "get_all_boards", "hidden" => false]), true);
            }
        } else {
            if (!$this->config["online_mode"]) {
                $get_all_boards = $this->dbh->query("SELECT * FROM boards WHERE hidden=false OR hidden IS NULL")->fetchAll();
            } else {
                $get_all_boards = json_decode($this->belugacloud->HTTPGet(["action" => "get_all_boards", "hidden" => true]), true);
            }
        }
        return $get_all_boards;
    }

    public function GetBoardTotalPostCount($board)
    {
        $get_count = $this->dbh->prepare("SELECT COUNT(id) FROM posts WHERE board=:board");
        $get_count->execute(["board" => $board]);
        return $get_count->fetchColumn();
    }

    public function CreateBoard($board, $subtitle, $owner)
    {
        if (in_array($board, $this->routes)) {
            return false;
        }
        if ($this->config["online_mode"]) {
            $this->belugacloud->HTTPPost([
                "action" => "create_board",
                "name" => $board,
                "subtitle" => $subtitle,
                "owner" => $owner
            ]);
            $this->belugacloud->HTTPPost([
                "action" => "new_configuration_board",
                "name" => $board,
                "theme" => $this->config["default_theme"],
            ]);
        } else {
            $create_board = $this->dbh->prepare("INSERT INTO boards (name, subtitle, owner) VALUES (:name, :subtitle, :owner)");
            $create_board->execute(["name" => $board, "subtitle" => $subtitle, "owner" => $owner]);
            // Add to the board_config table
            $config_board = $this->dbh->prepare("INSERT INTO board_config (name, custom_css_enabled, theme, locked) VALUES (:name, 1, :theme, 0)");
            $config_board->execute(["name" => $board, "theme" => $this->config["default_theme"]]);
        }
        // If custom css is enabled, create board CSS file
        if ($this->config["custom_css_allowed"]) {
            $empty_file = str_replace("#board", $board, $this->config["custom_css_header"]);
            $create_file = fopen("css/boards/" . $board . ".css", "wb");
            fwrite($create_file, $empty_file);
            fclose($create_file);
        }
        $this->logs->AddLogEntry("User ${owner} created /${board}/", false, true);
        return true;
    }

    public function DeleteBoard($board)
    {
        if ($this->config["online_mode"]) {
            $this->belugacloud->HTTPPost(["action" => "delete_board", "board" => $board]);
        } else {
            $delete_posts = $this->dbh->prepare("DELETE FROM posts WHERE board=:board");
            $delete_posts->execute(["board" => $board]);
            // Delete from boards table now
            $delete_board = $this->dbh->prepare("DELETE FROM boards WHERE name=:board");
            $delete_board->execute(["board" => $board]);
            // Delete board config
            $delete_board_config = $this->dbh->prepare("DELETE FROM board_config WHERE name=:board");
            $delete_board_config->execute(["board" => $board]);
        }
        if (is_dir($this->config["file_dir"] . $board)) {
            $this->rm_dir($this->config["file_dir"] . $board);
        }
        if (is_file("css/boards/" . $board . ".css")) {
            unlink("css/boards/" . $board . ".css");
        }
        $this->logs->AddLogEntry("Board /${board}/ was deleted", false, true);
        // Update cache!
        $this->cache->DeleteVariableFromCache("board_exists_${board}");
        $this->cache->DeleteVariableFromCache("board_config_${board}");
        $this->cache->DeleteVariableFromCache("board_info_${board}");

        return true;
    }

    public function BoardUserCount($username)
    {
        if ($this->config["online_mode"]) {
            $count = $this->belugacloud->HTTPGet(["action" => "board_user_count", "username" => $username]);
        } else {
            $get_count = $this->dbh->prepare("SELECT count(name) FROM boards WHERE owner=:username");
            $get_count->execute(["username" => $username]);
            $count = $get_count->fetchColumn();
        }
        return $count;
    }

    public function AddVol($board, $vol_username)
    {
        if ($this->config["online_mode"]) {
            $this->belugacloud->HTTPGet(["action" => "add_vol", "board" => $board, "username" => $vol_username]);
        } else {
            $add_vol = $this->dbh->prepare("INSERT INTO board_vols (name, username) VALUES (:board, :username)");
            $add_vol->execute(["board" => $board, "username" => $vol_username]);
        }
        $this->logs->AddLogEntry("VOL ${vol_username} was added to /${board}/", false, true);
        return true;
    }

    public function GetVols($board)
    {
        if ($this->config["online_mode"]) {
            $all_vols = json_decode($this->belugacloud->HTTPGet(["action" => "get_vols", "board" => $board]), true);
        } else {
            $vols = $this->dbh->prepare("SELECT username FROM board_vols WHERE name=:board");
            $vols->execute(["board" => $board]);
            $all_vols = $vols->fetchAll();
        }
        return $all_vols;
    }

    public function RemoveVol($board, $vol_username)
    {
        if ($this->config["online_mode"]) {
            $this->belugacloud->HTTPGet(["action" => "remove_vol", "username" => $vol_username, "board" => $board]);
        } else {
            $delete_vol = $this->dbh->prepare("DELETE FROM board_vols WHERE username=:username AND name=:board");
            $delete_vol->execute(["username" => $vol_username, "board" => $board]);
        }
        $this->logs->AddLogEntry("VOL ${vol_username} was removed from /${board}/", false, true);
        return true;
    }

    public function GetBoardCSS($board)
    {
        // Get CSS content from file
        $get_css = fopen("css/boards/" . $board . ".css", "r");
        $css_content = fread($get_css, filesize("css/boards/" . $board . ".css"));
        fclose($get_css);
        return $css_content;
    }

    public function GetAllVolunteeredBoards($username)
    {
        if ($this->config["online_mode"]) {
            $boards = json_decode($this->belugacloud->HTTPGet(["action" => "get_all_voled_boards", "username" => $username]), true);
        } else {
            $get_boards = $this->dbh->prepare("SELECT name FROM board_vols WHERE username=:username");
            $get_boards->execute(["username" => $username]);
            $boards = $get_boards->fetchAll();
        }
        return $boards;
    }

    public function GetAllBans($board)
    {
        if ($this->config["online_mode"]) {
            $bans = json_decode($this->belugacloud->HTTPGet(["action" => "get_all_bans", "board" => $board]), true);
        } else {
            $get_bans = $this->dbh->prepare("SELECT ip, reason, appeal_reason, date_banned FROM bans WHERE board=:board");
            $get_bans->execute(["board" => $board]);
            $bans = $get_bans->fetchAll();
        }
        return $bans;
    }

    public function GetRandomBoardBanner($board)
    {
        if ($this->config["database_type"] == "sqlite3") {
            $get_banner = $this->dbh->prepare("SELECT image FROM board_banners WHERE board=:board ORDER BY RANDOM() LIMIT 0,1");
        } elseif ($this->config["database_type"] == "mysql") {
            $get_banner = $this->dbh->prepare("SELECT image FROM board_banners WHERE board=:board ORDER BY RAND() LIMIT 0,1");
        } elseif ($this->config["online_mode"]) {
            $banner = json_decode($this->belugacloud->HTTPGet(["action" => "board_banner", "board" => $board]));
        }
        if (!$this->config["online_mode"]) {
            $get_banner->execute(["board" => $board]);
            $banner = $get_banner->fetch();
        }
        return $banner;
    }

    public function GetAllBanners($board)
    {
        if ($this->config["online_mode"]) {
            $banners = json_decode($this->belugacloud->HTTPGet(["action" => "get_all_banners", "board" => $board]), true);
        } else {
            $get_banners = $this->dbh->prepare("SELECT id, image FROM board_banners WHERE board=:board");
            $get_banners->execute(["board" => $board]);
            $banners = $get_banners->fetchAll();
        }
        return $banners;
    }

    public function AddBanner($board, $banner_image)
    {
        if ($this->config["online_mode"]) {
            $this->belugacloud->HTTPPost(["action" => "add_banner", "board" => $board, "image" => $banner_image]);
        } else {
            $add_banner = $this->dbh->prepare("INSERT INTO board_banners (image, board) VALUES (:image, :board)");
            $add_banner->execute(["board" => $board, "image" => $banner_image]);
        }
        return true;
    }

    public function CheckIfBannerIsForBoard($board, $banner_id)
    {
        if ($this->BannerInfo($banner_id)["board"] == $board) {
            return true;
        }
        return false;
    }

    public function BannerInfo($banner_id)
    {
        if ($this->config["online_mode"]) {
            $info = json_decode($this->belugacloud->HTTPGet(["action" => "banner_info", "id" => $banner_id]), true);
        } else {
            $get_info = $this->dbh->prepare("SELECT image, board FROM board_banners WHERE id=:id");
            $get_info->execute(["id" => $banner_id]);
            $info = $get_info->fetch();
        }
        return $info;
    }

    public function DeleteBanner($banner_id)
    {
        $banner_info = $this->BannerInfo($banner_id);
        if (is_file($this->config["file_dir"] . $banner_info["board"] . "/banners/" . $banner_info["image"])) {
            unlink($this->config["file_dir"] . $banner_info["board"] . "/banners/" . $banner_info["image"]);
        }
        if ($this->config["online_mode"]) {
            $this->belugacloud->HTTPGet(["action" => "delete_banner", "id" => $banner_id]);
        } else {
            $delete_banner_from_database = $this->dbh->prepare("DELETE FROM board_banners WHERE id=:id");
            $delete_banner_from_database->execute(["id" => $banner_id]);
        }
        return true;
    }

    public function BoardPPH($board)
    {
        switch ($this->config["database_type"]) {
            case "mysql":
                $get_pph = $this->dbh->prepare("SELECT count(id) FROM posts WHERE board=:board AND post_date > UNIX_TIMESTAMP() - 3600");
                break;
            case "sqlite3":
                $get_pph = $this->dbh->prepare("SELECT count(id) FROM posts WHERE board=:board AND post_date > strftime('%s', 'now') - 3600");
                break;
        }
        if (!$this->config["online_mode"]) {
            $get_pph->execute(["board" => $board]);
            $pph = $get_pph->fetch(PDO::FETCH_NUM)[0];
        } else {
            $pph = $this->belugacloud->HTTPGet(["action" => "get_pph", "board" => $board]);
        }
        if (!$pph) {
            $pph = 0;
        }
        return (int)$pph;
    }

    public function UpdatePPH($board)
    {
        $pph = $this->BoardPPH($board);
        if ($this->config["online_mode"]) {
            $this->belugacloud->HTTPGet(["action" => "update_pph", "board" => $board, "pph" => $pph]);
        } else {
            $update_pph = $this->dbh->prepare("UPDATE boards SET pph=:pph WHERE name=:board");
            $update_pph->execute(["pph" => $pph, "board" => $board]);
        }
        return true;
    }

    public function BoardTimeStamp($board, $output_format)
    {
        switch ($this->config["database_type"]) {
            case "mysql":
                $get_timestamp = $this->dbh->prepare("SELECT post_date FROM posts WHERE board=:board AND post_date > UNIX_TIMESTAMP() - 86400 ORDER BY post_date DESC LIMIT 0,1");
                break;
            case "sqlite3":
                $get_timestamp = $this->dbh->prepare("SELECT post_date FROM posts WHERE board=:board AND post_date > strftime('%s', 'now') - 86400 ORDER BY post_date DESC LIMIT 0,1");
                break;
        }
        if ($this->config["online_mode"]) {
            $timestamp = $this->belugacloud->HTTPGet(["action" => "board_timestamp", "board" => $board]);
        } else {
            $get_timestamp->execute(["board" => $board]);
            $timestamp = $get_timestamp->fetch(PDO::FETCH_NUM)[0];
        }
        if (!$timestamp) {
            $timestamp = time();
        }
        $timestamp = new DateTime("@" . $timestamp);
        switch ($output_format) {
            case "atom":
                $timestamp = $timestamp->format(DATE_ATOM);
                break;
            case "iso8601":
                $timestamp = $timestamp->format(DATE_ISO8601);
                break;
            default:
                $timestamp = null;
                break;
        }
        return $timestamp;
    }

    public function SendMessage($message, $board)
    {
        $message_data = [
            "message" => $message,
            "board" => $board,
            "action" => $board . "_message",
        ];
        $message_data = json_encode($message_data);
        $context = new ZMQContext();
        $socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'BoardMessage');
        $socket->connect("tcp://localhost:" . $this->config["mzq_port"]);
        $socket->send($message_data);
    }

    public function AddBoardEmote($emote_name, $emote_url, $board)
    {
        if ($this->config["online_mode"]) {
            $this->belugacloud->HTTPPost(["action" => "add_emote",
                "emote_name" => $emote_name,
                "emote_url" => $emote_url,
                "board" => $board]);
        } else {
            $add_emote = $this->dbh->prepare("INSERT INTO emotes (name, url, board) VALUES (:name, :url, :board)");
            $add_emote->execute(["name" => $emote_name, "url" => $emote_url, "board" => $board]);
        }
    }

    public function GetAllBoardEmotes($board, $pagination = null)
    {
        if ($pagination) {
            if ($this->config["online_mode"]) {
                $all_emotes = json_decode($this->belugacloud->HTTPGet([
                    "action" => "paginated_emotes",
                    "board" => $board,
                    "start_page" => $pagination[0],
                    "emotes_per_page" => $pagination[1]
                ]), true);
            } else {
                $get_emotes = $this->dbh->prepare("SELECT name, url, board FROM emotes WHERE board=:board LIMIT :start_page, :emotes_per_page");
                $get_emotes->execute(["board" => $board, "start_page" => $pagination[0], "emotes_per_page" => $pagination[1]]);
                $all_emotes = $get_emotes->fetchAll(PDO::FETCH_ASSOC);
            }
        } else {
            if ($this->config["online_mode"]) {
                $all_emotes = json_decode($this->belugacloud->HTTPGet([
                    "action" => "get_board_emotes",
                    "board" => $board,
                ]), true);
            } else {
                $get_emotes = $this->dbh->prepare("SELECT name, url, board FROM emotes WHERE board=:board");
                $get_emotes->execute(["board" => $board]);
                $all_emotes = $get_emotes->fetchAll(PDO::FETCH_ASSOC);
            }
        }
        // Count
        if ($this->config["online_mode"]) {
            $emote_count = $this->belugacloud->HTTPGet([
                "action" => "get_board_emote_count",
                "board" => $board,
            ]);
        } else {
            $get_emote_count = $this->dbh->prepare("SELECT count(name) FROM emotes WHERE board=:board");
            $get_emote_count->execute(["board" => $board]);
            $emote_count = $get_emote_count->fetchColumn();
        }
        return [$emote_count, $all_emotes];
    }

    public function CheckIfEmoteExists($name, $board)
    {
        if ($this->config["online_mode"]) {
            $found = $this->belugacloud->HTTPGet([
                "action" => "check_if_emote_exists",
                "name" => $name,
                "board" => $board]);
        } else {
            $check = $this->dbh->prepare("SELECT null FROM emotes WHERE board=:board AND name=:name");
            $check->execute(["board" => $board, "name" => $name]);
            $found = $check->fetch();
        }
        return $found;
    }

    public function DeleteBoardEmote($name, $board)
    {
        if($this->config["online_mode"]) {
            $this->belugacloud->HTTPGet(["action" => "delete_board_emote", "name" => $name, "board" => $board]);
        } else {
            $delete_emote = $this->dbh->prepare("DELETE FROM emotes WHERE name=:name AND board=:board");
            $delete_emote->execute(["name" => $name, "board" => $board]);
        }
    }
}