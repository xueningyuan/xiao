<?php

/*
WebSocket 的服务器端
*/


require_once '../workerman/Autoloader.php';
require('./vendor/autoload.php');
// 实例化 Worker 类对象
use Workerman\Worker;
use Firebase\JWT\JWT;

$worker = new Worker('websocket://0.0.0.0:9999');
// 设置进程数
$worker->count = 1;

// 保存所有的用户
$users = [];
// 定义数组保存用户ID和这个客户端的关系
$userConn = [];

// 绑定连接的回调函数，这个函数会在有客户端连接时调用
// 参数：TcpConnection 类的对象，代表每个客户端
$worker->onConnect = function( $connection ) {
    
    $connection->onWebSocketConnect = function ($connection, $http_header) {
     	
        // 保存当前用户信息
        global $users, $worker,$userConn;
    
        // 解析 JWT 令牌
        try
        {
            $key = 'abcd1234';
            $data = JWT::decode($_GET['token'], $key, array('HS256'));
            // 把ID和name保存到这个连接上， 以区分这个连接是谁
            $connection->uid = $data->id;
            $connection->uname = $data->name;
            // 保存当前连接到所有用户的数组中
            $users[$data->id] = $data->name;
            // 把当前这个客户端的对象保存到数组中，用户ID是下标
            $userConn[$data->id] = $connection;
            // 把用户列表群发给所有客户端
            foreach($worker->connections as $c)
            {
                $c->send(json_encode([
                    'type'=>'users',
                    'users'=>$users,
                ]));
            }

            // 打印解析出来的数据
            // var_dump($data);
        }
        catch(  \Firebase\JWT\ExpiredException $e)
        {
            $connection->close();
        }
        catch( \Exception $e)
        {
            $connection->close();
        }

        
    };

};
// 接收消息
$worker->onMessage = function($connection, $data) {
    global $worker;
    $ret = explode(':',$data);
    $code = $ret[0];
    unset($ret[0]);
    $rawData = implode(':',$ret);
    if($code == 'all'){
        // 循环所有的客户端，给它们发消息
        foreach($worker->connections as $c)
        {
            $c->send(json_encode([
                'type'=>'message',
                'message'=>$data
            ]));
        }
    }
    else
    {
        global $userConn;
        $userConn[$code]->send(json_encode([
            'type'=>'message',
            'message'=>$rawData
        ]));

    }

};

// 当有人断开时调用的回调函数
$worker->onClose = function($connection){
    global $users,$worker;
    unset($users[$connection->uid]);

    foreach($worker->connections as $c)
    {
        $c->send(json_encode([
            'type'=>'users',
            'users'=>$users
        ]));
    }
};
// 运行
Worker::runAll();