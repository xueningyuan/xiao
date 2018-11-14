<?php
use Workerman\Worker;
require_once "../workerman/Autoloader.php";

$worker = new Worker('websocket://0.0.0.0:9999');

$worker->count =1;

// 保存房间的数组
$rooms =[];
// 连接回调函数
$worker->onConnect = function($connection){
    // 为了能够使用 $_GET 接收连接时的参数，我们需要在这里绑定一个 onWebSocketConnect
    // 的回调函数，然后在函数中就可以使用 $_GET 接收参数了
    $connection->onWebSocketConnect = function ($connection,$http_header){
        $connection->room_id = $_GET['room_id'];
        global $rooms;
        $rooms[$_GET['room_id']][] = $connection;
    };
};

// 接受信息
$worker->onMessage = function($connection,$data){
    global $worker,$rooms;
    foreach($rooms[$connection->room_id] as $c){
        $c->send($data);
    }
};

// 连接断开删除数据
$worker->onClose = function($connection){
    global $rooms;
    foreach($rooms[$connection->room_id] as $k=>$c)
    {
        if($c == $connection)
            unset($rooms[$connection->room_id][$k]);
    }
};

Worker::runAll();

