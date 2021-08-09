<?php


namespace app\api\validate;


use think\Validate;

class AndroidLogin extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'compuser' => 'require',
        'comppass' => 'require',
        'id' => 'require',
        'compsk' => 'require',
        'compvk' => 'require',
        'comp_id' => 'require'
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
        'login' => ['wxuid'],
        'android' => ['compuser','comppass'],
        'get' => ['id','compsk','compvk'],
        'pass' => ['comp_id','compsk','compvk','comppass']
    ];

}