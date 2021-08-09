<?php

namespace app\admin\model;

use think\Model;
use traits\model\SoftDelete;

class Dept extends Model
{

    use SoftDelete;

    

    // 表名
    protected $name = 'dept';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [

    ];
    

    







    public function comp()
    {
        return $this->belongsTo('Comp', 'comp_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
