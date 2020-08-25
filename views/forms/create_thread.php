<?php
// For GeoIP
use GeoIp2\Database\Reader;
// Poster name
$poster_name = isset($_POST["name"]) ? $_POST["name"] : $config["default_poster_name"];
// Subject name
$subject_name = $_POST["subject_name"];
// Email
$email = filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL);
// Message
$message = $_POST["message"];
// File count
$file_count = count($_FILES['files']['name']);
// Get board
$board = $_POST["board"];
$poster_name = $Posts->UseTrip($poster_name, $board, $username);
// Get password
$password = isset($_POST["password"]) ? $_POST["password"] : bin2hex(random_bytes(5));
// Declare Upload class
$Upload = new Upload($config, $dbh, $BelugaCloud);
// Check if user pressed the create post button first
if (isset($_POST["submit_post"])) {
    // Get board info
    $board_info = $Boards->GetBoardConfig($board);

    $files = $Upload->MoveFile($_FILES, $board, $file_count);
    $Posts->CheckPostBeforePosting($board_info, $message, $poster_name, $file_count, $board, $Boards, $files);
    $salted_ip = $Posts->GetUserIP();
    $ban_info = $Posts->CheckIfUserIsBanned($salted_ip[0], $board);
    if ($ban_info[0] == "SHADOW_BAN") {
        header("Location: " . $config["access_point"] . $board . "/");
        die();
    }
    if ($ban_info) {
        Redirect("error?error=BANNED&reason=" . $ban_info[0] . "&is_global=" . $ban_info[1] . "&board=" . $board);
    }
    // If country flags are enabled for the board, get ISO name
    if ($board_info["country_flags_enabled"] && $config["allow_country_flags"] && is_file($config["geoip_database"])) {
        // Start GeoIP reader
        $reader = new Reader($config["geoip_database"]);
        try {
            $record = $reader->country($salted_ip[1]);
            $country_iso = strtolower($record->country->isoCode);
        } catch (GeoIp2\Exception\AddressNotFoundException $exception) {
            $country_iso = null;
        }
    }
    $thread_id = $Posts->CreateThread($poster_name, $email, $subject_name, $message, $board, $files, $password, $salted_ip[0], $country_iso);
    if($thread_id == "BAN") {
        $ban_info = $Posts->CheckIfUserIsBanned($salted_ip[0], $board);
        if ($ban_info) {
            Redirect("error?error=BANNED&reason=" . $ban_info[0] . "&is_global=" . $ban_info[1] . "&board=" . $board);
        }
    }
    $Posts->UpdatePostCount($board, 1);
    header("Location: " . $config["access_point"] . $board . "/" . $thread_id);
    die();


}
