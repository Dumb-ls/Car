<?php


namespace app\api\controller;

use app\common\controller\Photo;
use fast\Arr;
use think\Db;
use app\common\controller\Api;
use think\exception\HttpResponseException;
use think\File;
use think\Request;
use think\Response;

class Cardlog extends Base
{
    // 无需登录的接口,*表示全部
    protected $noNeedLogin = ['*'];
    // 无需鉴权的接口,*表示全部
    protected $noNeedRight = ['*'];


    /**
     * 写入读卡日志
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/cardlog/writeLog)
     * @ApiParams   (name="dev_id", type="int", required=true, description="设备编号")
     * @ApiParams   (name="devnum", type="int", required=true, description="设备号")
     * @ApiParams   (name="logdt", type="string", required=true, description="设备时间，格式：yyyy-mm-dd")
     * @ApiParams   (name="createtime", type="string", required=false, description="创建时间，可选，格式：yyyy-mm-dd HH:mm:ii")
     * @ApiParams   (name="card_id", type="string", required=true, description="卡编号")
     * @ApiParams   (name="cardnum", type="strin", required=true, description="卡号")
     * @ApiParams   (name="carpthoto", type="file", required=true, description="卡图片")
     * @ApiParams   (name="comp_id", type="string", required=true, description="机构编号")
     *  @ApiParams   (name="dept_id", type="string", required=true, description="工区编号")
     * @ApiParams   (name="cardjson", type="string", required=true, description="读卡信息")
     * @ApiParams   (name="logstat", type="string", required=true, description="记录状态")
     * @ApiParams   (name="uqnum", type="string", required=true, description="唯一码")
     * @ApiParams   (name="logswitch", type="string", required=true, description="记录标志")
     * @ApiReturn   ({
        'code':'1',
        'msg':'返回成功'
        })
     */
    public function writeLog()
    {
        if ($this->request->isPost()) {
            $params = $this->request->param();
            $validate = new \app\api\validate\Cardlog();
            $validate->scene('add')->batch()->check($params);
            $msg = $validate->getError();
            if (!$validate->scene('add')->batch()->check($params)){
                $this->error('参数错误',$msg);
            }else{
                if (Db::name('cardlog')->where(['uqnum'=>$params['uqnum']])->find()){
                    $this->error('唯一码已存在');
                }else{
                    if ($this->request->file()){
                        $pic = $this->request->file('carpthoto');
                        $path = $this->upload($pic);
                    }

//                    $params['logdt'] = $params['logdt'];
                    if (empty($params['createtime'])){
                        $params['createtime'] = time();
                    }else{
                        $params['createtime'] = strtotime($params['createtime']);
                    }
                    (!empty($path))?$params['carpthoto'] = $path:$params['carpthoto']='/assets/img/avatar.png';
                    $insert = db('cardlog')->insert($params);
                    $this->success('操作成功',[]);
                }


            }


        }

    }

    /**
     * 获取读卡信息，C#端
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/cardlog/getMsg)
     * @ApiParams   (name="comp_id", type="int", required=true, description="机构id")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    })
     */
    public function getMsg()
    {
        if ($this->request->isPost()) {
            $params = $this->request->param();

            $validate = new \app\api\validate\Cardlog();
            $validate->scene('C')->batch()->check($params);
            $msg = $validate->getError();
            if (!$validate->scene('C')->batch()->check($params)) {
                $this->error("参数错误", $msg);
            } else {
                $compid = $params['comp_id'];
                    $data = Db::name('cardlog')->where(['comp_id'=>$compid])->select();

                    $datas = $data;
                    for ($i=0;$i<count($data);$i++){
                        $deptid = $data[$i]['dept_id'];
                        $deptname = Db::name('dept')->where(['id'=>$deptid,'deletetime'=>null])->find();
                        if ($deptname){
                            $compname = Db::name('comp')->where(['id'=>$compid,'deletetime'=>null])->find();
                            $datas[$i]['deptname'] = $deptname['deptname'];
                            $datas[$i]['compname'] = $compname['compname'];
                        }


                    }

                (!empty($datas))? $this->success('操作成功', $datas) : $this->error('没有数据', []);

                    }

            }
    }


