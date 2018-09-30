<?php
require_once __DIR__.'/vendor/autoload.php';

use Workerman\Worker;
use \Workerman\Connection\AsyncTcpConnection;

$inside_worker = new Worker();


$inside_worker->onWorkerStart = function() use ($inside_worker){

    // Channel客户端连接到Channel服务端
    Channel\Client::connect('47.88.58.78', 2206);

    Channel\Client::on('cs_connect', function($event_data) use($inside_worker){
        echo "cs_connect\n";

        $connection_to_local = new AsyncTcpConnection('tcp://127.0.0.1:80');

        
        $connection_to_local->onConnect = function($connection){
            // $connect_data['session'] = $_SESSION;
            $connect_data['connection'] = [
                'ip'=>$connection->getRemoteIp(),
                'port'=>$connection->getRemotePort()
            ];
            Channel\Client::publish('sc_connect',$connect_data);
            echo "sc_connect\n";
        };

        $connection_to_local->onMessage = function($connection,$data){
            // $message_data['session'] = $_SESSION;
            $message_data['data'] = $data;
            $message_data['connection'] = [
                'ip'=>$connection->getRemoteIp(),
                'port'=>$connection->getRemotePort()
            ];

            Channel\Client::publish('sc_message',$message_data);
            echo "sc message\n";
        };

        $connection_to_local->onClose = function($connection){
            // $close_data['session'] = $_SESSION;
            $close_data['connection'] = [
                'ip'=>$connection->getRemoteIp(),
                'port'=>$connection->getRemotePort()
            ];
            
            Channel\Client::publish('sc_close',$close_data);
            echo "sc close\n";
        };
        
        $connection_to_local->connect();


        $inside_worker->connections[$event_data['connection']['ip'].$event_data['connection']['port']] = $connection_to_local;
        
    });
    Channel\Client::on('cs_message',function($event_data)use($inside_worker){
        echo "cs_message\n";
        $inside_worker->connections[$event_data['connection']['ip'].$event_data['connection']['port']]->send($event_data['data']);
    });
    Channel\Client::on('cs_close',function($event_data)use($inside_worker){
        echo "cs_close\n";
        $inside_worker->connections[$event_data['connection']['ip'].$event_data['connection']['port']]->close();
    });
};


if(!defined('GLOBAL_START'))
{
    Worker::runAll();
}