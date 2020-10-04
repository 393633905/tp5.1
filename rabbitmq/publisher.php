<?php

require_once '';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;
use think\Exception;

try {
    $host='127.0.0.1';
    $port='5672';
    $username='guest';
    $password='guest';

    $queueName='goods_ms_queue';
    $exchangeName='goods_ms_exchange';
    $routingKey='goods';


    // 创建AMQP链接.
    $connection=new AMQPStreamConnection($host,$port,$username,$password);
    if (!$connection)
        throw new Exception('AMQP连接创建失败');

    // 创建管道.
    $channel=$connection->channel();

    // 创建队列.
    $channel->queue_declare($queueName,false,true,false,false);

    // 创建交换机.
    $channel->exchange_declare($exchangeName,AMQPExchangeType::DIRECT,false,true,false);

    // 交换机和队列进行绑定.
    $channel->queue_bind($queueName,$exchangeName,$routingKey);

    // 发送消息.
    $msg=new AMQPMessage('Hello RabbitMQ');
    $channel->basic_publish($msg,$exchangeName,$routingKey);

    $channel->close();
    $connection->close();
    return '消息发送成功';
}catch (\Exception $e){
    return $e->getMessage();
}