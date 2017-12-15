<?php
namespace app\www\controller;

use think\Request;
use think\Db;
use app\core\model\RegionModel;
class Agent extends Base
{
    /**
     * 获取城市
     * @author dongpeng <346685786@qq.com>
     */
    public function city(){
        //获取提交信息
        $parentid = request()->param('parent_id');
        //实例化分类模型类
        $RegionModel = new RegionModel;
        $city = $RegionModel->getCity($parentid);
        $html = '';
        if (empty($city[0]['city'])){
            $html = $this->district();
        }else{
            foreach($city as $v){
                $html.="<option value=".$v['id'].">".$v['city']."</option>";
            }
        }
        return $html;
    }

    /**
     * 获取县/区
     * @author dongpeng <346685786@qq.com>
     */
    public function district(){
        //获取提交信息
        $parentid = request()->param('parent_id');
        //实例化分类模型类
        $RegionModel = new RegionModel;
        $district = $RegionModel->getDistrict($parentid);
        $html = '';
        foreach($district as $v){
            $html.="<option value=".$v['id'].">".$v['district']."</option>";
        }
        return $html;
    }

}
