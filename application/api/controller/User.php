<?php
namespace app\api\controller;

use app\common\lib\traits\RequestTraits;
use app\common\lib\traits\ResponseJson;
use think\Controller;
use think\Request;

class User extends Controller
{
    use ResponseJson,RequestTraits;

    public function sendMsg(Request $request)
    {
        // 接收参数.
        $params=$this->getParams($request,['phone']);

        // 参数检测.
        $result=$this->validate($params,'app\api\validate\User.login');
        if (true !== $result)
            return $this->responseFail($result);

        // 发送短信.
        $code=rand(9999,99999);

        // 

        // 存储验证码用于后续校验.

        // 返回结果.


    }
}
