<?php


namespace app\api\controller;


use app\api\validate\AddCard;
use think\Db;

class Card extends Base
{
    // 无需登录的接口,*表示全部
    protected $noNeedLogin = ['*'];
    // 无需鉴权的接口,*表示全部
    protected $noNeedRight = ['*'];
    /**
     * 新建车卡
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/card/addCard)
     * @ApiParams   (name="comp_id", type="int", required=true, description="机构id")
     * @ApiParams   (name="cardnum", type="string", required=true, description="卡号")
     * @ApiParams   (name="carduid", type="string", required=true, description="卡芯片号")
     * @ApiParams   (name="cardname", type="string", required=true, description="卡名称")
     * @ApiParams   (name="dept_id", type="strin", required=true, description="工区编号")
     * @ApiParams   (name="cardjson", type="string", required=true, description="卡信息")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    })
     */
    public function addCard()
    {
        if ($this->request->isPost()) {
            $params = $this->request->param();
            $validate = new AddCard();
            $validate->scene('add')->batch()->check($params);
            $msg = $validate->getError();
            if (!$validate->scene('add')->batch()->check($params)){
                $this->error('参数错误',$msg);
            }else{
                $num = Db::name('card')->where(['cardnum'=>$params['cardnum'],'deletetime' => null, 'cardswitch' => 1])->find();
                $uid = Db::name('card')->where(['carduid'=>$params['carduid'],'deletetime' => null, 'cardswitch' => 1])->find();
                if ($num && $uid){
                    $this->error('卡号与uid均已存在');
                }
                elseif ($num){
                    $this->error('卡号已存在');
                }elseif ($uid){
                    $this->error('uid已存在');
                }else{
                    $now = date('Y-m-d H:i:s',time());
                    $params['cardtime'] = strtotime('+1years',strtotime($now));
                    $params['createtime'] = time();
                    $params['cardswitch'] = 1;
                    $insert = db('card')->insert($params);
                    $this->success('添加成功');
                }


            }


        }

    }
    /**
     * 获取相应机构车卡信息
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/card/getCard)
     * @ApiParams   (name="comp_id", type="int", required=true, description="机构id")
     * @ApiParams   (name="dept_id", type="int", required=true, description="工区id，可选")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    })
     */
    public function getCard()
    {
        if ($this->request->isPost()) {
            $params = $this->request->param();
            $validate = new AddCard();
            $validate->scene('get')->batch()->check($params);
            $msg = $validate->getError();
            if (!$validate->scene('get')->batch()->check($params)){
                $this->error('参数错误',$msg);
            }else{
                $where1 = [
                    'comp_id' => $params['comp_id'],
                    'deletetime'=>null
                ];
                (!empty($params['dept_id']))?$where1['dept_id']=$params['dept_id']:false;
                $find = db('card')->where($where1)->select();
                if (!$find){
                    $this->error("无数据",[]);
                }else{
                    $where = [
                        'comp_id' => $params['comp_id'],
                        'deletetime'=>null,
                        'cardswitch'=>1
                    ];
                    (!empty($params['dept_id']))?$where['dept_id']=$params['dept_id']:false;
                    $data = db('card')->where($where)->select();
                    for ($i=0;$i<count($data);$i++){
                        $dept = Db::name('dept')->where(['id'=>$data[$i]['dept_id'],'deletetime'=>null])->find();
                        if ($dept){
                            $data[$i]['deptname'] = $dept['deptname'];
                        }else{
                            $data[$i]['deptname'] = '-';
                        }

                    }
                    $this->success('操作成功',$data);
                }


            }


        }
    }

