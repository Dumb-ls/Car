<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\Config;
use think\Db;

/**
 * 控制台
 *
 * @icon fa fa-dashboard
 * @remark 用于展示当前系统中的统计数据、统计报表及重要实时数据
 */
class Dashboard extends Backend
{

    /**
     * 查看
     */
    public function index()
    {
        //dump(input('session.'));  //---> sessiong.json
        //dump(config());  //---> config.json
        //die();
//        $seventtime = \fast\Date::unixtime('day', -7);
//        $paylist = $createlist = [];
//        for ($i = 0; $i < 7; $i++)
//        {
//            $day = date("Y-m-d", $seventtime + ($i * 86400));
//            $createlist[$day] = mt_rand(20, 200);
//            $paylist[$day] = mt_rand(1, mt_rand(1, $createlist[$day]));
//        }
        $hooks = config('addons.hooks');
        $uploadmode = isset($hooks['upload_config_init']) && $hooks['upload_config_init'] ? implode(',', $hooks['upload_config_init']) : 'local';
        $addonComposerCfg = ROOT_PATH . '/vendor/karsonzhang/fastadmin-addons/composer.json';
        Config::parse($addonComposerCfg, "json", "composer");
        $config = Config::get("composer");
        $addonVersion = isset($config['version']) ? $config['version'] : __('Unknown');

//        dump($paylist);
//        echo '<br/>';
//        echo '----------------';
//        dump($createlist);
//        die();
        //获取数据
        $compid = input('session.')['admin']['comp_id'];
        $where = [];
        ($compid>0)?$where['comp_id']=$compid:false;
        $dept = Db::name('dept')->where($where)->count();
        $card = Db::name('card')->where($where)->count();
        $dev = Db::name('dev')->where($where)->count();
        $cardlog = Db::name('cardlog')->where($where)->count();
        //今日信息
        $todayCardlog = Db::name('cardlog')->where($where)->whereTime('createtime','d')->count();        //今日读卡数
        $todayCard = Db::name('card')->where($where)->whereTime('createtime','d')->count();        //今日发卡数
        //本周信息
        $weekCardlog = Db::name('cardlog')->where($where)->whereTime('createtime','w')->count();        //本周读卡数
        $weekCard = Db::name('card')->where($where)->whereTime('createtime','w')->count();        //本周发卡数
        //本月信息
        $monthCardlog = Db::name('cardlog')->where($where)->whereTime('createtime','m')->count();        //本月读卡数
        $monthCard = Db::name('card')->where($where)->whereTime('createtime','m')->count();        //本月发卡数
        //图片数量
        $photo = Db::name('cardlog')->where($where)->where('carpthoto','not null')->count();

        //统计图读卡数
        $logCount = Db::name('cardlog')->field('count(logdt) as count,logdt')->where($where)->group('logdt')->select();
        $logList = [];
        for ($i=0;$i<count($logCount);$i++){
            $logList[$logCount[$i]['logdt']] = $logCount[$i]['count'];
        }
        //统计图发卡数
        $cardCount = Db::name('card')->field('count(createtime) as count,createtime')->where($where)->group('createtime')->select();
        $cardList = [];
        for ($i=0;$i<count($logCount);$i++){
            if ($i >= count($logList)){
//                if (null == $cardCount[$i]['count']){
                    $cardList[$logCount[$i]['logdt']] = 0;
//                }
            }elseif ($i < count($logList)){
                if (null == $cardCount[$i]['count']){
                    $cardList[$logCount[$i]['logdt']] = 0;

                }else{
                    $cardList[$logCount[$i]['logdt']] = $cardCount[$i]['count'];
                }
            }


        }

//        dump($cardList);
//        dump($logList);
//        die();
        $this->view->assign([
            'dept'        => $dept,
            'dev'         => $dev,
            'card'        => $card,
            'cardlog'     => $cardlog,
            'dayCard'     => $todayCard,
            'weekCard'    => $weekCard,
            'monthCard'   => $monthCard,
            'dayCardlog'  => $todayCardlog,
            'weekCardlog' => $weekCardlog,
            'monthCardlog'=> $monthCardlog,
            'photo'       => $photo,
            'paylist'     => $logList,
            'createlist'  => $cardList,
            'addonversion'=> $addonVersion,
            'uploadmode'  => $uploadmode
        ]);

        return $this->view->fetch();
    }

}
