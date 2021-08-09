<?php


namespace app\api\controller;


use app\api\validate\AndroidLogin;
use app\common\controller\Api;
use think\Db;

class Android extends Base
{
    // 无需登录的接口,*表示全部
    protected $noNeedLogin = ['*'];
    // 无需鉴权的接口,*表示全部
    protected $noNeedRight = ['*'];
    /**
     * Android通过用户名密码登陆
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/android/login)
     * @ApiParams   (name="compuser", type="int", required=true, description="机构用户")
     * @ApiParams   (name="comppass", type="string", required=true, description="密码")
     * @ApiReturn   ({
            'code':'1',
            'msg':'返回成功'
            })
     */
    public function login()
    {
        if ($this->request->isPost()){
            $params = $this->request->param();
            $validate = new AndroidLogin();
            $validate->scene('android')->batch()->check($params);
            $msg = $validate->getError();
            $salt = config('car.car_salts');
            $password = md5($params['comppass'].$salt);
            if (!$validate->scene('android')->batch()->check($params)){
                $this->error('参数错误',$msg);
            }else{
                $user = db('comp')->where(['compuser' => $params['compuser']])->find();
                if ($user){
                    if ($user['comppass'] == $password){
                        $now = time();
                        if ($now > $user['comptime']){
                            $this->error('账号逾期，无法登陆',[]);
                        }else{
                            if (1 == $user['compswitch']){

                                $data = [];
                                $data['id'] = $user['id'];
                                $data['compsk'] = $user['compsk'];
                                $data['compvk'] = $user['compvk'];

                                $this->success('登录成功',$data);
                            }else{
                                $this->error('账号被禁用，请联系管理员',[]);
                            }

                        }

                    }else{
                        $this->error('密码错误',[]);
                    }
                }else{
                    $this->error('用户名不存在',[]);
                }
            }
        }

    }
    /**
     * 获取机构信息,该机构工区信息，包含设备数，工区数，卡数
     * @ApiMethod(POST)
     * @ApiRoute    (/api/android/getMsg)
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
            $params = $this->request->post();
            $valdiate = new AndroidLogin();
            $valdiate->scene('get')->batch()->check($params);
            $msg = $valdiate->getError();
//            $version = db('comp')->where(['id' => $params['id'],'compsk' => $params['compsk'],'compvk' => $params['compvk']])->find();
            if ($valdiate->scene('get')->batch()->check($params)){
                $user = db('comp')->where(['id' => $params['id'],'compsk' => $params['compsk'],'compvk' => $params['compvk']])->find();

                if ($user){
                    $dept = db('dept')->where(['comp_id' => $params['id'],'deptswitch'=>1,'deletetime'=>null])->count();
                    $dev = db('dev')->where(['comp_id' => $params['id'],'devswitch'=>1,'deletetime'=>null])->count();
                    $card = db('card')->where(['comp_id' => $params['id'],'cardswitch'=>1,'deletetime'=>null])->count();
                    $data = [];
                    $data['compname'] = $user['compname'];
                    $data['compaddr'] = $user['compaddr'];
                    $data['dept'] = $dept;
                    $data['dev'] = $dev;
                    $data['card'] = $card;

                    $this->success('获取成功',$data);
                }else{
                    $this->error('该机构不存在',[]);
                }
            }else{
                $this->error('参数错误',$msg);
            }
        }

    }

    /**
     * 验证sk，vk
     *
     *
     */
    private function check($sk,$vk)
    {

        $user = db('comp')->where(['compsk' => $sk,'compvk' => $vk])->find();
        if ($user){
            return true;
        }else{
            return false;
        }

    }

    /**
     * 机构用户修改密码
     * @ApiMethod(POST)
     * @ApiRoute    (/api/android/updatePass)
     * @ApiParams   (name="comp_id", type="int", required=true, description="机构id")
     * @ApiParams   (name="compsk", type="int", required=true, description="机构sk")
     * @ApiParams   (name="compvk", type="int", required=true, description="机构vk")
     * @ApiParams   (name="comppass", type="int", required=true, description="新密码")
     * @ApiReturn   ({
    'code':'1',
    'msg':'操作成功'
    })
     */
    public function updatePass()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post();
            $valdiate = new AndroidLogin();
            $valdiate->scene('pass')->batch()->check($params);
            $msg = $valdiate->getError();
            if ($valdiate->scene('pass')->batch()->check($params)){
                $check = $this->check($params['compsk'],$params['compvk']);

                if ($check){
                    $salt = config('car.car_salts');
                    $pass = md5($params['comppass'].$salt);
                    $update = Db::name('comp')->where(['id'=>$params['comp_id'],'compsk'=>$params['compsk'],'compvk'=>$params['compvk']])->update(['comppass'=>$pass]);
                    if ($update){
                        $this->success('密码修改成功');
                    }else{
                        $this->error('未做更改');
                    }

                }else{
                    $this->error('该机构不存在',[]);
                }

            }else{
                $this->error('参数错误',$msg);
            }
        }
    }

}