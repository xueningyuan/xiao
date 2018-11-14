<?php
require('./vendor/autoload.php');

use Firebase\JWT\JWT;

$pdo = new \PDO('mysql:host=127.0.0.1;dbname=chat','root','123');
$pdo->exec('SET NAMES utf8');

// 接受原始数据
$post = file_get_contents('php://input');

$_POST = json_decode($post,true);

// 连接数据库
$stmt = $pdo->prepare('SELECT * FROM user WHERE username=? AND password =?');
$stmt->execute([
    $_POST['username'],
    md5($_POST['password'])
]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);
if($user)
{
    $key = 'abcd1234';
    $now = time();
    $data = [
        'id'=>$user['id'],
        'name' => $user['username'],
    ]; 
    $jwt = JWT::encode($data, $key);

    echo json_encode([
        'code'=>'200',
        'jwt'=>$jwt,
    ]);
}else{
    // 返回 JSON 数据
    echo json_encode([
        'code'=>'403',
        'error'=>'用户名或者密码错误！',
    ]);
}