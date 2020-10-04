<?php

namespace app\common\lib\traits;

trait ResponseJson
{

    /**
     * 响应成功的json.
     *
     * @param array $data
     * @param string $msg
     * @param bool $status
     * @param int $code
     *
     * @return \think\response\Json
     */
    public function responseSuccess(array $data = [], string $msg = 'success', bool $status = true, int $code = 200)
    {
        return json(['status' => $status, 'code' => $code, 'msg' => $msg, 'data' => $data]);
    }

    /**
     * 响应失败的 json.
     *
     * @param string $msg
     * @param int $code
     * @param bool $status
     * @param array $data
     *
     * @return \think\response\Json
     */
    public function responseFail(string $msg = 'error', int $code = 500, bool $status = false, array $data = [])
    {
        return json(['status' => $status, 'code' => $code, 'msg' => $msg, 'data' => $data]);
    }
}