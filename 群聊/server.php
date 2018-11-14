<?php
use Workerman\Worker;
require_once "../workerman/Autoloader.php";

$worker = new Worker('websocket://0.0.0.0:9999');

$worker->count =1;

$worker->onConnect = function($connection){
    $connection->send('欢迎');
};

$worker->onMessage = function($connection,$data){
    global $worker;
    foreach($worker->connections as $c){
        $c->send($data);
    }
};

Worker::runAll();

