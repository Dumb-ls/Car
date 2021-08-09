<?php

namespace app\admin\model;

use think\Model;
use traits\model\SoftDelete;

class Card extends Model
{

    use SoftDelete;

    

    // 表名
    protected $name = 'card';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [
        'cardtime_text'
    ];
    

    



    public function getCardtimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['cardtime']) ? $data['cardtime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setCardtimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


    public function comp()
    {
        return $this->belongsTo('Comp', 'comp_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function dept()
    {
        return $this->belongsTo('Dept', 'dept_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
