<?php

namespace EQT\Wss;

use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;

class PushServer implements WampServerInterface {

    protected $subscribedTopics = [];

    public function __construct() {
        $this->clients = new \SplObjectStorage();
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo sprintf("New Connection with id: %s\n", $conn->resourceId);
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);

        echo "Disconnect from {$conn->resourceId}\n";
    }
    
    public function onSubscribe(ConnectionInterface $conn, $topic) {
        $this->subscribedTopics[$topic->getId()]  = $topic;
    }
    
    public function onUnSubscribe(ConnectionInterface $conn, $topic) {
        // TODO: Implement onUnSubscribe() method.
    }

    public function onCall(ConnectionInterface $conn, $id, $topic, array $params) {
        $conn->callError($id, $topic, 'You are not allowed to make calls')->close();
    }
    
    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible) {
        $conn->close();
    }
    
    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occured {$e->getMessage()}\n";
        $conn->close();
    }

    public function onAction($entry) {
        var_dump($entry);
    }
}
