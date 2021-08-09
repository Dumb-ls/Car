<?php


namespace app\api\controller;


use app\common\controller\Api;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use think\Db;

class Report extends Base
{

    // 无需登录的接口,*表示全部
    protected $noNeedLogin = ['*'];
    // 无需鉴权的接口,*表示全部
    protected $noNeedRight = ['*'];

    /**
     * 获取报表记录信息  ------------C#
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/report/report)
     * @ApiParams   (name="comp_id", type="int", required=true, description="机构id")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    })
     */

    public function report()
    {
        if ($this->request->isPost()){
            $params = $this->request->param();
            $validate = new \app\api\validate\Report();
            $validate->scene('report')->check($params);
            $msg = $validate->getError();
            if ($validate->scene('report')->check($params)){
                $compid = $params['comp_id'];
                //获取报表信息
                $datas = Db::table('fa_report')->where(['comp_id'=>$compid,'deletetime'=>null])->select();
                for ($i=0;$i<count($datas);$i++){
                    $comp = Db::name('comp')->where(['id'=>$datas[$i]['comp_id']])->find();
                    $dept = Db::name('dept')->where(['id'=>$datas[$i]['dept_id']])->find();
                    $datas[$i]['compname'] = $comp['compname'];
                    $datas[$i]['deptname'] = $dept['deptname'];
                }
                $datas?$this->success('操作成功',$datas):$this->error('无数据',[]);
            }else{
                $this->error('参数错误',$msg);
            }

        }

    }

    /**
     * 添加报表记录并生成excel存储在本地  ------------C#
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/report/addReport)
     * @ApiParams   (name="repname", type="int", required=true, description="报表名称")
     * @ApiParams   (name="comp_id", type="int", required=true, description="机构id")
     * @ApiParams   (name="reptype", type="int", required=true, description="报表类型,只有三种类型：流水表，按天统计表，按卡号统计表")
     *  @ApiParams   (name="dept_id", type="int", required=false, description="机构id")
     *  @ApiParams   (name="dev_id", type="int", required=false, description="机构id")
     *  @ApiParams   (name="card_id", type="int", required=false, description="机构id")
     *  @ApiParams   (name="stime", type="string", required=true, description="开始时间，格式：yyyy-mm-dd H:i:s")
     *  @ApiParams   (name="etime", type="string", required=true, description="结束时间，格式：yyyy-mm-dd H:i:s")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    })
     */

    public function addReport()
    {
        if ($this->request->isPost()){
            $params = $this->request->param();
            $validate = new \app\api\validate\Report();
            $validate->scene('add')->check($params);
            $msg = $validate->getError();
            if ($validate->scene('add')->check($params)){

                $compid = $params['comp_id'];
                $now = time();
                //将数据传入msg函数进行查找筛选用以形成报表
                if ($params['reptype']=='按天统计表' || $params['reptype']=='按卡号统计表'){
                    $msg = self::msg($params,$compid);
                    if ($msg == null){
                        $this->error(__('当前无数据'));
                    }
                    self::cardAndDay($msg,$params['repname'],$now,$params['reptype'],$compid);
                }else{
                    $res = self::sum($params);
                    if ($res == null){
                        $this->error(__('当前无数据'));
                    }
                    self::export($res,$params['repname'],$compid,$now);
                }

                    //获取报表信息
//                    $arr = $this->sum($params);
                    //根据返回信息修改params内容用于将记录写入数据库
//                    $this->export($arr,$params['repname'],$compid);
                    $data = [
//                        'repname' => $params['repname'],
//                        'reptype' => '统计表',
//                        'comp_id' => $params['comp_id'],
//                        'repdate' => time(),
//                        'repjson' => '读卡数据统计',
//                        'createtime' => time()

                        'repname' => $params['repname'],
                        'reptype' => $params['reptype'],
                        'comp_id' => $compid,
                        'dept_id' => $params['dept_id'],
                        'repdate' => $now,
                        'repjson' => '读卡数据统计',
                        'createtime' => $now
                    ];
                (!empty($params['dept_id'])) ? $data['dept_id'] = $params['dept_id'] : $data['dept_id']='ALL';
                    $datas = Db::name('report')->insert($data);
                    $datas?$this->success('统计成功'):$this->error('统计失败');


            }else{
                $this->error('参数错误',$msg);
            }

        }
    }


