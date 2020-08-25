<?php

class Login
{
    protected $config, $dbh, $boards, $belugacloud;

    public function __construct($config, $dbh, Boards $boards, BelugaCloud $belugacloud)
    {
        $this->config = $config;
        $this->dbh = $dbh;
        $this->boards = $boards;
        $this->belugacloud = $belugacloud;
    }

    public function CheckLogin($username, $password)
    {
        if ($this->config["online_mode"]) {
            $password_hash = $this->belugacloud->HTTPPost(["action" => "get_login_password", "username" => $username]);
        } else {
            $get_login = $this->dbh->prepare("SELECT password FROM users WHERE username=:username");
            $get_login->execute(["username" => $username]);
            $password_hash = $get_login->fetch()["password"];
        }
        if (password_verify($password, $password_hash)) {
            return true;
        }
        return false;
    }

    public function CheckIfUserExists($username)
    {
        if ($this->config["online_mode"]) {
            if ($this->belugacloud->HTTPPost(["action" => "user_exists", "username" => $username])) {
                return true;
            }
        } else {
            $check = $this->dbh->prepare("SELECT null FROM users WHERE username=:username");
            $check->execute(["username" => $username]);
            if ($check->fetch()) {
                return true;
            }
        }
        return false;
    }

    public function CreateAccount($username, $password)
    {
        $password = password_hash($password, PASSWORD_DEFAULT);
        if ($this->CheckIfUserExists($username) || in_array($username, $this->config["blacklisted_usernames"])) {
            return false;
        }
        if ($this->config["online_mode"]) {
            $this->belugacloud->HTTPPost(["action" => "create_user", "username" => $username, "password" => $password, "role" => "user"]);
        } else {
            $create_account = $this->dbh->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, 'user')");
            $create_account->execute(["username" => $username, "password" => $password]);
        }
        return true;
    }

    public function ChangePassword($username, $old_password, $new_password)
    {
        if ($this->CheckLogin($username, $old_password)) {
            $new_password = password_hash($new_password, PASSWORD_DEFAULT);
            if($this->config["online_mode"]) {
                $this->belugacloud->HTTPPost([
                    "action" => "change_password",
                    "username" => $username,
                    // Overwrites old password, so use the new one!
                    "password" => $new_password,
                ]);
            } else {
                $update_password = $this->dbh->prepare("UPDATE users SET password=:password WHERE username=:username");
                $update_password->execute(["username" => $username, "password" => $new_password]);
            }
            return true;
        }
        return false;
    }

    public function GetUserInfo($username)
    {
        if ($this->config["online_mode"]) {
            $info = json_decode($this->belugacloud->HTTPGet(["action" => "get_user_info", "username" => $username]), true);
        } else {
            $get_info = $this->dbh->prepare("SELECT * FROM users WHERE username=:username");
            $get_info->execute(["username" => $username]);
            $info = $get_info->fetch();
        }
        return $info;
    }

    public function CheckUserRole($board, $username)
    {
        if ($this->boards->CheckIfUserOwnsBoard($board, $username)) {
            return true;
        } elseif ($this->GetUserInfo($username)["role"] == "admin") {
            return "admin";
        } else {
            if ($this->boards->UserIsVol($username, $board)) {
                return "VOL";
            }
        }
        return false;
    }

    public function GetAllUsers()
    {
        if ($this->config["online_mode"]) {
            return json_decode($this->belugacloud->HTTPGet(["action" => "get_all_users"]), true);
        }
        return $this->dbh->query("SELECT username, role FROM users");
    }

    public function UpdateUserRole($username, $user_role)
    {
        if ($this->config["online_mode"]) {
            $this->belugacloud->HTTPPost(["action" => "update_user_role", "username" => $username, "role" => $user_role]);
        } else {
            $update_role = $this->dbh->prepare("UPDATE users SET role=:role WHERE username=:username");
            $update_role->execute(["role" => $user_role, "username" => $username]);
        }
        return true;
    }

    public function DeleteUser($username)
    {
        $boards = $this->boards->GetBoardsOwnedByUser($username);
        foreach ($boards as $board) {
            $this->boards->DeleteBoard($board);
        }
        if($this->config["online_mode"]) {
            $this->belugacloud->HTTPGet(["action" => "delete_user", "username" => $username]);
        } else {
            $delete_user = $this->dbh->prepare("DELETE FROM users WHERE username=:username");
            $delete_user->execute(["username" => $username]);
        }
        return true;
    }
}