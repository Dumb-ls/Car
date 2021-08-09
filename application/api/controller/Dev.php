<?php


namespace app\api\controller;


use app\api\validate\DevValidate;
use app\common\controller\Api;
use think\Db;

class Dev extends Base
{
    // 无需登录的接口,*表示全部
    protected $noNeedLogin = ['*'];
    // 无需鉴权的接口,*表示全部
    protected $noNeedRight = ['*'];
    /**
     * 添加设备
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/dev/addDev)
     * @ApiParams   (name="devnum", type="string", required=true, description="设备号")
     * @ApiParams   (name="comp_id", type="string", required=true, description="所属机构id")
     *  @ApiParams   (name="dept_id", type="string", required=true, description="工区编号")
     * @ApiParams   (name="devjson", type="string", required=true, description="设备信息")
     *  @ApiParams   (name="devname", type="string", required=true, description="设备名称")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    })
     */
    public function addDev()
    {
        if ($this->request->isPost()) {
            $params = $this->request->param();
            $validate = new DevValidate();
            $validate->scene('add')->batch()->check($params);
            $msg = $validate->getError();
            if (!$validate->scene('add')->batch()->check($params)){
                $this->error('参数错误',$msg);
            }else{
                if (Db::name('dev')->where(['devname'=>$params['devname'],'comp_id'=>$params['comp_id']])->find()){
                    $this->error('设备名已存在，请修改');
                }else{
                    $salt = config('car.car_salts');
                    $params['devsk'] = md5($params['devnum'].$salt);
                    $params['devswitch'] = 1;
                    $params['createtime'] = time();
                    $now = date('Y-m-d H:i:s',time());
                    $params['devtime'] = strtotime('+1years',strtotime($now));
                    $insert = db('dev')->insert($params);
                    $this->success('添加成功',[]);
                }



            }


        }
    }
    /**
     * 获取设备信息
     * @ApiMethod(POST)
     * @ApiRoute    (/api/Dev/getDev)
     * @ApiParams   (name="comp_id", type="int", required=true, description="所属机构id")
     * @ApiParams   (name="dept_id", type="int", required=true, description="工区id，可选")
     * @ApiReturn   ({
    'code':'1',
    'msg':'操作成功'
    })
     */
    public function getDev()
    {
        if ($this->request->isPost()) {
            $params = $this->request->param();
            $validate = new DevValidate();
            $validate->scene('get')->batch()->check($params);
            $msg = $validate->getError();
            if (!$validate->scene('get')->batch()->check($params)){
                $this->error('参数错误',$msg);
            }else{
                $where = [
                    'comp_id' => $params['comp_id'],
                    'deletetime'=>null,
                    'devswitch' =>1
                ];
                (!empty($params['dept_id']))?$where['dept_id']=$params['dept_id']:false;
                $dev = db('dev')->where($where)->select();
                $data = [];
                for ($i = 0;$i < count($dev);$i++){
                    $comp = db('comp')->where(['id' => $dev[$i]['comp_id']])->find();
                    $dept = db('dept')->where(['id' => $dev[$i]['dept_id'],'deletetime'=>null])->find();
                    if ($dept){
                        $data[$i]['id'] = $dev[$i]['id'];
                        $data[$i]['comp_id'] = $params['comp_id'];
                        $data[$i]['compname'] = $comp['compname'];
                        $data[$i]['dept_id'] = $dept['id'];
                        $data[$i]['deptname'] = $dept['deptname'];
                        $data[$i]['devnum'] = $dev[$i]['devnum'];
                        $data[$i]['devname'] = $dev[$i]['devname'];
                        $data[$i]['devsk'] = $dev[$i]['devsk'];
                        $data[$i]['devjson'] = $dev[$i]['devjson'];
                        $data[$i]['devswitch'] = $dev[$i]['devswitch'];
                        $data[$i]['devtime'] = $dev[$i]['devtime'];
                    }else{
                        $data[$i]['id'] = $dev[$i]['id'];
                        $data[$i]['comp_id'] = $params['comp_id'];
                        $data[$i]['compname'] = $comp['compname'];
                        $data[$i]['dept_id'] = $dept['id'];
                        $data[$i]['deptname'] = '-';
                        $data[$i]['devnum'] = $dev[$i]['devnum'];
                        $data[$i]['devname'] = $dev[$i]['devname'];
                        $data[$i]['devsk'] = $dev[$i]['devsk'];
                        $data[$i]['devjson'] = $dev[$i]['devjson'];
                        $data[$i]['devswitch'] = $dev[$i]['devswitch'];
                        $data[$i]['devtime'] = $dev[$i]['devtime'];
                    }
                }
                if ($data){
                    $this->success('操作成功',$data);
                }else{
                    $this->error('无信息');
                }


            }


        }
    }

