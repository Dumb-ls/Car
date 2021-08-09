<?php

namespace app\admin\model;

use think\Model;
use traits\model\SoftDelete;

class Runlog extends Model
{

    use SoftDelete;

    

    // 表名
    protected $name = 'runlog';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [
        'stime_text',
        'etime_text'
    ];
    

    



    public function getStimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['stime']) ? $data['stime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getEtimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['etime']) ? $data['etime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setStimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setEtimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


    public function admin()
    {
        return $this->belongsTo('Admin', 'uid', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