    /**
     * 获取设备读卡信息，可选，Android端
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/cardlog/getLog)
     * @ApiParams   (name="comp_id", type="int", required=true, description="机构id")
     * @ApiParams   (name="compsk", type="int", required=true, description="机构sk")
     * @ApiParams   (name="compvk", type="int", required=true, description="机构vk")
     * @ApiParams   (name="createtime", type="string", required=true, description="开始时间，格式：yyyy-mm-dd")
     * @ApiParams   (name="logdt", type="string", required=true, description="结束时间，格式：yyyy-mm-dd")
     * @ApiParams   (name="cardnum", type="string", required=false, description="卡号，可选")
     * @ApiParams   (name="cardname", type="string", required=false, description="车号，可选")
     * @ApiParams   (name="dev_id", type="string", required=false, description="设备id，可选")
     * @ApiParams   (name="devnum", type="string", required=false, description="设备号，可选")
     * @ApiParams   (name="dept_id", type="string", required=false, description="工区编号，可选")
     * @ApiParams   (name="page", type="string", required=true, description="页码， 可选")
     * @ApiParams   (name="rows", type="string", required=true, description="数据数量，可选")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    })
     */
    public function getLog()
    {
        if ($this->request->isPost()) {
            $params = $this->request->param();

            $validate = new \app\api\validate\Cardlog();
            $validate->scene('get')->batch()->check($params);
            $msg = $validate->getError();
            if (!$validate->scene('get')->batch()->check($params)){
                $this->error("参数错误",$msg);
            }else{
                $check = $this->check($params['compsk'],$params['compvk']);
                if ($check){

                    $stime = strtotime($params['createtime']);
                    $etime = strtotime($params['logdt']);
                    $where = [
                        'createtime' => array(array('EGT', $stime), array('ELT', $etime)),
                        'deletetime'=>null,
                        'comp_id' => $params['comp_id'],

                    ];
                    (!empty($params['dept_id']))?$where['dept_id']=$params['dept_id']:false;
                    (!empty($params['dev_id']))?$where['dev_id']=$params['dev_id']:false;
                    (!empty($params['cardnum']))?$where['cardnum']=['like','%'.$params['cardnum'].'%']:false;
                    (!empty($params['devnum']))?$where['devnum']=['like','%'.$params['devnum'].'%']:false;
                    (!empty($params['cardname']))?$where['cardname']=['like','%'.$params['cardname'].'%']:false;

                        if ((!empty($params['page']))){
                            (!empty($params['rows']))?$rows=$params['rows']:$rows=20;
                            $data = Db::table('logview')->where($where)->page($params['page'],$rows)->order('createtime desc')->select();
                        }else{
                            $data = Db::table('logview')->where($where)->order('createtime desc')->select();
                        }

                        $total = Db::table('logview')->where($where)->order('createtime desc')->count();

                        for ($i=0;$i<count($data);$i++){
                            $compname = Db::table('fa_comp')->where(['id'=>$data[$i]['comp_id'],'deletetime'=>null])->find();
                            $deptname = Db::table('fa_dept')->where(['id'=>$data[$i]['dept_id'],'deletetime'=>null])->find();
                            $devname = Db::table('fa_dev')->where(['id'=>$data[$i]['dev_id'],'deletetime'=>null])->find();
                            $card = Db::name('card')->where(['id'=>$data[$i]['card_id'],'deletetime'=>null])->find();
                            if ($deptname){
                                $data[$i]['compname'] = $compname['compname'];
                                $data[$i]['deptname'] = $deptname['deptname'];
                                $data[$i]['devname'] = $devname['devname'];
                                $data[$i]['cardname'] = $card['cardname'];
                                $data[$i]['createtime'] = date('Y-m-d H:i:s',$data[$i]['createtime']);
                                $data[$i]['deletetime'] = '-';
                            }else{
                                $data[$i]['compname'] = $compname['compname'];
                                $data[$i]['deptname'] = '-';
                                $data[$i]['devname'] = $devname['devname'];
                                $data[$i]['cardname'] = $card['cardname'];
                                $data[$i]['createtime'] = date('Y-m-d H:i:s',$data[$i]['createtime']);
                                $data[$i]['deletetime'] = '-';
                            }
                        }

                    for ($i=0;$i<count($data);$i++){
                        for ($j=0;$j<count($data[$i]);$j++){
                            $key = array_keys($data[$i]);
                            for ($k=0;$k<count($key);$k++){
                                if ($data[$i][$key[$k]] == null){
                                    $data[$i][$key[$k]] = '-';
                                }
                            }
                        }

                    }


                    $data?$this->result2('操作成功',$total,$data,1):$this->error('没有数据',[]);
                }else{
                    $this->error('该机构不存在',[]);
                }




            }

        }
    }

