<?php

// Declare Twig
$loader = new \Twig\Loader\FilesystemLoader($config["templates_dir"]);
$twig = new \Twig\Environment($loader, ["debug" => true]);
$json_decode = new \Twig\TwigFilter('json_decode', function ($string) {
    return json_decode($string);
});
$twig->addFilter($json_decode);
$twig->addExtension(new \Twig\Extension\DebugExtension());
$username = $_SESSION["username"];

$board_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$threads_per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : $config["threads_per_page"];
$thread_pagination = ($board_page > 1) ? ($board_page * $threads_per_page) - $threads_per_page : 0;

if (LOGGED_IN) {
    if ($Boards->UserIsVol($username, $board) || $Boards->CheckIfUserOwnsBoard($board, $username)) {
        $vol = true;
    }
} else {
    $vol = false;
}
if (!$_SESSION["authed_boards"]) {
    $_SESSION["authed_boards"] = [];
}
$board_config = $Boards->GetBoardConfig($board);
if ($board_config["password"] && !in_array($board, $_SESSION["authed_boards"])) {
    $password = $_POST["password"];
    if (isset($_POST["auth_btn"]) && password_verify($password, $board_config["password"])) {
        $_SESSION["authed_boards"][] = $board;
        Redirect($board);
    } elseif (isset($_POST["auth_btn"])) {
        $error = "BAD_PASSWORD";
    }
    echo $twig->render('board_password.twig', [
        "config" => $config,
        "board" => $board,
        "error" => $error,
    ]);
}
$random_password = bin2hex(random_bytes(5));
if ($thread) {
    if (!$Posts->CheckIfThreadExists($thread)) {
        header("Location: ${config["access_point"]}error?error=THREAD_DOES_NOT_EXIST");
        die();
    }
    $show_reply_box = true;
    echo $twig->render('thread.twig', [
        "config" => $config,
        "board" => $board,
        "board_info" => $Boards->GetBoardInfo($board),
        "board_config" => $board_config,
        "thread" => $Boards->GetThreads($board, $thread),
        "replies" => $Posts->GetThreadReplies($thread),
        "show_reply_box" => true,
        "vol" => $vol,
        "random_password" => $random_password,
        "random_banner" => $Boards->GetRandomBoardBanner($board),
    ]);
} else {
    $get_replies = new \Twig\TwigFilter('get_replies', [$Posts, 'GetPreviewRepliesForThread']);
    $twig->addFilter($get_replies);
    $threads = $Boards->GetThreads($board, null, [$thread_pagination, $threads_per_page]);
    $paginated_pages = ceil($threads[1] / $threads_per_page);
    for($x = 1; $x <= $paginated_pages; $x++):
        $pages[] = $x;
    endfor;

    echo $twig->render('board.twig', [
        "config" => $config,
        "board" => $board,
        "board_info" => $Boards->GetBoardInfo($board),
        "threads" => $threads[0],
        "replies" => $Posts->GetThreadReplies($thread),
        "show_reply_box" => false,
        "vol" => $vol,
        "random_password" => $random_password,
        "random_banner" => $Boards->GetRandomBoardBanner($board),
        "paginated_pages" => $pages,
        "per_page" => $threads_per_page,
    ]);
}