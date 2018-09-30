<?php
require_once __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/common.php';

use Workerman\Worker;

$channel_server = new Channel\Server('0.0.0.0', 2206);


try{
    $config = get_config();
}catch(\Exception $e){
    echo "error:{$e}\n";
}

if(isset($config['nat_list']) && !is_win()){
    foreach ($config['nat_list'] as $n_key => $n_value) {
        $unique_key = $n_key;
        $nat_client_list['nat_client_worker_'.$n_key] = build_server_woker($n_value);
    }
}else{
    $worker = build_server_woker($config);
}

Worker::runAll();


function build_server_woker($config){

    
    $outside_worker = new Worker('tcp://0.0.0.0:'.$config['server_port']);
    
    $outside_worker->onWorkerStart = function() use ($outside_worker,$config){
        // Channel客户端连接到Channel服务端
        Channel\Client::connect('127.0.0.1', $config['channel_port']);
            
        Channel\Client::on('sc_message'.$config['local_ip'].":".$config['local_port'],function($event_data) use ($outside_worker){
            $outside_worker->connections[$event_data['connection']['c_connection_id']]->send($event_data['data']);
        });

        Channel\Client::on('sc_close'.$config['local_ip'].":".$config['local_port'],function($event_data) use ($outside_worker){
            $outside_worker->connections[$event_data['connection']['c_connection_id']]->close();
        });

        Channel\Client::on('sc_connect'.$config['local_ip'].":".$config['local_port'],function($event_data) use($outside_worker){

        });

    };
    
    $outside_worker->onConnect = function($connection) use ($config){
    
        // $connection_data['session'] = $_SESSION;
        $connection_data['connection'] = [
            'ip'=>$connection->getRemoteIp(),
            'port'=>$connection->getRemotePort(),
            'c_connection_id'=>$connection->id
        ];
    
    
        Channel\Client::publish('cs_connect'.$config['local_ip'].":".$config['local_port'], $connection_data);
        
        $connection->onMessage = function($connection, $data) use ($config){
            // $message_data['session'] = $_SESSION;
            $message_data['connection'] = [
                'ip'=>$connection->getRemoteIp(),
                'port'=>$connection->getRemotePort(),
                'c_connection_id'=>$connection->id
            ];
            $message_data['data'] = $data;
    
            Channel\Client::publish('cs_message'.$config['local_ip'].":".$config['local_port'], $message_data);
            
        };
        
        $connection->onClose = function ($connection) use ($config){
        
            // $close_data['session'] = $_SESSION;
            $close_data['connection'] = [
                'ip'=>$connection->getRemoteIp(),
                'port'=>$connection->getRemotePort(),
                'c_connection_id'=>$connection->id
            ];
        
            Channel\Client::publish('cs_close'.$config['local_ip'].":".$config['local_port'], $close_data);
        };
    };

}

