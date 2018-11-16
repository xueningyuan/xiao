<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use GatewayClient\Gateway;

class MessageController extends Controller
{
    public function insert(Request $req)
    {
        Message::create([
            'content'=>$req->content,
        ]);
        // 让 GatewayWorker 群发消息
        Gateway::$registerAddress = '127.0.0.1:1238';
        Gateway::sendToAll($req->content);  // 群发消息
    }

    public function index()
    {
        return Message::orderBy('id','asc')->get();
    }
}