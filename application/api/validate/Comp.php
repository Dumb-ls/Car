<?php


namespace app\api\validate;


use think\Validate;

class Comp extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'id' => 'require',
        'compsk' => 'require',
        'compvk' => 'require',
    ];
    /**
     * 提示消息
     */
    protected $message = [

    ];
    /**
     * 验证场景
     */
    protected $scene = [
        'bind'  => [],
        'login' => ['wxuid']
    ];

}