    /**
     * 返回封装后的 API 数据到客户端
     * @access protected
     * @param mixed  $msg    提示信息
     * @param mixed  $data   要返回的数据
     * @param int    $code   错误码，默认为0
     * @param string $type   输出类型，支持json/xml/jsonp
     * @param array  $header 发送的 Header 信息
     * @return void
     * @throws HttpResponseException
     */
    protected function result2($msg,$value1,$data = null, $code = 0, $type = null, array $header = [])
    {
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'total'  => $value1,
            'time' => Request::instance()->server('REQUEST_TIME'),
            'data' => $data,
        ];
        // 如果未设置类型则自动判断
        $type = $type ? $type : ($this->request->param(config('var_jsonp_handler')) ? 'jsonp' : $this->responseType);

        if (isset($header['statuscode'])) {
            $code = $header['statuscode'];
            unset($header['statuscode']);
        } else {
            //未设置状态码,根据code值判断
            $code = $code >= 1000 || $code < 200 ? 200 : $code;
        }
        $response = Response::create($result, $type, $code)->header($header);
        throw new HttpResponseException($response);
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

    public function test()
    {
        $data = Db::name('cardlog')->field('count(createtime) as count,createtime')->group('createtime')->select();
        $list = [];
        for ($i=0;$i<count($data);$i++){
           $list[date('Y-m-d',$data[$i]['createtime'])] = $data[$i]['count'];
        }
        $now = date('Y-m-d H:i:s',time());
        $params = strtotime('+1years',strtotime($now));
        $pass = md5(md5('123456').'m9AF1W');
        $data = Db::name('cardlog')->paginate(1);
        $a = '1323132123.4545';
        $aa = explode('.',$a);
        $b = 13/6;
        $c = ceil($b);
       return $c;
    }
    //处理上传图片
    public function upload($pic){

        $floder =  date('m',time());
        // 移动到框架应用根目录/public/file/ 目录下
        $info = $pic->move(ROOT_PATH . 'public' . DS . 'file' . DS .'images' . DS . $floder);
        if($info){
            $path = "file/images/".$floder.'/'.$info->getSaveName();
            $pic_path = str_replace('\\','/',$path);

            return $pic_path;
        }else{
            // 上传失败获取错误信息
            echo $pic->getError();
        }

    }

    /**
     * 按年获取统计数据，返回数据按12个月份区分  ----------Android用
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/cardlog/byYear)
     * @ApiParams   (name="comp_id", type="int", required=true, description="机构id")
     *  @ApiParams   (name="dept_id", type="int", required=true, description="工区id")
     *  @ApiParams   (name="dev_id", type="int", required=true, description="设备id")
     * @ApiParams   (name="year", type="int", required=true, description="年份")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    })
     */
    public function byYear()
    {
        if ($this->request->isPost()){
            $params = $this->request->param();
            $validate = new \app\api\validate\Cardlog();
            $validate->scene('year')->batch()->check($params);
            //绑定表
            $logTable = Db::name('cardlog');
            $cardTable = Db::name('card');
            //绑定机构、工区编号
            $compid = $params['comp_id'];
            $deptid = $params['dept_id'];
            $devid  = $params['dev_id'];
            $dev = Db::name('dev')->where(['id'=>$devid,'deletetime'=>null])->find();
            if ($validate->scene('year')->batch()->check($params)){
                //获得用户输入月份的天数
                $month = 12;

                //条件
                $where = [
                    'comp_id' => $compid,
                    'dept_id' => $deptid,
                    'dev_id'  => $devid
                ];
                $max = 0;
                $min = 0;
                $data = [];
                for ($i=1;$i<=$month;$i++){
                    $days = cal_days_in_month(CAL_GREGORIAN,$i,$params['year']);
                    $start = '-01 00:00:00';
                    $end = '-'.$days.' 23:59:59';
                    $date = $params['year'];
                    //获取该机构所有车卡
                    $card = $cardTable->where(['dept_id'=>$deptid])->select();
                    //时间限制1号00:00:00到23:59:59
                    $where['createtime'] = array(array('EGT', strtotime($date.'-'.$i.$start)), array('ELT', strtotime($date.'-'.$i.$end)));

//                    for ($j=0;$j<count($card);$j++){
                        //查询刷卡次数
                        $count = $logTable->where($where)->count();
                        if ($i==1){
                            $min = $count;
                        }
                        if ($count>$max){
                            $max = $count;
                        }
                        if ($i>1){
                            if ($min>$count){
                                $min = $count;
                            }
                        }
                        //向数组添加数据.
                    if (empty($count)){
                        array_push($data,[
                            'devname'  => $dev['devnum'],
                            'devnum' => $dev['devnum'],
                            'card_count' => 0
                        ]);
                    }else{
                        array_push($data,[
                            'devname'  => $dev['devnum'],
                            'devnum' => $dev['devnum'],
                            'card_count' => $count
                        ]);
                    }

                }

                $data?$this->yes('操作成功',$max,$min,$data):$this->error('没有数据',[]);
            }else{
                $msg = $validate->getError();
                $this->error('参数错误',$msg);
            }

        }


    }

