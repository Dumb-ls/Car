<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;

/**
 * 机构用户管理
 *
 * @icon fa fa-circle-o
 */
class Comp extends Backend
{
    protected $noNeedRight = ['get_type_list','otherIndex'];
    protected $multiFields = 'compswitch';
    /**
     * Comp模型对象
     * @var \app\admin\model\Comp
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Comp;

    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


    /**
     * 查看
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            $compid = input('session.')['admin']['comp_id'];
            $map = [];
            ($compid>0)?$map['comp_id']=$compid:false;
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->where($where)
                ->where($map)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->where($where)
                ->where($map)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 根据权限查看
     */
    public function otherIndex()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }

    public function get_type_list()
    {
        $result = array("rows" => [], "total" => 0);
        if ($this->request->isAjax()) {
//            if ($this->request->request("keyValue")) {
//                $id = $this->request->request("keyValue");
//        //这里的model，就是需要查找的那个数据库的模型，根据实际情况做更改
//                $type = $this->model;
//                $list = $type
//                    ->field("id,title")
//        //这里的条件，就是需要自己进行筛选的条件，根据实际情况做更改
//                    //->where("firmid", $this->auth->firmid)
//                    ->where("id", $id)
//                    ->select();
//                return ['total' => 1, 'list' => $list];
//            }
            $compid = input('session.')['admin']['comp_id'];
            //取回原有数据
            if($this->request->request("keyValue")){
                $id = $this->request->param("keyValue");
                $list = $this->model
                    ->where(['id' => $id])
                    //这里的条件，就是需要自己进行筛选的条件，根据实际情况做更改
                    //->where("firmid", $this->auth->firmid)
                    ->select();
                $count = $this->model
                    //这里的title，就是前段需要展示的，即：data-field的值
                    ->where(['id' => $id])
                    //->where("firmid", $this->auth->firmid)
                    ->count();
                $result = array("rows" => $list, "total" => $count);
                return  json($result);
            }
        //这里的model，就是需要查找的那个数据库的模型，根据实际情况做更改
            $type = $this->model;
            $arr = [];
            if($compid>0){
                $compid = input('session.')['admin']['comp_id'];
                $map = [];
                ($compid>0)?$map['id']=$compid:false;
                $map['deletetime'] = null;
                $map['compswitch'] = 1;
                $list = $type
                    ->where($map)
                    //这里的条件，就是需要自己进行筛选的条件，根据实际情况做更改
                    //->where("firmid", $this->auth->firmid)
                    ->select();
                $count = $type
                    //这里的title，就是前段需要展示的，即：data-field的值
                    ->where($map)
                    //->where("firmid", $this->auth->firmid)
                    ->count();
            }else{
                $list = $type
                    ->field("id,compname")
                    //这里的条件，就是需要自己进行筛选的条件，根据实际情况做更改
                    //->where("firmid", $this->auth->firmid)
                    ->select();
                $count = $type
                    //这里的title，就是前段需要展示的，即：data-field的值
                    ->field("id")
                    //->where("firmid", $this->auth->firmid)
                    ->count();
            }
//            dump($arr);
            $result = array("rows" => $list, "total" => $count);
            return json($result);
        }
        return json($result);
    }
    /**
     * 添加
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                $params = $this->preExcludeFields($params);

                $salt = config('car.car_salts');
                $params['comppass'] = md5($params['comppass'].$salt);
                $params['compsk'] = md5($params['compuser'].$salt);
                $params['compvk'] = md5($params['comppass'].$salt);
                ((empty($params['compjson'])))?$params['compjson']='-':false;
                if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                    $params[$this->dataLimitField] = $this->auth->id;
                }
                if (Db::name('comp')->where(['compuser'=>$params['compuser']])->count()){
                    $this->error('用户名已存在');
                }else{
                    $result = false;
                    Db::startTrans();
                    try {
                        //是否采用模型验证
                        if ($this->modelValidate) {
                            $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                            $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                            $this->model->validateFailException(true)->validate($validate);
                        }
                        $result = $this->model->allowField(true)->save($params);
                        Db::commit();
                    } catch (ValidateException $e) {
                        Db::rollback();
                        $this->error($e->getMessage());
                    } catch (PDOException $e) {
                        Db::rollback();
                        $this->error($e->getMessage());
                    } catch (Exception $e) {
                        Db::rollback();
                        $this->error($e->getMessage());
                    }
                    if ($result !== false) {
                        $this->success();
                    } else {
                        $this->error(__('No rows were inserted'));
                    }
                }

            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        return $this->view->fetch();
    }

    /**
     * 编辑
     */
    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                $params = $this->preExcludeFields($params);
                $salt = config('car.car_salts');
                $params['compsk'] = md5($params['compuser'].$salt);
                $params['compvk'] = md5($params['comppass'].$salt);
                $result = false;
                Db::startTrans();
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                        $row->validateFailException(true)->validate($validate);
                    }
                    $result = $row->allowField(true)->save($params);
                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $this->success();
                } else {
                    $this->error(__('No rows were updated'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

    /**
     * 重置密码为123456
     */
    public function reset()
    {
        $id = $this->request->param('ids');
        $salt = config('car.car_salts');
        $password = md5('123456'.$salt);
        Db::name('comp')->where(['id'=>$id])->update(['comppass'=>$password]);
        $this->success();

    }

    public function clear()
    {
        $id = $this->request->param('ids');
        Db::name('comp')->where(['id'=>$id])->update(['wxuid'=>null]);
        $this->success();
    }


}
