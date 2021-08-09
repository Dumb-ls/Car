<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;

/**
 * 机构设备管理
 *
 * @icon fa fa-circle-o
 */
class Dev extends Backend
{
    protected $noNeedRight = ['get_type_list','otherIndex'];
    protected $multiFields = 'devswitch';
    /**
     * Dev模型对象
     * @var \app\admin\model\Dev
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Dev;

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
        //当前是否为关联查询
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax())
        {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField'))
            {
                return $this->selectpage();
            }
            $compid = input('session.')['admin']['comp_id'];
            $map = [];
            ($compid>0)?$map['dev.comp_id']=$compid:false;
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                    ->with(['comp','dept'])
                    ->where($where)
                    ->where($map)
                    ->order($sort, $order)
                    ->count();

            $list = $this->model
                    ->with(['comp','dept'])
                    ->where($where)
                    ->where($map)
                    ->order($sort, $order)
                    ->limit($offset, $limit)
                    ->select();

            foreach ($list as $row) {
                
                $row->getRelation('comp')->visible(['compname']);
				$row->getRelation('dept')->visible(['deptname']);
            }
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
                ($compid>0)?$map['comp_id']=$compid:false;
                $map['deletetime'] = null;
                $map['devswitch'] = 1;
                $type = $this->model;
                $list = $type
                    ->field("id,devname,devnum")
                    ->where($map)
                    //这里的条件，就是需要自己进行筛选的条件，根据实际情况做更改
                    //->where("firmid", $this->auth->firmid)
                    ->select();
                $count = $type
                    //这里的title，就是前段需要展示的，即：data-field的值
                    ->field("id,devname,devnum")
                    ->where($map)
                    //->where("firmid", $this->auth->firmid)
                    ->count();
            }else{
                $type = $this->model;
                $list = $type
                    ->field("id,devname,devnum")
                    //这里的条件，就是需要自己进行筛选的条件，根据实际情况做更改
                    //->where("firmid", $this->auth->firmid)
                    ->select();
                $count = $type
                    //这里的title，就是前段需要展示的，即：data-field的值
                    ->field("id,devname,devnum")
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
                $compid = input('session.')['admin']['comp_id'];

                $map = [];
                ($compid>0)?$map['comp_id']=$compid:false;
//                ($compid>0)?$params['devswitch']=0:false;
                ((empty($params['devjson'])))?$params['devjson']='-':false;
                md5($params['devnum'].$salt);
                if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                    $params[$this->dataLimitField] = $this->auth->id;
                }
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
     * 批量更新
     */
    public function multi($ids = "")
    {
        $ids = $ids ? $ids : $this->request->param("ids");
        $compid = input('session.')['admin']['comp_id'];
        if ($ids) {
            if ($this->request->has('params')) {
                parse_str($this->request->post("params"), $values);
                $flag = $values['devswitch'];
                $values = $this->auth->isSuperAdmin() ? $values : array_intersect_key($values, array_flip(is_array($this->multiFields) ? $this->multiFields : explode(',', $this->multiFields)));
                if ($values) {
                    if ($compid>0){
                        if ($flag == 1){
                            $values['devswitch'] = 0;
                        }else{
                            $values['devswitch'] = 1;
                        }
                    }
                    $adminIds = $this->getDataLimitAdminIds();
                    if (is_array($adminIds)) {
                        $this->model->where($this->dataLimitField, 'in', $adminIds);
                    }
                    $count = 0;
                    Db::startTrans();
                    try {
                        $list = $this->model->where($this->model->getPk(), 'in', $ids)->select();
                        foreach ($list as $index => $item) {
                            $count += $item->allowField(true)->isUpdate(true)->save($values);
                        }
                        Db::commit();
                    } catch (PDOException $e) {
                        Db::rollback();
                        $this->error($e->getMessage());
                    } catch (Exception $e) {
                        Db::rollback();
                        $this->error($e->getMessage());
                    }
                    if ($count) {
                        $this->success();
                    } else {
                        $this->error(__('No rows were updated'));
                    }
                } else {
                    $this->error(__('You have no permission'));
                }
            }
        }
        $this->error(__('Parameter %s can not be empty', 'ids'));
    }

}
