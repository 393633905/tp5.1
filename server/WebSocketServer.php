<?php
namespace server;
use app\common\lib\task\Task;
use app\common\lib\utils\AliSMS;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;
use think\Container;

class WebSocketServer{

    private static $instance;

    private $ws = null;

    private function __construct($host,$port){
        // 创建HttpServer服务.
        $this->ws=new \Swoole\WebSocket\Server($host,$port);

        // 添加HttpServer配置.
        $this->ws->set([
            'work_num'=>2,
            'task_worker_num'=>2,
            'document_root'=>__DIR__.'/../public/static',//静态文件目录路径
            'enable_static_handler'=>true//开启静态资源服务器
        ]);

        // 回调监听.
        $this->ws->on('WorkerStart',[$this,'onWorkerStart']);//当work/task进程启动时回调
        $this->ws->on('open',[$this,'onOpen']);
        $this->ws->on('message',[$this,'onMessage']);
        $this->ws->on('request',[$this,'onRequest']);
        $this->ws->on('task',[$this,'onTask']);
        $this->ws->on('finish',[$this,'onFinish']);
        $this->ws->on('close',[$this,'onClose']);

        // 开启服务.
        $this->ws->start();

    }

    private function __clone(){}

    public static function getInstance(string $host = '0.0.0.0',string $port = '9998'){
        if (empty(self::$instance))
            self::$instance=new self($host,$port);

        return self::$instance;
    }

    public function onWorkerStart($ws,int $workId){
        // 加载基础文件.
        require __DIR__ . '/../thinkphp/base.php';
    }


    public function onOpen($ws, $request){
        echo '有客户端连接：'.$request->fd;
    }

    public function onMessage($ws, $frame){
        echo '收到消息'.$frame->data;
    }

    public function onRequest(Request $request,Response $response){
        $requestUri=$request->server['request_uri'];
        if ($requestUri == '/favicon.ico' || $request->server['path_info'] == '/favicon.ico'){
            $response->end();
            return;
        }

        // 处理信息.
        if (isset($request->server)){
            foreach ($request->server as $k=>$v){
                $_SERVER[strtoupper($k)]=$v;
            }
        }

        if (isset($request->header)){
            foreach ($request->header as $k=>$v){
                $_HEADER[strtoupper($k)]=$v;
            }
        }

        $_GET=[];
        if (isset($request->get)){
            foreach ($request->get as $k=>$v){
                $_GET[$k]=$v;
            }
        }

        $_POST=[];
        if (isset($request->post)){
            foreach ($request->post as $k=>$v){
                $_POST[$k]=$v;
            }
        }

        if (isset($request->files)){
            foreach ($request->files as $k=>$v){
                $_FILES[$k]=$v;
            }
        }

        $_POST['http_server']=$this->ws;

        // 响应并缓存结果.
        ob_start();
        try {
            Container::get('app')->run()->send();
        }catch (\Exception $e){
            echo $e->getMessage();
            return;
        }
        $res=ob_get_contents();
        if ($res){
            ob_clean();
            $response->end($res);
        }
    }

    public function onTask(Server $server, $task_id, $from_id, $data){
        // 执行发送短信任务.
        $task=new Task();
        $method=$data['method'];
        $params=$data['params'];

        // 调用方法.
        if($task->$method($params) === true){
            return '发送短信|success'.PHP_EOL;
        }

    }

    public function onFinish($serv, $task_id, $data){
        echo "AsyncTask[$task_id] Finish: $data".PHP_EOL;
    }

    public function onClose($ws, $fd){
        echo '有客户端离开：'.$fd.PHP_EOL;
    }
}

WebSocketServer::getInstance();