<?php


namespace app\api\controller;


use app\common\controller\Api;
use think\Db;
use think\Request;

class Config extends Base
{
    // 无需登录的接口,*表示全部
    protected $noNeedLogin = ['*'];
    // 无需鉴权的接口,*表示全部
    protected $noNeedRight = ['*'];

    /**
     * 获取配置信息
     * @ApiMethod(GET)
     * @ApiRoute    (/api/config/getConfig)
     * @ApiReturn   ({
            'code':'1',
            'msg':'操作成功'
            })
     */
    public function getConfig()
    {
        if ($this->request->isGet()){

          $data1 = db('config')->where(['group'=>'system'])->column(['name','value']);
          $data2 = db('config')->where(['group'=>'user'])->column(['name','value']);
          $data3 = db('config')->where(['group'=>'device'])->column(['name','value']);
          $data4 = db('config')->where(['group'=>'wechat'])->column(['name','value']);
          $data = array_merge($data1,$data2,$data3,$data4);
          $this->success('操作成功',$data);
        }

    }
    /**
     * 获取当前时间
     * @ApiMethod(POST)
     * @ApiRoute    (/api/config/getTime)
     * @ApiParams   (name="comp_id", type="string", required=true, description="机构id")
     * @ApiReturn   ({
    'code':'1',
    'msg':'操作成功'
    })
     */
    public function getTime()
    {
        if ($this->request->isPost()){
            $param = $this->request->param();
            $validate = new \app\api\validate\Config();
            $validate->scene('time')->check($param);
            $msg = $validate->getError();
            if ($validate->scene('time')->check($param)){
                $comp = Db::name('comp')->where(['id'=>$param['comp_id']])->find();
                $time = date('Y-m-d H:i:s',time());
                $comptime = date('Y-m-d H:i:s',$comp['comptime']);
                $result = [
                    'code' => 1,
                    'msg'  => '获取成功',
                    'comptime' => $comptime,
                    'time' => $time
                ];
                return json($result);
            }else{
                $this->error($msg);
            }

        }



    }

    public function restore()
    {
        $params = $this->request->param();
        $up = Db::name($params['tb'])->where(['id'=>$params['id']])->update(['deletetime'=>null]);
        if ($up){
            $this->success('成功');
        }
    }

}