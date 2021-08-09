<?php


namespace app\api\controller;


use app\api\validate\UpdateVersion;
use app\common\controller\Api;
use think\Exception;
use think\exception\PDOException;
use think\exception\ValidateException;

class Version extends Base
{
    //如果$noNeedLogin为空表示所有接口都需要登录才能请求
    //如果$noNeedRight为空表示所有接口都需要验证权限才能请求
    //如果接口已经设置无需登录,那也就无需鉴权了
    //
    // 无需登录的接口,*表示全部
    protected $noNeedLogin = ['getVersion','updateVersion'];
    // 无需鉴权的接口,*表示全部
    protected $noNeedRight = ['getVersion'];
    /**
     * 获取版本信息
     * @ApiSummary  (测试描述信息)
     * @ApiMethod   (GET)
     * @ApiRoute    (/api/version/getVersion)
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    })
     */
    public function getVersion()
    {
        if ($this->request->isGet()) {
             $data = db('version')->select();
             $this->success('操作成功',$data);

        }
    }
    /**
     * 更新版本信息
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/version/updateVersion)
     * @ApiParams   (name="oldversion", type="string", required=true, description="旧版本号")
     * @ApiParams   (name="newversion", type="string", required=true, description="新版本号")
     * @ApiParams   (name="packagesize", type="string", required=true, description="包大小，比如：**M")
     * @ApiParams   (name="downloadurl", type="string", required=true, description="下载地址")
     * @ApiParams   (name="enforce", type="strin", required=true, description="强制更新，值为0或1")
     * @ApiParams   (name="weigh", type="string", required=true, description="权重，值为整型")
     * @ApiParams   (name="status", type="string", required=true, description="状态，值为normal或hidden")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    })
     */
    public function updateVersion()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post();
            $validate = new UpdateVersion();
            $validate->batch()->check($params);
            $msg = $validate->getError();
            $version = db('version')->where(['oldversion' => $params['oldversion']])->find();
            if (!$version){
                $this->error("旧版本号不存在",[]);
            }else{
                if (!$validate->batch()->check($params)){
                    $this->error('参数错误',$msg);
                }else{
                    $params['updatetime'] = time();
                    $insert = db('version')->where(['oldversion' => $params['oldversion']])->update($params);
                    $this->success('更新成功',[]);


                }
            }

        }
    }

}