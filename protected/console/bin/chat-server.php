<?php
require __DIR__ . '/../../components/WebSocketServer.php';

$ws = new WebSocketServer('192.168.111.10', '9818');
$ws->run();