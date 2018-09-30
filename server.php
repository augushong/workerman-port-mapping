<?php
require_once __DIR__.'/vendor/autoload.php';

use Workerman\Worker;

$channel_server = new Channel\Server('0.0.0.0', 2206);

$outside_worker = new Worker('tcp://0.0.0.0:888');

$outside_worker->onWorkerStart = function(){
    // Channel客户端连接到Channel服务端
    Channel\Client::connect('127.0.0.1', 2206);

};

$outside_worker->onConnect = function($connection){

    // $connection_data['session'] = $_SESSION;
    $connection_data['connection'] = [
        'ip'=>$connection->getRemoteIp(),
        'port'=>$connection->getRemotePort()
    ];


    Channel\Client::publish('cs_connect', $connection_data);
    echo "cs_connect\n";

    Channel\Client::on('sc_message',function($event_data) use ($connection){
        echo "sc_message\n";
        var_dump($event_data['data']);
        $connection->send($event_data['data']);
    });
    Channel\Client::on('sc_close',function($event_data) use ($connection){
        echo "sc_close\n";
        $connection->close();
    });
    Channel\Client::on('sc_connect',function($event_data) use($connection){
        echo "sc_connect\n";

    });
    
    $connection->onMessage = function($connection, $data){
        // $message_data['session'] = $_SESSION;
        $message_data['connection'] = [
            'ip'=>$connection->getRemoteIp(),
            'port'=>$connection->getRemotePort()
        ];
        $message_data['data'] = $data;

        Channel\Client::publish('cs_message', $message_data);
        echo "cs_message\n";
    };
    
    $connection->onClose = function ($connection){
    
        // $close_data['session'] = $_SESSION;
        $close_data['connection'] = [
            'ip'=>$connection->getRemoteIp(),
            'port'=>$connection->getRemotePort()
        ];
    
        Channel\Client::publish('cs_close', $close_data);
        echo "cs_close\n";
    };

};



if(!defined('GLOBAL_START'))
{
    Worker::runAll();
}
