<?php
namespace app\common\lib\exception;
use think\exception\Handle;

class Handler extends Handle{

    public function render(\Exception $e){
        if(config('app_debug') == true){
            // 交给TP处理.
            return parent::render($e);
        }

        // 如果是自定义错误类型.
        if($e instanceof ApiException){
            $data=[
                'status' => false,
                'code'=>$e->getCode(),
                'message' => $e->getMessage(),
                'data' => [],
            ];

            return json($data, 500);
        }

        return parent::render($e);
    }
}