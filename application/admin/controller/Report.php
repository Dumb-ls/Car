<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;
use PhpOffice\PhpSpreadsheet\Helper\Sample;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

/**
 * 报表统计管理
 *
 * @icon fa fa-circle-o
 */
class Report extends Backend
{

    /**
     * Report模型对象
     * @var \app\admin\model\Report
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Report;


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
     * 添加
     */
    public function add()
    {
        if ($this->request->isPost()) {

            $params = $this->request->post("row/a");
            if ($params) {
                $params = $this->preExcludeFields($params);
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
                    $now = time();
                    //将数据传入msg函数进行查找筛选用以形成报表
                    if ($params['reptype']=='按天统计表' || $params['reptype']=='按卡号统计表'){
                        $msg = self::msg($params);
                        if ($msg == null){
                            $this->error(__('当前无数据'));
                        }
                        self::cardAndDay($msg,$params['repname'],$now,$params['reptype']);
                    }else{
                        $res = self::sum($params);
                        if ($res == null){
                            $this->error(__('当前无数据'));
                        }
                        self::export($res,$params['repname'],$now);
                    }

                    $compid = input('session.')['admin']['comp_id'];
                    //根据返回信息修改params内容用于将记录写入数据库
                    $data = [
                        'repname' => $params['repname'],
                        'reptype' => $params['reptype'],
                        'comp_id' => $compid,
                        'dept_id' => $params['dept_id'],
                        'repdate' => $now,
                        'repjson' => '读卡数据统计',
                        'createtime' => $now
                    ];
                    $result = $this->model->allowField(true)->save($data);
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
     * 统计
     */
    public function sum($arr)
    {
        $name = $arr['repname'];
        $compid = input('session.')['admin']['comp_id'];
        $deptid = $arr['dept_id'];
        $cardid = $arr['card_id'];
        $devid = $arr['dev_id'];
        $stime = strtotime($arr['stime']);
        $etime = strtotime($arr['etime']);
        $where = [
            'createtime' => array(array('EGT', $stime), array('ELT', $etime)),
            'deletetime' => null
        ];
        (!empty($deptid))?$where['dept_id']=$deptid:false;
        (!empty($cardid))?$where['card_id']=$deptid:false;
        (!empty($devid))?$where['dev_id']=$devid:false;
        ($compid>0)?$where['comp_id']=$compid:false;
        ($compid>0)?$where1['comp_id']=$compid:false;
//        dump($where);
//        die();
        //获取读卡信息
        $datas = Db::table('fa_cardlog')->where($where)->select();
        $where1 = [];
        for ($i=0;$i<count($datas);$i++){
            $compname = Db::name('comp')->where($where1)->find();
            $where1['id'] = $datas[$i]['dept_id'];
            $deptname = Db::name('dept')->where($where1)->find();
            $where1['id'] = $datas[$i]['dev_id'];
            $devname = Db::name('dev')->where($where1)->find();
            $where1['id'] = $datas[$i]['card_id'];
            $cardname = Db::name('card')->where($where1)->find();
            $datas[$i]['compname'] = $compname['compname'];
            $datas[$i]['deptname'] = $deptname['deptname'];
            $datas[$i]['devname'] = $devname['devname'];
            $datas[$i]['cardname'] = $cardname['cardname'];
        }

        return $datas;

    }
    protected function msg($arr){
        $data = [];
        $compid = input('session.')['admin']['comp_id'];

        $deptid = $arr['dept_id'];
        $cardid = $arr['card_id'];
        $devid = $arr['dev_id'];
        $stime = strtotime($arr['stime']);
        $etime = strtotime($arr['etime']);
        $where = [
            'createtime' => array(array('EGT', $stime), array('ELT', $etime)),
            'deletetime' => null
        ];
        (!empty($deptid))?$where['dept_id']=$deptid:false;
        (!empty($cardid))?$where['card_id']=$deptid:false;
        (!empty($devid))?$where['dev_id']=$devid:false;
        ($compid>0)?$where['comp_id']=$compid:false;
        $total = 0;
        if ($arr['reptype'] == '按卡号统计表'){
            $data = Db::name('cardlog')->where($where)->field('card_id,cardnum,count(cardnum) as count')->group('cardnum')->select();
        }else{
            $data = Db::name('cardlog')->where($where)->field('card_id,cardnum,logdt,count(logdt) as count')->group('logdt')->select();
        }
        for ($i=0;$i<count($data);$i++){
            $cardname = Db::name('card')->where(['id'=>$data[$i]['card_id']])->find();
            $data[$i]['cardname']=$cardname['cardname'];
        }
        return $data;
    }
    //导出表格存储本地
    public function export($datas,$fileName,$time)
    {

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
//设置sheet的名字  两种方法
        $sheet->setTitle('流水表');
        $spreadsheet->getActiveSheet()->setTitle('流水表');
//设置第一行小标题
        $k = 1;
        $sheet->setCellValue('A1', '编号');
        $sheet->setCellValue('B1', '设备号');
        $sheet->setCellValue('C1', '设备名');
        $sheet->setCellValue('D1', '工区');
        $sheet->setCellValue('E1', '车号');
        $sheet->setCellValue('F1', '卡号');
        $sheet->setCellValue('G1', '时间');
        $sheet->setCellValue('H1', '共计');
//设置A单元格的宽度 同理设置每个
//        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(20);
//设置第三行的高度
//        $spreadsheet->getActiveSheet()->getRowDimension('3')->setRowHeight(50);
//A1水平居中
//        $styleArray = [
//            'alignment' => [
//                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
//            ],
//        ];
//        $sheet->getStyle('A1')->applyFromArray($styleArray);
//将A3到D4合并成一个单元格
//        $spreadsheet->getActiveSheet()->mergeCells('A3:D4');
//拆分合并单元格
//        $spreadsheet->getActiveSheet()->unmergeCells('A3:D4');
//将A2到D8表格边框 改变为红色
//        $styleArray = [
//            'borders' => [
//                'outline' => [
//                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
//                    'color' => ['argb' => 'FFFF0000'],
//                ],
//            ],
//        ];
//        $sheet->getStyle('A2:D8')->applyFromArray($styleArray);
//设置超链接
//        $sheet->setCellValue('D6', 'www.baidu.com');
//        $spreadsheet->getActiveSheet()->setCellValue('E6', 'www.baidu.com');
//循环赋值
        $row = 2;
        for ($i=0;$i<count($datas);$i++){

            $sheet->setCellValue('A'.$row, $datas[$i]['id']."\t");
            $sheet->setCellValue('B'.$row, $datas[$i]['devnum']."\t");
            $sheet->setCellValue('C'.$row, $datas[$i]['devname']."\t");
            $sheet->setCellValue('D'.$row, $datas[$i]['deptname']."\t");
            $sheet->setCellValue('E'.$row, $datas[$i]['cardname']."\t");
            $sheet->setCellValue('F'.$row, $datas[$i]['cardnum']."\t");
            $sheet->setCellValue('G'.$row, $datas[$i]['logdt']."\t");
            $row++;

        }
        $sheet->setCellValue('H2', count($datas).'趟');
//
        ob_clean();
        flush();
        $file_name = date('Y-m-d', $time).'-'.$fileName;
//第一种保存方式
        $writer = new Xlsx($spreadsheet);
//        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

        //保存的路径可自行设置
        $compid = input('session.')['admin']['comp_id'];
//        $comp = Db::name('report')->where(['repname'=>$fileName])->find();
//        ($compids>1)?$compid=$compids:$compid=$comp['comp_id'];
        $file_name = ROOT_PATH . 'public' . DS . 'file' . DS .'excel' . DS . $file_name.'-'.$compid. ".xlsx";
        $writer->save($file_name);
//第二种直接页面上显示下载
//        $file_name = $file_name . ".xlsx";
//        header('Content-Type: application/vnd.ms-excel');
//        header('Content-Disposition: attachment;filename="'.$file_name.'"');
//        header('Cache-Control: max-age=0');
//        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
////注意createWriter($spreadsheet, 'Xls') 第二个参数首字母必须大写
//        $writer->save('php://output');
    }

    //下载excel
    public function download()
    {
        $id = $this->request->param('ids');
       $data = Db::table('fa_report')->where(['id'=>$id])->find();


        $compids = input('session.')['admin']['comp_id'];
        $comp = Db::name('report')->where(['id'=>$id])->find();
        ($compids>1)?$compid=$compids:$compid=$comp['comp_id'];
        $fileName =  date('Y-m-d', $comp['createtime']).'-'.$data['repname'].'-'.$compid;
        $file_path = ROOT_PATH . 'public' . DS . 'file' . DS .'excel' . DS . $fileName. ".xlsx"; //下载文件的存放目录
        if (!file_exists($file_path)) {
            $this->error(__('文件不存在!'));
        } else {
            $arr = explode(DS, $file_path);
            $file_name = $fileName.".xlsx";
            //打开文件
            $open_file = fopen($file_path, "r");
            //输入文件标签
            header("Content-type: application/octet-stream");
            header("Accept-Ranges: bytes");
            header("Accept-Length: " . filesize($file_path));
            header("Content-Disposition: attachment; filename=" . $file_name);
            ob_clean();
            flush();
            //输出文件内容
            echo fread($open_file, filesize($file_path));
            fclose($open_file);
            exit();
        }
    }

    public function cardAndDay($datas,$fileName,$time,$title){
        if ($title == '按卡号统计表'){
            $text = [
                'one' => '车号',
                'two' => '卡号',
                'three' => '次数'
            ];

        }else{
            $text = [
                'one' => '日期',
                'two' => '次数',

            ];
        }
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
//设置sheet的名字  两种方法
        $sheet->setTitle($title);
        $spreadsheet->getActiveSheet()->setTitle($title);
//设置第一行小标题
        $k = 1;

        $total = 0;
        for ($i=0;$i<count($datas);$i++){
            $total += $datas[$i]['count'];
        }
        $row = 2;
        if ($title == '按卡号统计表'){
            for ($i=0;$i<count($datas);$i++){
                $sheet->setCellValue('A1', $text['one']."\t");
                $sheet->setCellValue('B1', $text['two']."\t");
                $sheet->setCellValue('C1', $text['three']."\t");
                $sheet->setCellValue('D1', '共计');
                $sheet->setCellValue('A'.$row, $datas[$i]['cardname']."\t");
                $sheet->setCellValue('B'.$row, $datas[$i]['cardnum']."\t");
                $sheet->setCellValue('C'.$row, $datas[$i]['count']."\t");
                $row++;
                $sheet->setCellValue('D2', $total.'次');
            }

        }else{
            for ($i=0;$i<count($datas);$i++){
                $sheet->setCellValue('A1', $text['one']."\t");
                $sheet->setCellValue('B1', $text['two']."\t");
                $sheet->setCellValue('C1', '共计');
                $sheet->setCellValue('A'.$row, $datas[$i]['logdt']."\t");
                $sheet->setCellValue('B'.$row, $datas[$i]['count']."\t");
                $row++;
                $sheet->setCellValue('C2', $total.'次');
            }
        }


        $sheet->setCellValue('C2', $total.'次');
        ob_clean();
        flush();
        $file_name = date('Y-m-d', $time).'-'.$fileName;
//第一种保存方式
        $writer = new Xlsx($spreadsheet);
//        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

        //保存的路径可自行设置
        $compid = input('session.')['admin']['comp_id'];
//        $comp = Db::name('report')->where(['repname'=>$fileName])->find();
//        ($compids>1)?$compid=$compids:$compid=$comp['comp_id'];
        $file_name = ROOT_PATH . 'public' . DS . 'file' . DS .'excel' . DS . $file_name.'-'.$compid. ".xlsx";
        $writer->save($file_name);
//第二种直接页面上显示下载
//        $file_name = $file_name . ".xlsx";
//        header('Content-Type: application/vnd.ms-excel');
//        header('Content-Disposition: attachment;filename="'.$file_name.'"');
//        header('Cache-Control: max-age=0');
//        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
////注意createWriter($spreadsheet, 'Xls') 第二个参数首字母必须大写
//        $writer->save('php://output');

    }


}
