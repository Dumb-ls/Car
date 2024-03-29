<?php

namespace app\admin\model;

use think\Model;
use traits\model\SoftDelete;

class Comp extends Model
{

    use SoftDelete;

    

    // 表名
    protected $name = 'comp';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [
        'comptime_text'
    ];
    

    



    public function getComptimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['comptime']) ? $data['comptime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setComptimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}
