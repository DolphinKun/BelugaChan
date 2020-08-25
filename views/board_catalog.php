<?php
// Declare Twig
$loader = new \Twig\Loader\FilesystemLoader($config["templates_dir"]);
$twig = new \Twig\Environment($loader);
$json_decode = new \Twig\TwigFilter('json_decode', function ($string) {
    return json_decode($string);
});
$twig->addFilter($json_decode);
$htmlspecialdecode = new \Twig\TwigFilter('htmlspecialdecode', function ($string) {
   return htmlspecialchars_decode($string);
});
$twig->addFilter($htmlspecialdecode);
echo $twig->render('board_catalog.twig', [
    "config" => $config,
    "board" => $board,
	"board_info" => $Boards->GetBoardInfo($board),
	"board_config" => $Boards->GetBoardConfig($board),
    "threads" => $Boards->GetThreads($board),
	"random_banner" => $Boards->GetRandomBoardBanner($board),
]);