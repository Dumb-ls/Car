<?php


namespace app\api\validate;



use think\Validate;

class WriteDevLog extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'comp_id' => 'require|isInt',
//        'dept_id' => 'require|isInt',
        'operip' => 'require',
        'operdt' => 'require|timeFormat',
        'opermsg' => 'require',
        'operdo' => 'require',
        'operswitch' => 'require',
        'createtime' => 'require|timeFormat'
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
        'add'  => [],
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


}