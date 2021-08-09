<?php


namespace app\api\controller;


use app\api\validate\AddDept;
use app\common\controller\Api;
use think\Db;

class Dept extends Base
{
    // 无需登录的接口,*表示全部
    protected $noNeedLogin = ['*'];
    // 无需鉴权的接口,*表示全部
    protected $noNeedRight = ['*'];
    /**
     * 新建工区
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/dept/addDept)
     * @ApiParams   (name="deptname", type="string", required=true, description="工区名称")
     * @ApiParams   (name="deptaddr", type="string", required=true, description="工区地址")
     * @ApiParams   (name="deptjson", type="string", required=true, description="工区信息")
     * @ApiParams   (name="comp_id", type="string", required=true, description="所属机构id")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    })
     */
    public function addDept()
    {
        if ($this->request->isPost()) {
            $params = $this->request->param();
            $validate = new AddDept();
            $validate->scene('add')->batch()->check($params);
            $msg = $validate->getError();
            if (!$validate->scene('add')->batch()->check($params)){
                $this->error('参数错误',$msg);
            }else{
                $count = Db::name('dept')->where(['comp_id'=>$params['comp_id'],'deptname'=>$params['deptname']])->count();
                if ($count){
                    $this->error('名称重复，请修改',['deptname已存在']);
                }else{
                    $params['deptjson'] = "";
                    $params['deptswitch'] = 1;
                    $params['createtime'] = time();
                    $insert = db('dept')->insert($params);
                    $this->success('添加成功',[]);
                }



            }


        }
    }
    /**
     * 获取工区信息
     * @ApiMethod(POST)
     * @ApiRoute    (/api/dept/getDept)
     * @ApiParams   (name="comp_id", type="int", required=true, description="所属机构id")
     * @ApiReturn   ({
    'code':'1',
    'msg':'操作成功'
    })
     */
    public function getDept()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post();
            $validate = new AddDept();
            $validate->scene('get')->batch()->check($params);
            $msg = $validate->getError();
            if (!$validate->scene('get')->batch()->check($params)){
                $this->error('参数错误',$msg);
            }else{
               $dept = db('dept')->where(['comp_id' => $params['comp_id'],'deletetime'=>null])->select();
               $comp = db('comp')->where(['id' => $params['comp_id'],'deletetime'=>null])->find();
               $data = [];
               for ($i = 0;$i < count($dept);$i++){

                       $data[$i]['id'] = $dept[$i]['id'];
                       $data[$i]['deptname'] = $dept[$i]['deptname'];
                       $data[$i]['comp_id'] = $params['comp_id'];
                       $data[$i]['compname'] = $comp['compname'];
                       $data[$i]['dept_id'] = $dept[$i]['id'];
                       $data[$i]['deptjson'] = $dept[$i]['deptjson'];
                       $data[$i]['deptswitch'] = $dept[$i]['deptswitch'];
                       $data[$i]['deptaddr'] = $dept[$i]['deptaddr'];


               }
                $this->success('操作成功',$data);

            }


        }
    }

    /**
     * 更新工区信息
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/dept/updateDept)
     * @ApiParams   (name="id", type="string", required=true, description="工区id")
     * @ApiParams   (name="comp_id", type="string", required=true, description="所属机构id")
     * @ApiParams   (name="deptname", type="string", required=true, description="工区名称")
     * @ApiParams   (name="deptaddr", type="string", required=true, description="工区地址")
     * @ApiParams   (name="deptjson", type="string", required=true, description="工区信息")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    })
     */
    public function updateDept()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post();
            $validate = new AddDept();
            $validate->scene('update')->batch()->check($params);
            $msg = $validate->getError();
            if (!$validate->scene('update')->batch()->check($params)){
                $this->error('参数错误',$msg);
            }else{
//                $deptname = $params['deptname'];
//                $deptaddr = $params['deptaddr'];
//                $deptjson = $params['deptjson'];
//                $compid = $params['comp_id'];
                $info = [];
                !empty($params['deptname'])?$info['deptname']=$params['deptname']:false;
                !empty($params['deptaddr'])?$info['deptaddr']=$params['deptaddr']:false;
                !empty($params['deptjson'])?$info['deptjson']=$params['deptjson']:false;
                !empty($params['comp_id'])?$info['comp_id']=$params['comp_id']:false;
                $insert = db('dept')->where(['id' => $params['id']])->update($info);
                if ($insert){

                    $this->success('更新成功',[]);

                }else{
                    $this->error('未作改动',[]);
                }


            }


        }
    }
    /**
     * 删除工区信息
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/dept/delDept)
     * @ApiParams   (name="id", type="string", required=true, description="工区id")
     * @ApiParams   (name="comp_id", type="string", required=true, description="所属机构id")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    })
     */
    public function delDept()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post();
            $validate = new AddDept();
            $validate->scene('update')->batch()->check($params);
            $msg = $validate->getError();
            if (!$validate->scene('update')->batch()->check($params)){
                $this->error('参数错误',$msg);
            }else{
                db('dept')->where($params)->update(['deletetime'=>time()]);
                $this->success('删除成功',[]);

            }


        }
    }

}