    /**
     * 删除报表信息
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/report/delReport)
     * @ApiParams   (name="id", type="string", required=true, description="报表id")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    })
     */
    public function delReport()
    {
        if ($this->request->isPost()) {
            $params = $this->request->param();
            $validate = new \app\api\validate\Report();
            $validate->scene('del')->batch()->check($params);
            $msg = $validate->getError();
            if (!$validate->scene('del')->batch()->check($params)){
                $this->error('参数错误',$msg);
            }else{
                if (Db::name('report')->where($params)->find()){
                    $del = db('report')->where($params)->update(['deletetime'=>time()]);
                    $this->success('删除成功');
                }else{
                    $this->error('信息不存在');
                }



            }


        }
    }

    //导出表格存储本地
    public function export($datas,$fileName,$compid,$now)
    {

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
//设置sheet的名字  两种方法
        $sheet->setTitle('统计表');
        $spreadsheet->getActiveSheet()->setTitle('流水表表');
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

//第一种保存方式
        $writer = new Xlsx($spreadsheet);
//        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

        //保存的路径可自行设置
        $fileName =  date('Y-m-d', $now).'-'.$fileName;
        $file_name = ROOT_PATH . 'public' . DS . 'file' . DS .'excel' . DS . $fileName.'-'. $compid  . ".xlsx";
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

    /**
     * 下载报表  ------------C#
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/report/download)
     * @ApiParams   (name="id", type="int", required=true, description="报表id")
     * @ApiParams   (name="comp_id", type="int", required=true, description="机构id")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    })
     */
    public function download()
    {
        if ($this->request->isPost()){
            $param = $this->request->param();
            $validate = new \app\api\validate\Report();
            $validate->scene('download')->check($param);
            $msg = $validate->getError();
            if ($validate->scene('download')->check($param)){
                $id = $this->request->param('id');
                $data = Db::table('fa_report')->where(['id'=>$id])->find();
                $name = iconv("UTF-8","GB2312",$data['repname']);
                $fileName =  date('Y-m-d', $data['createtime']).'-'.$data['repname'].'-'.$param['comp_id'].".xlsx";

                $file_path = ROOT_PATH . 'public' . DS .'/file/excel/'.$fileName; //文件的存放目录
                if(!file_exists($file_path)){
                     $this->error('文件不存在');
                 }else {
                    $path = config('fastadmin.api_url');
                    $filepath = $path.'/file/excel/'.$fileName;
                    $result = [
                        'code' => 1,
                        'msg'  => '获取成功',
                        'url' => $filepath

                    ];
                    return json($result);
//                   //打开文件
//                    $file1 = fopen($file_path, "r");
//                    //输入文件标签
//                    Header("Content-type: application/octet-stream");
//                    Header("Accept-Ranges: bytes");
//                    Header("Accept-Length: " . filesize($file_path));
//                    Header("Content-Disposition: attachment; filename=" . $fileName);
//                    ob_clean();
//                    flush();
//                    echo fread($file1, filesize($file_path));
//                    fclose($file1);
                }

//                $this->success('下载成功',['address'=>$file_path]);
            }else{
                $this->error('参数错误',$msg);
            }
        }

    }

    /**
     * 统计
     */
    public function sum($arr)
    {
        $name = $arr['repname'];
        $compid = $arr['comp_id'];;
        $stime = strtotime($arr['stime']);
        $etime = strtotime($arr['etime']);
        $where = [
            'createtime' => array(array('EGT', $stime), array('ELT', $etime)),
            'deletetime' => null
        ];
        (!empty($arr['dept_id'])) ? $where['dept_id'] = $arr['dept_id'] : false;
        (!empty($arr['card_id'])) ? $where['card_id'] = $arr['card_id'] : false;
        (!empty($arr['dev_id'])) ? $where['dev_id'] = $arr['dev_id'] : false;
        $where['comp_id'] = $compid;
        //获取读卡信息
        $datas = Db::table('fa_cardlog')->where($where)->select();
        $where1 = [];
        for ($i=0;$i<count($datas);$i++){
            $compname = Db::name('comp')->where(['id'=>$compid])->find();
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

    protected function msg($arr,$compid){
        $data = [];


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
        $where['comp_id']=$compid;
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

    public function cardAndDay($datas,$fileName,$time,$title,$compid){
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


        ob_clean();
        flush();
        $file_name = date('Y-m-d', $time).'-'.$fileName;
//第一种保存方式
        $writer = new Xlsx($spreadsheet);
//        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

        //保存的路径可自行设置

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