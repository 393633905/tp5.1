<?php
namespace app\common\lib\traits;

use think\Request;

trait RequestTraits{

    /**
     * 获取所有参数.
     *
     * @param Request $request
     * @param array $fields
     *
     * @return array|mixed|null
     */
    public function getParams(Request $request,array $fields = []){
        // 接收所有参数.
        $params=$request->param();
        if (!$params)
            return [];

        if (empty($fields))
            return $params;

        foreach ($fields as $v){
            $params[$v]=$request->param($v);
        }

        return $params;

    }

}