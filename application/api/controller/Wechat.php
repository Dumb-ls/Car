<?php


namespace app\api\controller;


use app\api\validate\WechatBind;
use app\common\controller\Api;
use think\Db;

class Wechat extends Base
{
    // 无需登录的接口,*表示全部
    protected $noNeedLogin = ['*'];
    // 无需鉴权的接口,*表示全部
    protected $noNeedRight = ['*'];
    /**
     * 微信第一次登陆绑定wxuid
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/wechat/firstBind)
     * @ApiParams   (name="compuser", type="int", required=true, description="机构用户")
     * @ApiParams   (name="comppass", type="string", required=true, description="密码")
     * @ApiParams   (name="wxuid", type="string", required=true, description="微信uid")
     * @ApiReturn   ({
            'code':'1',
            'msg':'返回成功'
            })
     */
    public function firstBind()
    {
        if ($this->request->isPost()){
            $params = $this->request->param();
            $validate = new WechatBind();
            $validate->batch()->check($params);
            $msg = $validate->getError();
            if ( $validate->batch()->check($params)){
                $user = db('comp')->where(['compuser' => $params['compuser']])->find();
                $salt = config('car.car_salts');
                $password = md5($params['comppass'].$salt);
                if ($user){
                        if ($user['comppass'] == $password){
                            $data = db('comp')->where(['compuser' => $params['compuser']])->update(['wxuid' => $params['wxuid']]);
                            $this->success('绑定成功',[2]);
                        }else{
                            $this->error('密码错误',[]);
                        }
                }else{
                    $this->error('用户名错误',[]);
                }
            }else{
                $this->error('参数错误',$msg);
            }
        }

    }
    /**
     * 微信通过wxuid登陆
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/wechat/login)
     * @ApiParams   (name="wxuid", type="string", required=true, description="微信uid")
     * @ApiReturn   ({
            'code':'1',
            'msg':'返回成功'
            })
     */
    public function login()
    {
        if ($this->request->isPost()){
            $params = $this->request->param();
            $validate = new WechatBind();
            $validate->scene('login')->check($params);
            $msg = $validate->getError();
            if ($validate->scene('login')->check($params)){
                $wxuid = db('comp')->where(['wxuid' => $params['wxuid']])->find();
                if ($wxuid){
                    $now = time();
                    if ($now > $wxuid['comptime']){
                        $this->error('账号逾期，无法登陆',[]);
                    }else{
                        if (1 == $wxuid['compswitch']){
                            $data = [];
                            $data['id'] = $wxuid['id'];
                            $data['compsk'] = $wxuid['compsk'];
                            $data['compvk'] = $wxuid['compvk'];
                            $this->success('登录成功',$data);
                        }else{
                            $this->error('账号被禁用，请联系管理员',[]);
                        }


                    }

                }else{
                    $this->error('wxuid不存在',[]);
                }
            }else{
                $this->error('参数错误',$msg);
            }
        }

    }

    /**
     * 清除wxuid
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/wechat/logout)
     * @ApiParams   (name="wxuid", type="string", required=true, description="微信uid")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    })
     */
    public function logout()
    {
        if ($this->request->isPost()) {
            $params = $this->request->param();
            $validate = new WechatBind();
            $validate->scene('login')->check($params);
            $msg = $validate->getError();
            if ($validate->scene('login')->check($params)) {
                $wxuid = db('comp')->where(['wxuid' => $params['wxuid']])->find();
                if ($wxuid) {
                    $info = [
                        'wxuid' => null
                    ];
                    $up = Db::name('comp')->where(['wxuid'=>$params['wxuid']])->update($info);
                    if ($up){
                        $this->success('注销成功');
                    }else{
                        $this->error('注销失败，请稍后重试');
                    }

                } else {
                    $this->error('wxuid不存在', []);
                }
            } else {
                $this->error('参数错误', $msg);
            }
        }
    }




    /**
     * 微信id是否已绑定
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/wechat/isBind)
     * @ApiParams   (name="wxuid", type="string", required=true, description="微信uid")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    })
     */
    public function isBind()
    {
        if ($this->request->isPost()){
            $params = $this->request->param();
            $validate = new WechatBind();
            $validate->scene('bind')->batch()->check($params);
            $msg = $validate->getError();
            if ( $validate->scene('bind')->batch()->check($params)){
                $user = db('comp')->where(['wxuid' => $params['wxuid']])->find();
                if ($user){
                    if($params['wxuid'] == $user['wxuid']){
                        $this->success('已绑定，无须再次绑定',[]);
                    }
                }else{
                    $this->error('未绑定',[]);
                }
            }else{
                $this->error('参数错误',$msg);
            }
        }

    }
}