    /**
     * 按月获取统计数据，返回数据一每个月的天数区分  ----------Android用
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/cardlog/byMonth)
     * @ApiParams   (name="comp_id", type="int", required=true, description="机构id")
     * @ApiParams   (name="dept_id", type="int", required=true, description="工区id")
     * @ApiParams   (name="dev_id", type="int", required=true, description="设备id")
     * @ApiParams   (name="stime", type="string", required=true, description="开始时间，格式：yyyy-mm-dd H:i:s")
     * @ApiParams   (name="etime", type="string", required=true, description="结束时间，格式：yyyy-mm-dd H:i:s")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    })
     */
    public function byMonth()
    {
        if ($this->request->isPost()){
            $params = $this->request->param();
            $validate = new \app\api\validate\Cardlog();
            $validate->scene('byMonth')->batch()->check($params);
            //绑定表
            $logTable = Db::name('cardlog');
            $cardTable = Db::name('card');
            //绑定机构、工区编号
            $compid = $params['comp_id'];
            $deptid = $params['dept_id'];
            $devid  = $params['dev_id'];
            $stime = strtotime($params['stime']);
            $etime = strtotime($params['etime']);
            $flag = $etime-$stime;
            //时间段为60天
            $differ = 5184000;
            if ($validate->scene('byMonth')->batch()->check($params)){
                //获得用户输入月份的天数
                $start = ' 00:00:00';
                $end = ' 23:59:59';
//                $days = cal_days_in_month(CAL_GREGORIAN,$params['month'],$params['year']);
                $days = $flag/86400;
                //条件
                $where = [
                    'comp_id' => $compid,
                    'dept_id' => $deptid,
                    'dev_id'  => $devid
                ];
                $max = 0;
                $min = 0;
                $data = [];
                $dev = Db::name('dev')->where(['id'=>$devid,'deletetime'=>null])->find();
                if ($flag > $differ){
                    $this->error('查询失败，时间区间不能超过60天',[]);
                }else{
                    for ($i=0;$i<$days;$i++){
                        $where['createtime'] = array(array('EGT', ($stime+86400*$i)), array('ELT', ($stime+86400*($i+1))));
                        //查询刷卡次数
                        $count = $logTable->where($where)->count();
                        if ($i==0){
                            $min = $count;
                        }

                        if ($count>$max){
                            $max = $count;
                        }
                        if ($i>1){
                            if ($min>$count){
                                $min = $count;
                            }
                        }
                        //向数组添加数据
                        if (empty($count)){
                            array_push($data,[
                                'devname'  => $dev['devnum'],
                                'devnum' => $dev['devnum'],
                                'card_count' => 0
                            ]);
                        }else{
                            array_push($data,[
                                'devname'  => $dev['devnum'],
                                'devnum' => $dev['devnum'],
                                'card_count' => $count
                            ]);
                        }


                    }

                    $data?$this->yes('操作成功',$max,$min,$data):$this->error('没有数据',[]);
                }

            }else{
                $msg = $validate->getError();
                $this->error('参数错误',$msg);
            }

        }

    }

