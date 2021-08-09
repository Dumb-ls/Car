<?php
namespace app\api\validate;

use think\Validate;

class UpdateVersion extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'oldversion' => 'require',
        'newversion' => 'require',
        'packagesize' => 'require',
        'downloadurl' => 'require',
        'enforce' => 'require|isOne',
        'weigh' => 'require|isInt',
        'status' => 'require|isNormal'
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
}