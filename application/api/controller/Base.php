<?php


namespace app\api\controller;


use app\common\controller\Api;
use think\Db;

class Base extends Api
{
    protected $noNeedLogin = ['*'];

    /**
     * 查询
     */
    public function query($name){
        $data = Db::name('config')->where(['name'=>$name])->column(['value']);
        return $data[0];
    }
    /**
     * 网站管理端开关状态
     */
    public function stat_web()
    {
        $status = $this->query('stat_web');
        return $status;
    }

    /**
     * 是否允许客户机构通过网站登录
     */
    public function stat_weblogin()
    {
        $status = $this->query('stat_weblogin');
        return $status;
    }

    /**
     * 是否允许小程序接入数据
     */
    public function stat_mp()
    {
        $status = $this->query('stat_mp');
        return $status;
    }

    /**
     * 服务逾期后小程序开关状态
     */
    public function stat_mpfee()
    {
        $status = $this->query('stat_mpfee');
        return $status;
    }

    /**
     * 是否允许下端设备接入
     */
    public function dev_log()
    {
        $status = $this->query('dev_log');
        return $status;
    }

    /**
     * 是否允许下端逾期设备接入
     */
    public function dev_logfee()
    {
        $status = $this->query('dev_logfee');
        return $status;
    }

    /**
     * 允许查询逾期设备数据
     */
    public function dev_infofee()
    {
        $status = $this->query('dev_infofee');
        return $status;
    }


    /**
     * 允许未登记设备接入
     */
    public function dev_logunreg()
    {
        $status = $this->query('dev_logunreg');
        return $status;
    }

}