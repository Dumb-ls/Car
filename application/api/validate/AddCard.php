<?php


namespace app\api\validate;

use think\Db;
use think\Validate;

class AddCard extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'comp_id' => 'require',
        'cardnum' => 'require',
        'carduid' => 'require',
        'cardname' => 'require',
        'dept_id' => 'require',
        'cardjson' => 'require',
        'cardswitch' => 'require',
        'cardtime' => 'require|timeFormat',
        'createtime' => 'require',
        'logdt' =>'require'
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
        'add'  => ['comp_id','cardnum','carduid','cardname','dept_id','cardjson'],
        'query' => ['createtime','logdt'],
        'optional' => ['stime','etime'],
        'del' => ['comp_id','cardnum'],
        'stop'=> ['cardnum'],
        'update' => ['id'],
        'get' => ['comp_id']
    ];

    /**
     * @param $value
     * @param string $rule
     * @param string $data
     * @param string $field
     * @return bool|string
     */
    protected function timeFormat($value, $rule='', $data='', $field='')
    {
        $pattern = "/^(((20[0-3][0-9]-(0[13578]|1[02])-(0[1-9]|[12][0-9]|3[01]))|(20[0-3][0-9]-(0[2469]|11)-(0[1-9]|[12][0-9]|30))) (20|21|22|23|[0-1][0-9]):[0-5][0-9]:[0-5][0-9])$/";
        if (preg_match($pattern,$value)) {
            return true;
        }else{
            return '日期格式不正确';
        }

    }

    /**
     * @param $value
     * @param string $rule
     * @param string $data
     * @param string $field
     * @return bool|string
     */
    protected function isInt($value, $rule='', $data='', $field='')
    {
        if (is_numeric($value) && is_int($value + 0)) {
            return true;
        }
        return $field . '必须是整数';
    }
    /**
     * @param $value
     * @param string $rule
     * @param string $data
     * @param string $field
     * @return bool|string
     */
    protected function isOne($value, $rule='', $data='', $field='')
    {
        if (1 == $value || 0 == $value) {
            return true;
        }
        return $field . '必须是0或1';
    }

    protected function only($value, $rule='',$data='',$filed='')
    {
        $count = Db::name('card')->where(['cardname'=>$value])->count();
        if (false == $count){
            return true;
        }
        return $filed . '已存在';
    }


}