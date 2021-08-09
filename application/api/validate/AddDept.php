<?php


namespace app\api\validate;


use think\Db;
use think\Validate;

class AddDept extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'deptname' => 'require',
        'deptaddr' => 'require',
        'deptjson' => 'require',
        'comp_id' => 'require',
        'id' => 'require|isnull'

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
            'add' => ['deptname','deptaddr','comp_id'],
            'get' => ['comp_id'],
            'update' => ['id']
    ];

    protected function only($value, $rule='',$data='',$filed='')
    {
        $count = Db::name('dept')->where(['deptname'=>$value])->count();
        if ($count){
            return true;
        }
        return $filed . '已存在';
    }

    protected function isnull($value, $rule='',$data='',$filed='')
    {
        $count = Db::name('dept')->where(['id'=>$value])->count();
        if ($count){
            return true;
        }
        return '工区不存在';
    }

}