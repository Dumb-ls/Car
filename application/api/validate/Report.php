<?php


namespace app\api\validate;


use think\Validate;

class Report extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'repname' => 'require',
        'dept_id' => 'require',
        'dev_id'  => 'require',
        'card_id' => 'require',
        'comp_id' => 'require',
        'stime'   => 'require|timeFormat',
        'etime'   => 'require|timeFormat',
        'id'      => 'require',
        'reptype' => 'require'
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
        'report'   => ['comp_id'],
        'add'      => ['repname','stime','etime','comp_id','reptype'],
        'download' => ['id','comp_id'],
        'del'      => ['id']
    ];

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

    protected function isNormal($value, $rule='', $data='', $field='')
    {
        if ("normal" == $value || "hidden" == $value) {
            return true;
        }
        return $field . '必须是normal或hidden';
    }

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
}