<?php
require 'vendor/autoload.php';
include "config.php";
include "lib/database.php";
include "lib/PostsWebSockets.php";


$loop = React\EventLoop\Factory::create();
$pusher = new PostsWebSockets($config, $dbh);
$context = new React\ZMQ\Context($loop);
$pull = $context->getSocket(ZMQ::SOCKET_PULL);
$pull->bind('tcp://127.0.0.1:' . $config["mzq_port"]);
$pull->on('message', array($pusher, 'SendData'));

$webSock = new React\Socket\Server($config["websocket_host"] . ":" . $config["websocket_port"], $loop);
$webServer = new Ratchet\Server\IoServer(
    new Ratchet\Http\HttpServer(
        new Ratchet\WebSocket\WsServer(
            new Ratchet\Wamp\WampServer(
                $pusher
            )
        )
    ),
    $webSock
);

$loop->run();