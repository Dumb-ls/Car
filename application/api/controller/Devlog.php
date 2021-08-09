<?php


namespace app\api\controller;


use app\api\validate\WriteDevLog;
use app\common\controller\Api;
use think\Db;

class Devlog extends Base
{
    // 无需登录的接口,*表示全部
    protected $noNeedLogin = ['*'];
    // 无需鉴权的接口,*表示全部
    protected $noNeedRight = ['*'];
    /**
     * 写入设备日志
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/devlog/writeLog)
     * @ApiParams   (name="comp_id", type="int", required=true, description="机构编号")
     * @ApiParams   (name="dept_id", type="int", required=true, description="工区编号")
     * @ApiParams   (name="operip", type="string", required=true, description="操作地址")
     * @ApiParams   (name="operdt", type="string", required=true, description="操作时间，格式：yyyy-mm-dd H:i:s")
     * @ApiParams   (name="opermsg", type="strin", required=true, description="具体操作")
     * @ApiParams   (name="operdo", type="string", required=true, description="操作内容")
     * @ApiParams   (name="operswitch", type="string", required=true, description="操作状态")
     * @ApiParams   (name="createtime", type="string", required=true, description="发生时间，格式：yyyy-mm-dd H:i:s")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    })
     */
    public function writeLog()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post();
            $validate = new WriteDevLog();
            $validate->batch()->check($params);
            $msg = $validate->getError();
            if (!$validate->batch()->check($params)){
                $this->error('参数错误',$msg);
            }else{
                $operdt = strtotime($params['operdt']);
                $createtime = strtotime($params['createtime']);
                $params['operdt'] = $operdt;
                $params['createtime'] = $createtime;
//                dump($params);
                if (Db::name('devlog')->where($params)->find()){
                    $this->error('数据已存在',[]);
                }
                 $insert = db('devlog')->insert($params);
                 if ($insert){
                     $this->success('更新成功',[]);
                 }



            }


        }

    }

}