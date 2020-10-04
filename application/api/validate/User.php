<?php
namespace app\api\validate;

use think\Validate;

class User extends Validate{

    protected $rule=[
        'phone'=>'require|mobile'
    ];

    protected $message=[
        'phone.require'=>'请输入手机号',
        'phone.mobile'=>'请输入合法的手机号'
    ];

    protected $scene=[
        'login'=>['phone']
    ];
}
