<?php


namespace app\api\validate;


use think\Validate;

class WechatBind extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'compuser' => 'require',
        'comppass' => 'require',
        'wxuid' => 'require',
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
        'bind'  => ['wxuid'],
        'login' => ['wxuid']
    ];

}