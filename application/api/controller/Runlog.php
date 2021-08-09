<?php


namespace app\api\controller;

use app\api\validate\WriteLog;
use app\common\controller\Api;

class Runlog extends Base
{

    // 无需登录的接口,*表示全部
    protected $noNeedLogin = ['*'];
    // 无需鉴权的接口,*表示全部
    protected $noNeedRight = ['*'];
    /**
     * 写入运行日志
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/runlog/writeLog)
     * @ApiParams   (name="uid", type="string", required=true, description="uid")
     * @ApiParams   (name="runtype", type="string", required=true, description="运行类型 比如：auto，   指自动运行，其他如121212")
     * @ApiParams   (name="stime", type="string", required=true, description="开始时间，格式：yyyy-mm-dd H:i:s")
     * @ApiParams   (name="etime", type="string", required=true, description="结束时间，格式：yyyy-mm-dd H:i:s")
     * @ApiParams   (name="userjson", type="strin", required=true, description="用户信息")
     * @ApiParams   (name="statjson", type="string", required=true, description="运行信息")
     * @ApiParams   (name="runstat", type="string", required=true, description="运行结论")
     * @ApiParams   (name="createtime", type="string", required=true, description="创建时间时间，格式：yyyy-mm-dd H:i:s")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    })
     */
    public function writeLog()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post();
            $validate = new WriteLog();
            $validate->batch()->check($params);
            $msg = $validate->getError();
            if (!$validate->batch()->check($params)){
                $this->error('参数错误',$msg);
            }else{
                $stime = strtotime($params['stime']);
                $etime = strtotime($params['etime']);
                $createtime = strtotime($params['createtime']);
                $params['stime'] = $stime;
                $params['etime'] = $etime;
                $params['createtime'] = $createtime;
                $params['updatetime'] = time();
                 $insert = db('runlog')->insert($params);
                 $this->success('更新成功',[]);


                }


        }

    }

}