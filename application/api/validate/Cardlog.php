<?php


namespace app\api\validate;


use think\Db;
use think\Validate;

class Cardlog extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'logdt'      =>  'require',
        'comp_id'    =>  'require',
        'compsk'     =>  'require',
        'compvk'     =>  'require',
        'year'       =>  'require|number',
        'month'      =>  'require|number',
        'stime'      =>  'require',
        'etime'      =>  'require',
        'dev_id'     =>  'require',
        'devnum'     =>  'require',
        'card_id'    =>  "require",
        'cardnum'    =>  'require',
        'carpthoto'  =>  'require',
        'dept_id'    =>  'require',
        'cardjson'   =>  'require',
        'logstat'    =>  'require',
        'uqnum'      =>  'require',
        'logswitch'  =>  'require',
        'page'       =>  'require',
        'rows'       =>  'require',
        'createtime' =>  'require'
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
            'add'      => ['dev_id','devnum','card_id','cardnum','comp_id','cardjson','logstat','uqnum','logswitch','dept_id'],
            'get'      => ['createtime','logdt','compsk','compvk','comp_id'],
            'month'    => ['month','year','comp_id','dev_id','dept_id'],
            'byMonth'  => ['comp_id','dev_id','dept_id','stime','etime'],
            'year'     => ['year','comp_id','dev_id','dept_id'],
             'C'       => ['comp_id'],
            'cardnum'  => ['comp_id','stime','etime'],
            'byDay'    => ['stime','etime','comp_id']
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
        $pattern = '/^(((1[6-9]|[2-9]\d)(\d{2})-((0?[13578])|(1[02]))-((0?[1-9])|([12]\d)|(3[01])))|((1[6-9]|[2-9]\d)(\d{2})-((0?[469])|11)-((0?[1-9])|([12]\d)|30))|((1[6-9]|[2-9]\d)(\d{2})-0?2-((0?[1-9])|(1\d)|(2[0-8])))|((1[6-9]|[2-9]\d)([13579][26])-0?2-29)|((1[6-9]|[2-9]\d)([2468][048])-0?2-29)|((1[6-9]|[2-9]\d)(0[48])-0?2-29)|([13579]600-0?2-29)|([2468][048]00-0?2-29)|([3579]200-0?2-29))$/';
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
    protected function stime($value, $rule='', $data='', $field='')
    {

        return $value;
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
        $count = Db::name('cardlog')->where(['uqnum'=>$value])->count();
        if (false == $count){
            return true;
        }
        return $filed . '已存在';
    }



}