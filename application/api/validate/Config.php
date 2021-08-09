<?php


namespace app\api\validate;


use think\Db;
use think\Validate;

class Config extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'comp_id' => 'require',
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
        'time'  => ['comp_id'],
    ];

}