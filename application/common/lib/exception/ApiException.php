<?php
namespace app\common\lib\exception;
use think\Exception;

class ApiException extends Exception{

    public function __construct($message = "error", int $code = 500)
    {
        parent::__construct($message, $code);
    }
}