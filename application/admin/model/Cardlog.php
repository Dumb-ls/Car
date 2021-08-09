<?php

namespace app\admin\model;

use think\Model;
use traits\model\SoftDelete;

class Cardlog extends Model
{

    use SoftDelete;

    

    // 表名
    protected $name = 'cardlog';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [

    ];
    

    







    public function card()
    {
        return $this->belongsTo('Card', 'card_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function dev()
    {
        return $this->belongsTo('Dev', 'dev_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function comp()
    {
        return $this->belongsTo('Comp', 'comp_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
