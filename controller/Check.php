<?php
namespace app\www\controller;

use think\Db;

/**
 * Class Check
 * @package app\admin\controller
 * 验证类
 */
class Check extends Base{
    /**
     * ajax验证企业名称是否重复
     */
    public function checkcompany(){

        $name = request()->param('name');

        $res = Db::name('company')->where('name',$name)->count();

        echo json_encode($res);
    }

    /**
     * ajax验证展会标题是否重复
     */
    public function checktitle(){

        $name = request()->param('name');

        $res = Db::name('exhibition')->where('title',$name)->count();
        echo json_encode($res);
    }
}