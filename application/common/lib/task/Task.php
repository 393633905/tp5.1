<?php
namespace app\common\lib\task;
class Task{

    public function sendMsg(array $params= [] ){
        try{
            if (empty($params))
                throw new \Exception('必要参数不能为空');

            if (empty($params['phone']) || empty($params['code']))
                throw new \Exception('缺少必要参数');

            return true;
        }catch (\Exception $e){
            return false;
        }

        // todo 存储验证码到redis中.


        // 返回结果.
        return true;
    }
}