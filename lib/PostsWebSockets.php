<?php

use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;

class PostsWebSockets implements WampServerInterface
{
    protected $config, $dbh;
    public $clients;
    protected $subscribedTopics = [];

    public function __construct($config, $dbh)
    {
        $this->config = $config;
        $this->dbh = $dbh;
        $this->clients = new \SplObjectStorage;
    }

    public function onSubscribe(ConnectionInterface $conn, $topic)
    {
        $this->subscribedTopics[$topic->getId()] = $topic;
    }

    public function onUnSubscribe(ConnectionInterface $conn, $topic)
    {
        // TODO: Implement onUnSubscribe() method.
    }

    public function onCall(ConnectionInterface $conn, $id, $topic, array $params)
    {
        $conn->callError($id, $topic, 'You are not allowed to make calls')->close();
    }

    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible)
    {
        $conn->close();
    }

    public function onOpen(ConnectionInterface $conn)
    {
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $from->send("Sorry, messaging is not allowed!");
    }

    public function onClose(ConnectionInterface $conn)
    {
        $conn->close();
    }

    public function SendData($reply_data)
    {
        $reply_data = json_decode($reply_data, true);
        if (!isset($reply_data['action'])) {
            return;
        }
        if ($reply_data["thread_id"]) {
            $topic = $this->subscribedTopics[$reply_data['thread_id']];
        } elseif($reply_data["action"]) {
            $topic = $this->subscribedTopics[$reply_data['action']];
        }
        if($topic) {
            $topic->broadcast($reply_data);
        }
        return;
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
    }
}