    /**
     * 更新设备信息
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/dev/updateDev)
     *  @ApiParams   (name="id", type="string", required=true, description="id")
     * @ApiParams   (name="comp_id", type="string", required=true, description="所属机构id")
     * @ApiParams   (name="devnum", type="string", required=true, description="设备号")
     *
     * @ApiParams   (name="devname", type="string", required=true, description="设备名称")
     * @ApiParams   (name="devjson", type="string", required=true, description="设备信息")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    })
     */
    public function updateDev()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post();
            $validate = new DevValidate();
            $validate->scene('update')->batch()->check($params);
            $msg = $validate->getError();
            if (!$validate->scene('update')->batch()->check($params)){
                $this->error('参数错误',$msg);
            }else{
//                $comp_id = $params['comp_id'];
//                $devnum = $params['devnum'];
//                $devname = $params['devname'];
//                $devjson = $params['devjson'];
                $salt = config('car.car_salts');
                $info = [];
                (!empty($params['comp_id']))?$info['comp_id']=$params['comp_id']:false;
                (!empty($params['devnum']))?$info['devnum']=$params['devnum']:false;
                (!empty($params['devname']))?$info['devname']=$params['devname']:false;
                (!empty($params['devjson']))?$info['devjson']=$params['devjson']:false;
                (!empty($params['devsk']))?$info['devsk']=md5($params['devnum'].$salt):false;
//                $info['comp_id']=$comp_id;
//                $info['devnum']=$devnum;
//                $info['devname']=$devname;
//                $info['devjson']=$devjson;

//                $info['devsk'] = md5($params['devnum'].$salt);

                if (!empty($info)){
                    $update = db('dev')->where(['id' => $params['id']])->update($info);
                 if ($update){
                     $this->success('更新成功',[]);
                 }else{
                     $this->error('未作改动',[]);
                 }

                }


            }


        }
    }
    /**
     * 删除设备信息
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/dev/delDev)
     * @ApiParams   (name="id", type="string", required=true, description="设备id")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    })
     */
    public function delDev()
    {
        if ($this->request->isPost()) {
            $params = $this->request->param();
            $validate = new DevValidate();
            $validate->scene('del')->batch()->check($params);
            $msg = $validate->getError();
            if (!$validate->scene('del')->batch()->check($params)){
                $this->error('参数错误',$msg);
            }else{

                db('dev')->where($params)->update(['deletetime'=>time()]);
                $this->success('删除成功',[]);


            }


        }
    }
    /**
     * 设备验证，返回相应信息
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/dev/checkDev)
     * @ApiParams   (name="devnum", type="string", required=true, description="设备号")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    })
     */
    public function checkDev()
    {
        if ($this->request->isPost()) {
            $params = $this->request->param();
            $validate = new DevValidate();
            $validate->scene('check')->batch()->check($params);
            $msg = $validate->getError();
            if (!$validate->scene('check')->batch()->check($params)){
                $this->error('参数错误',$msg);
            }else{
                $data = Db::name('dev')->where(['devnum'=>$params['devnum'],'deletetime'=>null])->find();
                $comp = Db::name('comp')->where(['id'=>$data['comp_id'],'deletetime'=>null])->find();
                $dept = Db::name('dept')->where(['id'=>$data['dept_id'],'deletetime'=>null])->find();
                if (!$data){
                    $this->error('设备不存在');
                }elseif ($data['deletetime'] == null && $data['devtime'] > time()){
                    $data['compname'] = $comp['compname'];
                    $data['deptname'] = $dept['deptname'];
                    $this->success('验证成功',$data);
                }elseif ($data['devtime'] < time() && $data['deletetime'] == null){
                    $this->error('设备已逾期，请联系管理员');
                }elseif ($data['deletetime'] != null){
                    $this->error('设备已被删除');
                }

            }


        }
    }

}