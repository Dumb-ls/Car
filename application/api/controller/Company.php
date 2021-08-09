<?php


namespace app\api\controller;


use app\api\validate\Comp;
use app\common\controller\Api;

class Company extends Base
{
    // 无需登录的接口,*表示全部
    protected $noNeedLogin = ['*'];
    // 无需鉴权的接口,*表示全部
    protected $noNeedRight = ['*'];

    /**
     * 获取机构信息
     * @ApiMethod(POST)
     * @ApiRoute    (/api/company/getComp)
     * @ApiParams   (name="compname", type="int", required=true, description="机构名称")
     * @ApiReturn   ({
    'code':'1',
    'msg':'操作成功'
    })
     */
    public function getComp()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post();
            $version = db('comp')->where(['compname' => $params['compname'],'deletetime'=>null])->find();
            if (!$version){
                $this->error("机构不存在",[]);
            }else{
                $data = db('comp')->where(['compname' => $params['compname'],'deletetime'=>null])->select();
                $this->success('操作成功',$data);
            }

        }

    }
    /**
     * 获取机构信息,该机构工区信息，该机构包含设备
     * @ApiMethod(POST)
     * @ApiRoute    (/api/company/getMsg)
     * @ApiParams   (name="id", type="int", required=true, description="机构id")
     * @ApiParams   (name="compsk", type="int", required=true, description="机构sk")
     * @ApiParams   (name="compvk", type="int", required=true, description="机构vk")
     * @ApiReturn   ({
    'code':'1',
    'msg':'操作成功'
    })
     */
    public function getMsg()
    {
        if ($this->request->isPost()) {
            $params = $this->request->param();
            $valdiate = new Comp();
            $valdiate->batch()->check($params);
            $msg = $valdiate->getError();
//            $version = db('comp')->where(['id' => $params['id'],'compsk' => $params['compsk'],'compvk' => $params['compvk']])->find();
            if ($valdiate->batch()->check($params)){
                $user = db('comp')->where(['id' => $params['id'],'compsk' => $params['compsk'],'compvk' => $params['compvk'],'deletetime'=>null])->find();
                if ($user){

                    $dev = db('dev')->where(['comp_id' => $params['id'],'deletetime'=>null])->select();

                    $data = [];
                    for ($i = 0;$i < count($dev);$i++){
                        $dept = db('dept')->where(['id' => $dev[$i]['dept_id'],'deletetime'=>null])->find();
                        if ($dept){
                            array_push($data,[
                                'comp_id' => $params['id'],
                                'compname' => $user['compname'],
                                'dept_id' => $dev[$i]['dept_id'],
                                'deptswitch' => $dept['deptswitch'],
                                'deptname' => $dept['deptname'],
                                'deptaddr' => $dept['deptaddr'],
                                'dev_id' => $dev[$i]['id'],
                                'devname' => $dev[$i]['devname'],
                                'devjson' => $dev[$i]['devjson'],
                                'devswitch' => $dev[$i]['devswitch'],
                                'devtime' => $dev[$i]['devtime']
                            ]);
                        }else{
                            array_push($data,[
                                'comp_id' => $params['id'],
                                'compname' => $user['compname'],
                                'dept_id' => $dev[$i]['dept_id'],
                                'deptswitch' => '-',
                                'deptname' => '-',
                                'deptaddr' => '-',
                                'dev_id' => $dev[$i]['id'],
                                'devname' => $dev[$i]['devname'],
                                'devjson' => $dev[$i]['devjson'],
                                'devswitch' => $dev[$i]['devswitch'],
                                'devtime' => $dev[$i]['devtime']
                            ]);
                        }






                    }

                    $this->success('获取成功',$data);
                }else{
                    $this->error('该机构不存在',[]);
                }
            }else{
                $this->error('参数错误',$msg);
            }
        }

    }
}