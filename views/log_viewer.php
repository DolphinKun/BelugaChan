<?php
// Declare Twig
$loader = new \Twig\Loader\FilesystemLoader($config["templates_dir"]);
$twig = new \Twig\Environment($loader);
// This will be checked by ReadLog to stop reading of files like config.php
$log_file = $_GET["log"];
$log_contents = $Logs->ReadLog($log_file);
if(!$log_contents) {
    header("Location: ${config["access_point"]}error?error=BAD_LOG");
    die();
}
echo $twig->render('log_viewer.twig', [
    "config" => $config,
    "log" => $Logs->ReadLog($log_file),
    "log_file" => $log_file
]);