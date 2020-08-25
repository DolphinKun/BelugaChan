<?php
// Declare Twig
$loader = new \Twig\Loader\FilesystemLoader($config["templates_dir"]);
$twig = new \Twig\Environment($loader);
$board = filter_input(INPUT_GET, "board", FILTER_SANITIZE_STRING);
if ($board) {
    $logs = $Logs->GetAllBoardLogs($board);
} else {
    $logs = $Logs->GetAllGlobalLogs();
}
echo $twig->render('log.twig', [
    "config" => $config,
    "logs" => $logs,
]);