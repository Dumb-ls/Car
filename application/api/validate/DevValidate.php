<?php


namespace app\api\validate;


use think\Db;
use think\Validate;

class DevValidate extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'devnum' => 'require',
        'comp_id' => 'require',
        'devsk' => 'require',
        'id' => 'require',
        'devname' => 'require',
        'dept_id' => 'require'

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
        'add' => ['devnum','comp_id','dept_id','devname'],
        'get' => ['comp_id'],
        'update' => ['id'],
        'del' => ['id'],
        'check' => ['devnum']
    ];


    protected function only($value, $rule='',$data='',$filed='')
    {
        $count = Db::name('dev')->where(['devnum'=>$value])->count();
        if (false == $count){
            return true;
        }
        return $filed . '已存在';
    }

}