    /**
     * 修改车卡信息
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/card/updateCard)
     * @ApiParams   (name="id", type="int", required=true, description="卡id")
     * @ApiParams   (name="cardnum", type="string", required=true, description="卡号")
     * @ApiParams   (name="cardname", type="string", required=true, description="卡名称")
     * @ApiParams   (name="dept_id", type="string", required=true, description="工区id")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    })
     */
    public function updateCard()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post();
            $validate = new AddCard();
            $validate->scene('update')->batch()->check($params);
            $msg = $validate->getError();
            if (!$validate->scene('update')->batch()->check($params)){
                $this->error('参数错误',$msg);
            }else{

                $info = [];
                (!empty($params['cardname']))?$info['cardname']=$params['cardname']:false;
                (!empty($params['carduid']))?$info['carduid']=$params['carduid']:false;
                (!empty($params['cardnum']))?$info['cardnum']=$params['cardnum']:false;
                (!empty($params['dept_id']))?$info['dept_id']=$params['dept_id']:false;
                $insert = db('card')->where(['id' => $params['id']])->update($info);
                if ($insert){

                $this->success('更新成功',[]);

                }else{
                    $this->error('未作改动',[]);
                }


            }


        }

    }

    /**
     * 删除车卡信息
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/card/delCard)
     * @ApiParams   (name="cardnum", type="string", required=true, description="卡号")
     * @ApiParams   (name="comp_id", type="string", required=true, description="所属机构id")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    })
     */
    public function delCard()
    {
        if ($this->request->isPost()) {
            $params = $this->request->param();
            $validate = new AddCard();
            $validate->scene('del')->batch()->check($params);
            $msg = $validate->getError();
            if (!$validate->scene('del')->batch()->check($params)){
                $this->error('参数错误',$msg);
            }else{
               db('card')->where($params)->update(['deletetime'=>time()]);
               $this->success('删除成功');
                

            }


        }
    }

    /**
     * 查询车卡是否存在
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/card/hasCard)
     * @ApiParams   (name="cardnum", type="string", required=true, description="卡号")
     * @ApiParams   (name="carduid", type="string", required=true, description="卡uid")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    })
     */
    public function hasCard()
    {
        if ($this->request->isPost()) {
            $params = $this->request->param();
            if (!$params){
                $this->error('参数错误');
            }else{
                $where1 = [
                    'deletetime' => null,
                    'cardswitch' => 1
                ];
                $where2 = [
                    'deletetime' => null,
                    'cardswitch' => 1
                ];
                (!empty($params['cardnum']))?$where1['cardnum']=$params['cardnum']:false;
                (!empty($params['carduid']))?$where2['carduid']=$params['carduid']:false;
                $cardnum = Db::name('card')->where($where1)->find();
                $carduid = Db::name('card')->where($where2)->find();

                if ($cardnum || $carduid){
                    $this->error('已存在');
                }else{
                    $this->success('不存在');
                }
            }


        }
    }

    /**
     * 停用车卡
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/card/stopCard)
     * @ApiParams   (name="cardnum", type="string", required=true, description="卡号")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    })
     */
    public function stopCard()
    {
        if ($this->request->isPost()) {
            $params = $this->request->param();
            $validate = new AddCard();
            $validate->scene('stop')->batch()->check($params);
            $msg = $validate->getError();
            if (!$validate->scene('stop')->batch()->check($params)){
                $this->error('参数错误',$msg);
            }else{
                $infos = [
                    'cardswitch' => 0
                ];
                $flag = Db::name('card')->where(['cardnum'=>$params['cardnum']])->update($infos);
                if ($flag){
                    $this->success('停用成功');
                }
            }
        }
    }

    /**
     * 停用车卡，卡号不变，换uid
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/card/changeCardnum)
     * @ApiParams   (name="cardnum", type="string", required=true, description="卡号")
     * @ApiParams   (name="carduid", type="string", required=true, description="新卡uid")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    })
     */
    public function changeCardnum()
    {
        if ($this->request->isPost()) {
            $params = $this->request->param();
            $validate = new AddCard();
            $validate->scene('stop')->batch()->check($params);
            $msg = $validate->getError();
            if (!$validate->scene('stop')->batch()->check($params)){
                $this->error('参数错误',$msg);
            }else{
                $card = Db::name('card')->where(['carduid'=>$params['carduid'],'deletetime'=>null])->find();
                $infos = [
                    'cardswitch' => 0
                ];
                Db::name('card')->where(['carduid'=>$params['carduid']])->update($infos);
                $info = [
                    'cardnum' => $card['cardnum'],
                    'comp_id' => $card['comp_id'],
                    'carduid' => $params['carduid'],
                    'cardname' => $card['cardname'],
                    'dept_id' => $card['dept_id'],
                    'cardjson' => $card['cardjson'],
                    'cardswitch' => $card['cardswitch'],
                    'cardtime' => $card['cardtime'],
                    'createtime' => time()
                ];
                $flag =  Db::name('card')->insert($info);
                if ($flag){
                    $this->success('更换成功');
                }
            }
        }
    }

    /**
     * 使用卡号或者uid查询车卡信息
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/card/msgByNumOrUid)
     * @ApiParams   (name="cardnum", type="string", required=false, description="卡号")
     * @ApiParams   (name="carduid", type="string", required=false, description="卡uid")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    })
     */
    public function msgByNumOrUid()
    {
        if ($this->request->isPost()) {
            $params = $this->request->param();
            if (empty($params['cardnum']) && empty($params['carduid'])){
                $this->error('参数不能为空');
            }else{
                $where['deletetime'] = null;
                $where['cardswitch'] = 1;
                (!empty($params['cardnum']))?$where['cardnum']=$params['cardnum']:false;
                (!empty($params['carduid']))?$where['carduid']=$params['carduid']:false;
                $data = Db::name('card')->where($where)->find();
                if ($data){
                    $comp = Db::name('comp')->where(['id'=>$data['comp_id']])->find();
                    $dept = Db::name('dept')->where(['id'=>$data['dept_id']])->find();
                    for ($i=0;$i<count($data);$i++){
                        $data['compname'] = $comp['compname'];
                        $data['deptname'] = $dept['deptname'];
                    }
                    $this->success('查询成功',$data);
                }else{
                    $this->success('信息不存在');
                }
            }


        }
    }

    /**
     * 模糊查询车卡
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/card/Card)
     * @ApiParams   (name="comp_id", type="int", required=true, description="机构id")
     * @ApiParams   (name="cardnum", type="string", required=false, description="卡号，支持模糊查询")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    })
     */
    public function Card()
    {
        if ($this->request->isPost()) {
            $params = $this->request->param();
            $validate = new AddCard();
            $validate->scene('get')->batch()->check($params);
            $msg = $validate->getError();
            if (!$validate->scene('get')->batch()->check($params)){
                $this->error('参数错误',$msg);
            }else{
                $where = [
                    'comp_id' => $params['comp_id'],
                    'deletetime'=>null
                ];
                (!empty($params['cardnum']))?$where['cardnum']=['like','%'.$params['cardnum'].'%']:false;

                $version = db('card')->where($where)->find();
                if (!$version){
                    $this->error("该卡不存在",[]);
                }else{
                    $where['cardswitch'] = 1;
                    $data = db('card')->where($where)->select();
                    for ($i=0;$i<count($data);$i++){
                        $dept = Db::name('dept')->where(['id'=>$data[$i]['dept_id'],'deletetime'=>null])->find();
                        if ($dept){
                            $data[$i]['deptname'] = $dept['deptname'];
                        }

                    }
                    $this->success('操作成功',$data);
                }


            }


        }
    }
}