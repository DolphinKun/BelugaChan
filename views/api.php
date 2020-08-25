<?php

$action = $_GET["action"];
$id = (int)$_GET["id"];

switch ($action) {
    case "thread":
        if ($id) {
            $thread_info = $Posts->GetPostInfo($id, true);
            if (!$thread_info) {
                die("THREAD_DOES_NOT_EXIST");
            }
            header("Content-type: application/json");
            echo json_encode($thread_info);
        } else {
            echo "INVALID_THREAD";
        }
        break;
    case "thread_replies":
        $id = (int)$_GET["id"];
        $thread_info = $Posts->GetThreadReplies($id, true);
        if ($id) {
            if (!$Posts->CheckIfThreadExists($id)) {
                die("THREAD_DOES_NOT_EXIST");
            }
            header("Content-type: application/json");
            echo json_encode($thread_info);
        } else {
            echo "INVALID_THREAD";
        }
        break;
    default:
        echo "NO_ACTION_SPECIFIED";
        break;
}