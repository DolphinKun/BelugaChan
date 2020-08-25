<?php

class Bans
{

    protected $config, $dbh, $belugacloud;


    public function __construct($config, $dbh, BelugaCloud $belugacloud)
    {
        $this->config = $config;
        $this->dbh = $dbh;
        $this->belugacloud = $belugacloud;
    }

    public function CheckIfBanWasAppealed($ip)
    {
        $check = $this->dbh->prepare("SELECT appeal_reason FROM bans WHERE ip=:ip");
        $check->execute(["ip" => $ip]);
        if (!empty($check->fetch()["appeal_reason"])) {
            return true;
        } else {
            return false;
        }
    }

    public function AppealBan($ip, $reason)
    {
        $reason = htmlspecialchars($reason);
        $add_appeal = $this->dbh->prepare("UPDATE bans SET appeal_reason=:appeal_reason WHERE ip=:ip");
        $add_appeal->execute(["appeal_reason" => $reason, "ip" => $ip]);
        return true;
    }

    public function CheckIfFileIsBanned($checksum)
    {
        // TODO: add file banning support
        /*$check = $this->dbh->prepare("SELECT checksum FROM banned_files WHERE checksum=:checksum");
        $check->execute(["checksum" => $checksum]);
        if ($check->fetch()) {
            return true;
        } else {
            return false;
        }*/
        return false;
    }

    public function GetIPFromPost($post_id)
    {
        if ($this->config["online_mode"]) {
            return json_decode($this->belugacloud->HTTPGet(["action" => "post_info", "id" => $post_id]), true)["ip"];
        } else {
            $get_ip = $this->dbh->prepare("SELECT ip FROM posts WHERE id=:id");
            $get_ip->execute(["id" => $post_id]);
        }
        return $get_ip->fetch()["ip"];
    }

    public function GloballyBanIP($post_id, $reason)
    {
        if ($this->config["online_mode"]) {
            $this->belugacloud->HTTPGet(["action" => "global_ban_ip", "ip" => $this->GetIPFromPost($post_id), "reason" => $reason]);
        } else {
            $add_global_ban = $this->dbh->prepare("INSERT INTO bans (ip, reason, date_banned, is_global) VALUES (:ip, :reason, :date, true)");
            $add_global_ban->execute(["ip" => $this->GetIPFromPost($post_id), "reason" => $reason, "date" => time()]);
        }
        return true;
    }

    public function GetAllGlobalBans()
    {
        if ($this->config["online_mode"]) {
            $bans = json_decode($this->belugacloud->HTTPGet(["action" => "get_all_global_bans"]), true);
        } else {
            $get_bans = $this->dbh->query("SELECT * FROM bans WHERE is_global=true");
            $bans = $get_bans->fetchAll();
        }
        return $bans;
    }

    public function LiftGlobalBan($ip)
    {
        if($this->config["online_mode"]) {
            $this->belugacloud->HTTPGet(["action" => "lift_global_ban", "ip" => $ip]);
        } else {
            $lift_ban = $this->dbh->prepare("DELETE FROM bans WHERE ip=:ip AND is_global=true");
            $lift_ban->execute(["ip" => $ip]);
        }
        return true;
    }

    public function GetBanInfo($ip, $board)
    {
        $ban_info = $this->dbh->prepare("SELECT reason, appeal_reason FROM bans WHERE ip=:ip AND board=:board OR is_global=true");
        $ban_info->execute(["board" => $board, "ip" => $ip]);
        return $ban_info->fetch(PDO::FETCH_ASSOC);
    }
}