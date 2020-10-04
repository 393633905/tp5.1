<?php
namespace app\admin\controller;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;
use think\Exception;
use think\facade\Env;
use think\Request;
use function foo\func;

class Index
{
    public function index(Request $request)
    {
       return 'Hello TP5.1'.time();

    }

    /**
     * 发送消息.
     *
     * @return string
     */
    public function send(){
        try {
            $host=\env('AMQP_HOST');
            $port=\env('AMQP_PORT');
            $username=\env('AMQP_USERNAME');
            $password=\env('AMQP_PASSWORD');

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
    }

    /**
     * 接收消息.
     *
     * @throws Exception
     */
    public function receive(){
        $host=\env('AMQP_HOST');
        $port=\env('AMQP_PORT');
        $username=\env('AMQP_USERNAME');
        $password=\env('AMQP_PASSWORD');

        // 创建AMQP链接.
        $connection=new AMQPStreamConnection($host,$port,$username,$password);
        if (!$connection)
            throw new Exception('AMQP连接创建失败');

        // 创建管道.
        $channel=$connection->channel();

        // 队列绑定交换机.
        $queueName='goods_ms_queue';
        $exchangeName='goods_ms_exchange';
        $routingKey='goods';

        // 创建队列.
        $channel->queue_declare($queueName,false,true,false,false);

        // 创建交换机.
        $channel->exchange_declare($exchangeName,AMQPExchangeType::DIRECT,false,true,false);

        // 交换机和队列进行绑定.
        $channel->queue_bind($queueName,$exchangeName,$routingKey);

        echo " 等待接收消息" .PHP_EOL;

        $callback=function (AMQPMessage $msg){
             echo '接收到消息：'.$msg->body.PHP_EOL;
             $msg->ack();
             // 收到quit消息内容时，则取消订阅.
            if ($msg->body == 'quit'){
                $msg->getChannel()->basic_cancel('consumer_tag');
            }
        };

        // 接收消息.
        $channel->basic_consume($queueName,'consumer_tag',false,false,false,false,$callback);

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
    }
}