    protected function yes($msg = '',$value1,$value2, $data = null, $code = 1, $type = null, array $header = [])
    {
        $this->result1($msg,$value1,$value2,$data, $code, $type, $header);
    }

    /**
     * 返回封装后的 API 数据到客户端
     * @access protected
     * @param mixed  $msg    提示信息
     * @param mixed  $data   要返回的数据
     * @param int    $code   错误码，默认为0
     * @param string $type   输出类型，支持json/xml/jsonp
     * @param array  $header 发送的 Header 信息
     * @return void
     * @throws HttpResponseException
     */
    protected function result1($msg,$value1,$value2,$data = null, $code = 0, $type = null, array $header = [])
    {
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'max'  => $value1,
            'min'  => $value2,
            'time' => Request::instance()->server('REQUEST_TIME'),
            'data' => $data,
        ];
        // 如果未设置类型则自动判断
        $type = $type ? $type : ($this->request->param(config('var_jsonp_handler')) ? 'jsonp' : $this->responseType);

        if (isset($header['statuscode'])) {
            $code = $header['statuscode'];
            unset($header['statuscode']);
        } else {
            //未设置状态码,根据code值判断
            $code = $code >= 1000 || $code < 200 ? 200 : $code;
        }
        $response = Response::create($result, $type, $code)->header($header);
        throw new HttpResponseException($response);
    }


    /**
     * 按年获取统计数据，返回数据按12个月份区分  --------小程序用
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/cardlog/year)
     * @ApiParams   (name="comp_id", type="int", required=true, description="机构id")
     *  @ApiParams   (name="dept_id", type="int", required=true, description="工区id")
     *  @ApiParams   (name="dev_id", type="int", required=true, description="设备id")
     * @ApiParams   (name="year", type="int", required=true, description="年份")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    })
     */
    public function year()
    {
        if ($this->request->isPost()){
            $params = $this->request->param();
            $validate = new \app\api\validate\Cardlog();
            $validate->scene('year')->batch()->check($params);
            //绑定表
            $logTable = Db::name('cardlog');
            $cardTable = Db::name('card');
            //绑定机构、工区编号
            $compid = $params['comp_id'];
            $deptid = $params['dept_id'];
            $devid  = $params['dev_id'];
            if ($validate->scene('year')->batch()->check($params)){
                //获得用户输入月份的天数
                $month = 12;

                //条件
                $where = [
                    'comp_id' => $compid,
                    'dept_id' => $deptid,
                    'dev_id'  => $devid
                ];
                $max = 0;
                $min = 0;
                $data = [];
                for ($i=1;$i<=$month;$i++){
                    $days = cal_days_in_month(CAL_GREGORIAN,$i,$params['year']);
                    $start = '-01 00:00:00';
                    $end = '-'.$days.' 23:59:59';
                    $date = $params['year'];
                    //获取该机构所有车卡
                    $card = $cardTable->where(['dept_id'=>$deptid,'deletetime'=>null])->select();
                    //时间限制1号00:00:00到23:59:59
                    $where['createtime'] = array(array('EGT', strtotime($date.'-'.$i.$start)), array('ELT', strtotime($date.'-'.$i.$end)));

                    for ($j=0;$j<count($card);$j++){
                        //绑定卡的名称
                        $cardname = $card[$j]['cardname'];
                        //查询刷卡次数
                        $where['card_id'] = $card[$j]['id'];   //加入卡号条件
                        $count = $logTable->where($where)->count();
                        if ($j==0){
                            $min = $count;
                        }

                        if ($count>$max){
                            $max = $count;
                        }
                        if ($j>0){
                            if ($min<$count){
                                $min = $count;
                            }
                        }
                        //向数组添加数据
                        $data[$i][$j] = [
                            'cardname'  => $cardname,
                            'cardnum' => $card[$j]['cardnum'],
                            'card_count' => $count
                        ];
                        if (empty($count)){
                            $data[$i][$j] = [
                                'cardname'  => $cardname,
                                'cardnum' => $card[$j]['cardnum'],
                                'card_count' => 0
                            ];
                        }
                    }

                }

                $data?$this->yes('操作成功',$max,$min,$data):$this->error('没有数据',[]);
            }else{
                $msg = $validate->getError();
                $this->error('参数错误',$msg);
            }

        }


    }

    /**
     * 按月获取统计数据，返回数据一每个月的天数区分 --------小程序用
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/cardlog/year)
     * @ApiParams   (name="comp_id", type="int", required=true, description="机构id")
     * @ApiParams   (name="dept_id", type="int", required=true, description="工区id")
     * @ApiParams   (name="dev_id", type="int", required=true, description="设备id")
     * @ApiParams   (name="year", type="int", required=true, description="年份")
     * @ApiParams   (name="month", type="int", required=true, description="月份")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    })
     */
    public function month()
    {
        if ($this->request->isPost()){
            $params = $this->request->param();
            $validate = new \app\api\validate\Cardlog();
            $validate->scene('month')->batch()->check($params);
            //绑定表
            $logTable = Db::name('cardlog');
            $cardTable = Db::name('card');
            //绑定机构、工区编号
            $compid = $params['comp_id'];
            $deptid = $params['dept_id'];
            $devid  = $params['dev_id'];
            if ($validate->scene('month')->batch()->check($params)){
                //获得用户输入月份的天数
                $start = ' 00:00:00';
                $end = ' 23:59:59';
                $date = $params['year'].'-'.$params['month'];
                $days = cal_days_in_month(CAL_GREGORIAN,$params['month'],$params['year']);

                //条件
                $where = [
                    'comp_id' => $compid,
                    'dept_id' => $deptid,
                    'dev_id'  => $devid
                ];
                $max = 0;
                $min = 0;
                $data = [];
                for ($i=1;$i<=$days;$i++){
                    //获取该机构所有车卡
                    $card = $cardTable->where(['dept_id'=>$deptid,'deletetime'=>null])->select();

                    $where['createtime'] = array(array('EGT', strtotime($date.'-'.$i.$start)), array('ELT', strtotime($date.'-'.$i.$end)));

                    for ($j=0;$j<count($card);$j++){
                        //绑定卡的名称
                        $cardname = $card[$j]['cardname'];
                        //查询刷卡次数
                        $where['card_id'] = $card[$j]['id'];   //加入卡号条件
                        $count = $logTable->where($where)->count();
                        if ($j==0){
                            $min = $count;
                        }

                        if ($count>$max){
                            $max = $count;
                        }
                        if ($j>0){
                            if ($min<$count){
                                $min = $count;
                            }
                        }
                        //向数组添加数据
                        $data[$i][$j] = [
                            'cardname'  => $cardname,
                            'cardnum' => $card[$j]['cardnum'],
                            'card_count' => $count
                        ];
                        if (empty($count)){
                            $data[$i][$j] = [
                                'cardname'  => $cardname,
                                'cardnum' => $card[$j]['cardnum'],
                                'card_count' => 0
                            ];
                        }
                    }
                }
                $data?$this->yes('操作成功',$max,$min,$data):$this->error('没有数据',[]);
            }else{
                $msg = $validate->getError();
                $this->error('参数错误',$msg);
            }

        }

    }



    /**
     * 按月获取统计数据，返回数据一每个月的天数区分 --------小程序用
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/cardlog/day)
     * @ApiParams   (name="comp_id", type="int", required=true, description="机构id")
     * @ApiParams   (name="dept_id", type="int", required=true, description="工区id")
     * @ApiParams   (name="dev_id", type="int", required=true, description="设备id")
     * @ApiParams   (name="year", type="int", required=true, description="年份")
     * @ApiParams   (name="month", type="int", required=true, description="月份")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    })
     */
    public function day()
    {
        if ($this->request->isPost()){
            $params = $this->request->param();
            $validate = new \app\api\validate\Cardlog();
            $validate->scene('month')->batch()->check($params);
            //绑定表
            $logTable = Db::name('cardlog');
            $devTable = Db::name('dev');
            //绑定机构、工区编号
            $compid = $params['comp_id'];
            $deptid = $params['dept_id'];
            $devid  = $params['dev_id'];
            if ($validate->scene('month')->batch()->check($params)){
                //获得用户输入月份的天数
                $start = ' 00:00:00';
                $end = ' 23:59:59';
                $date = $params['year'].'-'.$params['month'];
                $days = cal_days_in_month(CAL_GREGORIAN,$params['month'],$params['year']);

                //条件
                $where = [
                    'comp_id' => $compid,
                    'dept_id' => $deptid,
                    'dev_id'  => $devid
                ];
                $max = 0;
                $min = 0;
                $data = [];
                for ($i=1;$i<=$days;$i++){
                    //获取该机构所有车卡
                    $dev = $devTable->where(['id'=>$devid,'deletetime'=>null])->find();

                    $where['createtime'] = array(array('EGT', strtotime($date.'-'.$i.$start)), array('ELT', strtotime($date.'-'.$i.$end)));

                        $count = $logTable->where($where)->count();
                        if ($i==0){
                            $min = $count;
                        }

                        if ($count>$max){
                            $max = $count;
                        }
                        if ($i>0){
                            if ($min>$count){
                                $min = $count;
                            }
                        }
                        //向数组添加数据
                        if (empty($count)){
                            array_push($data,[
                                'devname'  => $dev['devnum'],
                                'devnum' => $dev['devnum'],
                                'card_count' => 0
                            ]);
                        }else{
                            array_push($data,[
                                'devname'  => $dev['devnum'],
                                'devnum' => $dev['devnum'],
                                'card_count' => $count
                            ]);
                        }

                }
                $data?$this->yes('操作成功',$max,$min,$data):$this->error('没有数据',[]);
            }else{
                $msg = $validate->getError();
                $this->error('参数错误',$msg);
            }

        }

    }

    /**
     * 查询区间内某天某张卡的刷卡次数
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/cardlog/byCardnum)
     * @ApiParams   (name="comp_id", type="int", required=true, description="机构id")
     * @ApiParams   (name="dept_id", type="int", required=false, description="工区id----可选")
     * @ApiParams   (name="dev_id", type="int", required=false, description="设备id-----可选")
     * @ApiParams   (name="cardnum", type="int", required=false, description="卡号-------可选")
     *  @ApiParams   (name="cardname", type="int", required=false, description="车号-------可选")
     * @ApiParams   (name="stime", type="int", required=true, description="开始时间-----格式 yyyy-mm-dd h:i:s")
     * @ApiParams   (name="etime", type="int", required=true, description="结束时间-----格式 yyyy-mm-dd h:i:s")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    })
     */
    public function byCardnum()
    {
        if ($this->request->isPost()){
            $params = $this->request->param();
            $validate = new \app\api\validate\Cardlog();
            $validate->scene('cardnum')->batch()->check($params);

            if ($validate->scene('cardnum')->batch()->check($params)){

                //绑定表
                $logTable = Db::name('cardlog');
                //绑定固定信息
                $compid = $params['comp_id'];
                $stime = strtotime($params['stime']);
                $etime = strtotime($params['etime']);
                $flag = $etime-$stime;
                //时间段为60天，86400为一天
                $differ = 5184000;
                $days = $flag/86400;
                //条件
                $where = [
                    'comp_id'  => $compid,
                    'deletetime'=>null,
                ];
                $list = [];
                if ($flag > $differ){
                    $this->error('查询失败，时间区间不能超过60天',[]);
                }else{
//                    for ($i=0;$i<$days;$i++){
                        $where['createtime'] = array(array('EGT', ($stime)), array('ELT', ($etime)));
                        (!empty($params['dept_id']))?$where['dept_id']=$params['dept_id']:false;
                        (!empty($params['dev_id']))?$where['dev_id']=$params['dev_id']:false;
                        (!empty($params['cardnum']))?$where['cardnum']=$params['cardnum']:false;
                        (!empty($params['cardname'])) ? $where['cardname'] = ['like','%'.$params['cardname'].'%'] : false;
//                        if(!empty($params['cardname'])){
//                            $cardname = Db::name('card')->where(['cardname'=>$params['cardname']])->find();
//                            $where['card_id'] = $cardname['id'];
//                        }
                        //查询刷卡次数
                        $data = Db::table('logview')->where($where)->field('card_id,cardnum,count(cardnum) as count')
                                                                        ->group('cardnum')->select();
                        if ($data){
                            for ($j = 0; $j < count($data);$j++) {
                                $card = Db::name('card')->where(['id'=>$data[$j]['card_id']])->find();
                                if (!empty($card['cardname'])){
                                    $data[$j]['cardname'] = $card['cardname'];
                                }

                        }


                    }

                    for ($i=0;$i<count($data);$i++){
                        for ($j=0;$j<count($data[$i]);$j++){
                            $key = array_keys($data[$i]);
                            for ($k=0;$k<count($key);$k++){
                                if ($data[$i][$key[$k]] == null){
                                    $data[$i][$key[$k]] = '-1';
                                }
                            }
                        }

                    }
//                    if (!empty($params['cardname'])){
//
//                        for ($i=0;$i<count($data);$i++){
//                            if (strpos($data[$i]['cardname'],$params['cardname']) !== false){
//                                array_push($list,$data[$i]);
//                            }
//                        }
//                        $list?$this->success('操作成功',$list):$this->error('没有数据',[]);
//                    }else{
                        $data?$this->success('操作成功',$data):$this->error('没有数据',[]);
//                    }




                }

            }else{
                $msg = $validate->getError();
                $this->error('参数错误',$msg);
            }

        }

    }



    /**
     * 按开始时间，所给工区，分天返回所给工区的读卡次数总和
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/cardlog/byDay)
     * @ApiParams   (name="comp_id", type="int", required=true, description="机构id")
     * @ApiParams   (name="dept_id", type="int", required=false, description="工区id----可选")
     * @ApiParams   (name="dev_id", type="int", required=false, description="设备id-----可选")
     * @ApiParams   (name="cardnum", type="int", required=false, description="卡号-------可选")
     *  @ApiParams   (name="cardname", type="int", required=false, description="车号-------可选")
     * @ApiParams   (name="stime", type="int", required=true, description="开始时间-----格式 yyyy-mm-dd")
     * @ApiParams   (name="etime", type="int", required=true, description="结束时间-----格式 yyyy-mm-dd")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    })
     */
    public function byDay()
    {

        if ($this->request->isPost()){
            $params = $this->request->param();
            $validate = new \app\api\validate\Cardlog();
            $validate->scene('byDay')->batch()->check($params);
            if ($validate->scene('byDay')->batch()->check($params)){
                //绑定表
                $logTable = Db::name('cardlog');
                //绑定固定信息
                $compid = $params['comp_id'];
                $stime = strtotime($params['stime']);
                $etime = strtotime($params['etime']);
                $flag = $etime-$stime;
                //时间段为60天
                $differ = 5184000;
                $days = $flag/86400;
                $total = 0;
                //条件
                $where = [
                    'comp_id'  => $compid,
                    'deletetime'=>null,
                ];
                $data = [];
                if ($flag > $differ){
                    $this->error('查询失败，时间区间不能超过60天',[]);
                }else {
                    $list = [];
//                    for ($i = 0; $i < $days; $i++) {
                        $where['createtime'] = array(array('EGT', ($stime)), array('ELT', ($etime)));
                        (!empty($params['dept_id'])) ? $where['dept_id'] = $params['dept_id'] : false;
                        (!empty($params['dev_id'])) ? $where['dev_id'] = $params['dev_id'] : false;
                        (!empty($params['cardnum'])) ? $where['cardnum'] = $params['cardnum'] : false;
                        (!empty($params['cardname'])) ? $where['cardname'] = ['like','%'.$params['cardname'].'%'] : false;
//                        if(!empty($params['cardname'])){
//                           $cardname = Db::name('card')->where(['cardname'=>$params['cardname']])->find();
//                           $where['card_id'] = $cardname['id'];
//                        }
                        //查询刷卡次数
//                        $data = $logTable->where($where)->field('logdt,count(logdt) as count')->group('logdt')->select();
                    $data = Db::table('logview')->field('logdt,count(logdt) as count')->where($where)->group('logdt')->select();
                        if (!$data == 0){
                            for ($j = 0; $j < count($data); $j++) {
                                $data[$j]['logdt'] = date('Ymd', strtotime($data[$j]['logdt']));
//                                array_push($list,$data[$j]);
                                $total += $data[$j]['count'];
                            }
                        }

                    $data?$this->success('操作成功', $data) : $this->error('没有数据', []);
                }

            }else{
                $msg = $validate->getError();
                $this->error('参数错误',$msg);
            }

        }
    }


}
