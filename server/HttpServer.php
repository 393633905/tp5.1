<?php
namespace server;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;
use think\Container;

class HttpServer{

    private static $instance;

    private $server = null;

    private function __construct($host,$port){
        // 创建HttpServer服务.
        $this->server=new \Swoole\Http\Server($host,$port);

        // 添加HttpServer配置.
        $this->server->set([
            'work_num'=>2,
            'document_root'=>__DIR__.'/../public/static',//静态文件目录路径
            'enable_static_handler'=>true//开启静态资源服务器
        ]);

        // 回调监听.
        $this->server->on('WorkerStart',[$this,'onWorkerStart']);//当work/task进程启动时回调
        $this->server->on('request',[$this,'onRequest']);

        // 开启服务.
        $this->server->start();

    }

    private function __clone(){}

    public static function getInstance(string $host = '0.0.0.0',string $port = '9998'){
        if (empty(self::$instance))
            self::$instance=new self($host,$port);

        return self::$instance;
    }

    public function onWorkerStart(Server $server,int $workId){
        // 加载基础文件.
        require __DIR__ . '/../thinkphp/base.php';
    }


    public function onRequest(Request $request,Response $response){
        $requestUri=$request->server['request_uri'];
        if ($requestUri == '/favicon.ico' || $request->server['path_info'] == '/favicon.ico'){
            $response->end();
            return;
        }

        // 处理信息.
        if ($request->server){
            foreach ($request->server as $k=>$v){
                $_SERVER[strtoupper($k)]=$v;
            }
        }

        if ($request->header){
            foreach ($request->header as $k=>$v){
                $_HEADER[strtolower($k)]=$v;
            }
        }

        if ($request->get){
            foreach ($request->get as $k=>$v){
                $_GET[strtolower($k)]=$v;
            }
        }

        if ($request->post){
            foreach ($request->post as $k=>$v){
                $_POST[strtolower($k)]=$v;
            }
        }

        if ($request->files){
            foreach ($request->files as $k=>$v){
                $_FILES[strtolower($k)]=$v;
            }
        }

        // 响应并缓存结果.
        ob_start();
        try {
            Container::get('app')->run()->send();
        }catch (\Exception $e){
            echo $e->getMessage();
        }
        $res=ob_get_contents();
        if ($res){
            ob_clean();
            $response->end($res);
        }
    }
}

HttpServer::getInstance();