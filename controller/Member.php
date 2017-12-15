<?php
namespace app\www\controller;

use app\core\model\CompanyModel;
use app\core\model\ExhibitorModel;
use app\core\model\MemberModel;
use app\core\model\ServiceModel;
use app\core\model\SponsorModel;
use app\core\model\SystemModel;
use app\core\model\ArticleModel;
use think\Cookie;
use think\Db;
use think\Request;
use app\core\model\ExhibitionModel;
use app\core\model\RegionModel;
use app\core\model\ShardModel;
use app\core\model\CategoryModel;

/**会员中心控制器
 * Class Member
 * @package app\www\controller
 */
class Member extends Auth {

    public function __construct()
    {
        parent::__construct();
        $config = include "../application/admin/extra/exhibitor.info.conf.php";
        $this->cycle = $config['exhibition']['exhibition_cycle'];
        //获取配置文件里的企业性质
        $this->nature = $config['exhibitor']['nature'];
        //获取配置文件里的企业规模
        $this->scope = $config['exhibitor']['scope'];
        //实例化展会模型
        $this->ExhibitionModel = new ExhibitionModel;
        //实例化地区模型
        $this->RegionModel = new RegionModel;
        //实例化推荐位模型
        $this->ShardModel = new ShardModel;
        //实例化分类模型
        $this->CategoryModel = new CategoryModel;
        //实例化主办方模型
        $this->SponsorModel = new SponsorModel;
        //实例化资讯模型
        $this->ArticleModel = new ArticleModel;
        //实例化服务商模型
        $this->ServiceModel = new ServiceModel;
        //实例化参展商型
        $this->ExhibitorModel = new ExhibitorModel;
        //实例化主办方模型
        $this->CompanyModel = new CompanyModel;
        //实例化会员模型
        $this->MemberModel = new MemberModel;
        //传输当前时间
        $this->assign('time',time());
        $this->assign('nature',$this->nature);
        $this->assign('scope',$this->scope);
        $this->assign('cycle', $this->cycle);
        
    }

    /**
     * 会员中心首页
     */
    public function index(){
        //获取用户登录信息
        $userId = Cookie::get("userinfo")['id'];
        $userinfo = $this->MemberModel->getMemberInfo($userId);
        $role_info = $this->CompanyModel->getCompany($userinfo['fk_role_id'],$userinfo['role']);
        $this->assign('role_info',$role_info);
        //获取相关展会
        $exhreleted = $this->ExhibitionModel->getExhByParam(null,5);
        $this->assign('exhreleted',$exhreleted);

        if ($userinfo['role'] == 4){
            //观展者
            return $this->fetch('visitor_index');
        }else{
            if ($userinfo['role'] == 1){
                //主办方
                $sponsor = $this->SponsorModel->getSponsor($userinfo['fk_role_id']);
                //查询近期发布的两个展会
                $exh = $this->ExhibitionModel->getExhByParam($userinfo['fk_role_id'],2);
                //获取行业
                if (!empty($exh)){
                    foreach ($exh as $key=>$value){
                        //获取该展会所选择的行业
                        $tradeid = $this->ExhibitionModel->getTradeRelation($value['id']);
                        if (!empty($tradeid)){
                            //如果第一个为一级行业，则取该值
                            $industry = $this->CategoryModel->getTradeCategoryOne($tradeid[0]['fk_industry_id']);
                            if ($industry['parent_id'] != 0){
                                //否则获取该子行业的一级行业
                                $industry = $this->CategoryModel->getTradeCategoryOne($industry['parent_id']);
                            }
                            $exh[$key]['industry'] = $industry['name'];
                        }else{
                            $exh[$key]['industry'] = '';
                        }
                    }
                }
                $this->assign('role',$sponsor);
                $this->assign('exh',$exh);
            }elseif($userinfo['role'] == 2){
                //参展商
                $exhibitor = $this->ExhibitorModel->getExhibitor($userinfo['fk_role_id']);
                //查询近期发布的两个展会
                $exh = $this->ExhibitionModel->getExhByParam($userinfo['fk_role_id'],2);
                //获取行业
                if (!empty($exh)){
                    foreach ($exh as $key=>$value){
                        //获取该展会所选择的行业
                        $tradeid = $this->ExhibitionModel->getTradeRelation($value['id']);
                        if (!empty($tradeid)){
                            //如果第一个为一级行业，则取该值
                            $industry = $this->CategoryModel->getTradeCategoryOne($tradeid[0]['fk_industry_id']);
                            if ($industry['parent_id'] != 0){
                                //否则获取该子行业的一级行业
                                $industry = $this->CategoryModel->getTradeCategoryOne($industry['parent_id']);
                            }
                            $exh[$key]['industry'] = $industry['name'];
                        }else{
                            $exh[$key]['industry'] = '';
                        }
                    }
                }
                // dump($exhibitor);die;
                $this->assign('role',$exhibitor);
                $this->assign('exh',$exh);
            }elseif($userinfo['role'] == 3){
                //服务商
                $sponsor = $this->ServiceModel->getService($userinfo['fk_role_id']);
                $this->assign('role',$sponsor);
            }
        }

        return $this->fetch();
    }

    
    /**
     * 企业资料管理
     */
    public function company_info(){
        //获取用户登录信息
        $userId = Cookie::get("userinfo")['id'];
        $userinfo = $this->MemberModel->getMemberInfo($userId);
        // 获取用户企业审核状态
        switch ($userinfo['role']) {
            case 1:
                $roleStatus = $this->SponsorModel->getSponsor($userinfo['fk_role_id'])['status'];
                break;
            case 2:
                $roleStatus = $this->ExhibitorModel->getExhibitor($userinfo['fk_role_id'])['status'];
                break;
            case 3:
                $roleStatus = $this->ServiceModel->getService($userinfo['fk_role_id'])['status'];
                break;
        }
        $this->assign('role_status',$roleStatus);

        //获取企业性质、规模
        $scope = $this->scope;
        $nature = $this->nature;
        if ($userinfo['role'] == 1){
            //获取主办方信息
            //获取该主办方的角色及资质数据
            $sponsor['role'] = $this->SponsorModel->getSponsor($userinfo['fk_role_id']);
            // dump($userinfo);die;
            if (!empty($sponsor['role'])){
                //获取干主办方的资料信息
                $sponsor['data'] = $this->CompanyModel->getCompany($userinfo['fk_role_id'],1);
                $sponsor['data']['scope'] = $scope[$sponsor['data']['scope']];
                $sponsor['data']['nature'] = $nature[$sponsor['data']['nature']];
                //获取其它资质图片数据
                $sponsor['attachment'] = $this->SponsorModel->getAttachment($sponsor['data']['id']);
            }
            $this->assign('company',$sponsor);
        }elseif($userinfo['role'] == 2){
            //获取该展商的角色及资质数据
            $exhibitor['role'] = $this->ExhibitorModel->getExhibitor($userinfo['fk_role_id']);
            //获取干展商的资料信息
            if (!empty($exhibitor['role'])){
                $exhibitor['data'] = $this->CompanyModel->getCompany($userinfo['fk_role_id'],2);
                $exhibitor['data']['scope'] = $scope[$exhibitor['data']['scope']];
                $exhibitor['data']['nature'] = $nature[$exhibitor['data']['nature']];
                //获取其它资质图片数据
                $exhibitor['attachment'] = $this->ExhibitorModel->getAttachment($exhibitor['data']['id']);
                //获取行业关系数据
                $traderelation = $this->ExhibitorModel->getTradeRelation($exhibitor['role']['id']);
                $industry = '';
                if (!empty($traderelation)){
                    //获取行业名称
                    foreach ($traderelation  as $value){
                        $trade = $this->CategoryModel->getTradeCategoryOne($value['fk_industry_id']);
                        $industry .= "&nbsp;&nbsp;".$trade['name'];
                    }
                }
                $exhibitor['data']['category'] = $industry;

            }
            $this->assign('company',$exhibitor);
        }elseif($userinfo['role'] == 3){
            //获取该服务商的角色及资质数据
            $service['role'] = $this->ServiceModel->getService($userinfo['fk_role_id']);
            
            //获取服务商的资料信息
            if (!empty($service['role'])){
                $service['data'] = $this->CompanyModel->getCompany($userinfo['fk_role_id'],3);
                $service['data']['scope'] = $scope[$service['data']['scope']];
                $service['data']['nature'] = $nature[$service['data']['nature']];

                //获取其它资质图片数据
                $service['attachment'] = $this->ServiceModel->getAttachment($service['data']['id']);
                //获取服务关系数据
                $servicerelation = $this->ServiceModel->getServiceRelation($service['role']['id']);

                $industry = '';
                if (!empty($servicerelation )){
                    //获取行业名称
                    foreach ($servicerelation  as $value){
                        $trade = $this->CategoryModel->getServiceCategoryOne($value['fk_industry_id']);
                        $industry .= "&nbsp;&nbsp;".$trade['name'];
                    }
                }
                $service['data']['category'] = $industry;
            }
            $this->assign('company',$service);
        }
        return $this->fetch();
    }

    /**
     * 编辑企业资料
     */
    public function company_edit(){
        //获取用户登录信息
        $userId = Cookie::get("userinfo")['id'];
        $userinfo = $this->MemberModel->getMemberInfo($userId);
        if (request()->post()){
            //获取表单数据
            $id = $userinfo['fk_role_id'];
            $company_id = request()->param('companyid');
            $data['name'] = request()->param('name');
            $data['imgurl'] = request()->param('imgurl');
            $data['nature'] = request()->param('nature');
            $data['scope'] = request()->param('scope');
            $data['founding_time'] = request()->param('founding_time');
            $data['registered'] = request()->param('registered');
            $data['address'] = request()->param('address');
            $data['website'] = request()->param('website');
            $data['wechat_qrcode'] = request()->param('wechat_qrcode');
            $data['client_qrcode'] = request()->param('client_qrcode');
            $data['keywords'] = request()->param('keywords');
            $data['aboutus'] = request()->param('aboutus');
            $data['contacts_person'] = request()->param('contact_person');
            $data['contacts_mobile'] = request()->param('contact_mobile');
            $data['contacts_phone'] = request()->param('contact_phone');
            $data['contacts_title'] = request()->param('contact_title');
            $data['contacts_email'] = request()->param('contact_email');
            if ($userinfo['role'] == 1){
                //主办方资料修改
                $city = request()->param('city');
                $district = request()->param('district');
                //直辖市处理
                if ($district == ''){
                    $roledata['fk_region_id'] = $city;
                }else{
                    $roledata['fk_region_id'] = $district;
                }
                //如果没上传图片就用原来的图片
                if ($data['imgurl'] == ''){
                    unset($data['imgurl']);
                }
                if ($data['wechat_qrcode'] == ''){
                    unset($data['wechat_qrcode']);
                }
                if ($data['client_qrcode'] == ''){
                    unset($data['client_qrcode']);
                }
                //角色主表数据
                $roledata['utime'] = time();
                //编辑主办方数据-角色主表
                $result = $this->SponsorModel->putSponsor($id,$roledata);
                if ($result){
                    //获得角色id后编辑企业资料表
                    $this->CompanyModel->putCompanyData($id,1,$data);

                    return json_encode(array(1,'修改资料成功'));
                }else{
                    return json_encode(array(0,'修改资料失败'));
                }

            }elseif($userinfo['role'] == 2){
                //参展商资料修改
                $city = request()->param('city');
                $district = request()->param('district');
                //直辖市处理
                if ($district == ''){
                    $roledata['fk_region_id'] = $city;
                }else{
                    $roledata['fk_region_id'] = $district;
                }
                //如果没上传图片就用原来的图片
                if ($data['imgurl'] == ''){
                    unset($data['imgurl']);
                }
                if ($data['wechat_qrcode'] == ''){
                    unset($data['wechat_qrcode']);
                }
                if ($data['client_qrcode'] == ''){
                    unset($data['client_qrcode']);
                }
                //角色主表数据
                $roledata['utime'] = time();

                //编辑展商数据-角色主表
                $result = $this->ExhibitorModel->putExhibitor($id,$roledata);
                if ($result){
                    //获得角色id后编辑企业资料表
                    $this->CompanyModel->putCompanyData($id,2,$data);

                    return json_encode(array(1,'修改资料成功'));
                }else{
                    return json_encode(array(0,'修改资料失败'));
                }

            }elseif($userinfo['role'] == 3){
                //服务商资料修改
                $city = request()->param('city');
                $district = request()->param('district');
                //直辖市处理
                if ($district == ''){
                    $roledata['fk_region_id'] = $city;
                }else{
                    $roledata['fk_region_id'] = $district;
                }
                //如果没上传图片就用原来的图片
                if ($data['imgurl'] == ''){
                    unset($data['imgurl']);
                }
                if ($data['wechat_qrcode'] == ''){
                    unset($data['wechat_qrcode']);
                }
                if ($data['client_qrcode'] == ''){
                    unset($data['client_qrcode']);
                }
                //角色主表数据
                $roledata['status'] = 2;
                $roledata['utime'] = time();

                //编辑服务商数据-角色主表
                $result = $this->ServiceModel->putService($id,$roledata);
                if ($result){
                    //获得角色id后编辑企业资料表
                    $this->CompanyModel->putCompanyData($id,3,$data);

                    return json_encode(array(1,'修改资料成功'));
                }else{
                    return json_encode(array(0,'修改资料失败'));
                }
            }
        }else{
            if ($userinfo['role'] == 1){
                //获取主办方数据
                $id = request()->param('id');
                $status = request()->param('status');
                //获取该主办方的角色及资质数据
                $sponsor['role'] = $this->SponsorModel->getSponsor($userinfo['fk_role_id']);
                //获取干主办方的资料信息
                $sponsor['data'] = $this->CompanyModel->getCompany($userinfo['fk_role_id'],1);
                //获取其它资质图片数据
                $sponsor['attachment'] = $this->SponsorModel->getAttachment($sponsor['data']['id']);
                //获得城市联动数据
                $province = $this->RegionModel->getProvinceAll();
                //获取县区的父id
                $cityid = $this->RegionModel->getArea($sponsor['role']['fk_region_id']);
                //获取县区
                $district = $this->RegionModel->getDistrict($cityid['parent_id']);
                // var_dump($district);
                //获取城市的父id
                $provinceid = $this->RegionModel->getArea($cityid['parent_id']);

                //如果省级id为一则是直辖市，则取id不取parent_id
                if ($provinceid['parent_id'] == 1){
                    $proid = $cityid['parent_id'];
                }else{
                    $proid = $provinceid['parent_id'];
                }
                //获取城市
                $city = $this->RegionModel->getCity($proid);
                $this->assign('provinceid',$proid);
                $this->assign('province',$province);
                //如果是直辖市的联动处理
                if($city[0]['city'] == ''){
                    foreach ($district as $key=>$value){
                        $districts[$key]['id'] = $value['id'];
                        $districts[$key]['city'] = $value['district'];
                    }
                    $this->assign('city',$districts);
                    $this->assign('cityid',$sponsor['role']['fk_region_id']);
                    $this->assign('district','');
                }else{
                    $this->assign('city',$city);
                    $this->assign('cityid',$cityid['parent_id']);
                    $this->assign('district',$district);
                }
                $this->assign('company',$sponsor);
            }elseif($userinfo['role'] == 2){
                //获取该展商的角色及资质数据
                $exhibitor['role'] = $this->ExhibitorModel->getExhibitor($userinfo['fk_role_id']);
                //获取干展商的资料信息
                $exhibitor['data'] = $this->CompanyModel->getCompany($userinfo['fk_role_id'],2);
                //获取其它资质图片数据
                $exhibitor['attachment'] = $this->ExhibitorModel->getAttachment($exhibitor['data']['id']);
                //获得城市联动数据
                $province = $this->RegionModel->getProvinceAll();
                //获取县区的父id
                $cityid = $this->RegionModel->getArea($exhibitor['role']['fk_region_id']);
                //获取县区
                $district = $this->RegionModel->getDistrict($cityid['parent_id']);
                // var_dump($district);
                //获取城市的父id
                $provinceid = $this->RegionModel->getArea($cityid['parent_id']);

                //如果省级id为一则是直辖市，则取id不取parent_id
                if ($provinceid['parent_id'] == 1){
                    $proid = $cityid['parent_id'];
                }else{
                    $proid = $provinceid['parent_id'];
                }
                //获取城市
                $city = $this->RegionModel->getCity($proid);

                $this->assign('provinceid',$proid);
                $this->assign('province',$province);
                //如果是直辖市的联动处理
                if($city[0]['city'] == ''){
                    foreach ($district as $key=>$value){
                        $districts[$key]['id'] = $value['id'];
                        $districts[$key]['city'] = $value['district'];
                    }
                    $this->assign('city',$districts);
                    $this->assign('cityid',$exhibitor['role']['fk_region_id']);
                    $this->assign('district','');
                }else{
                    $this->assign('city',$city);
                    $this->assign('cityid',$cityid['parent_id']);
                    $this->assign('district',$district);
                }
                //获取行业与展商对应数据
                $traderelation = $this->ExhibitorModel->getTradeRelation($exhibitor['role']['id']);
                $industry = '';
                if (!empty($traderelation )){
                    //获取行业名称
                    foreach ($traderelation  as $value){
                        $trade = $this->CategoryModel->getTradeCategoryOne($value['fk_industry_id']);
                        $industry .= "&nbsp;&nbsp;".$trade['name'];
                    }
                }
                $exhibitor['data']['category'] = $industry;

                $this->assign('company',$exhibitor);

            }elseif($userinfo['role'] == 3){

                //获取该服务商的角色及资质数据
                $service['role'] = $this->ServiceModel->getService($userinfo['fk_role_id']);
                //获取服务商的资料信息
                $service['data'] = $this->CompanyModel->getCompany($userinfo['fk_role_id'],3);
                //获取其它资质图片数据
                $service['attachment'] = $this->ServiceModel->getAttachment($service['data']['id']);
                //获得城市联动数据
                $province = $this->RegionModel->getProvinceAll();
                //获取县区的父id
                $cityid = $this->RegionModel->getArea($service['role']['fk_region_id']);
                //获取县区
                $district = $this->RegionModel->getDistrict($cityid['parent_id']);
                // var_dump($district);
                //获取城市的父id
                $provinceid = $this->RegionModel->getArea($cityid['parent_id']);

                //如果省级id为一则是直辖市，则取id不取parent_id
                if ($provinceid['parent_id'] == 1){
                    $proid = $cityid['parent_id'];
                }else{
                    $proid = $provinceid['parent_id'];
                }
                //获取城市
                $city = $this->RegionModel->getCity($proid);

                $this->assign('provinceid',$proid);
                $this->assign('province',$province);
                //如果是直辖市的联动处理
                if($city[0]['city'] == ''){
                    foreach ($district as $key=>$value){
                        $districts[$key]['id'] = $value['id'];
                        $districts[$key]['city'] = $value['district'];
                    }
                    $this->assign('city',$districts);
                    $this->assign('cityid',$service['role']['fk_region_id']);
                    $this->assign('district','');
                }else{
                    $this->assign('city',$city);
                    $this->assign('cityid',$cityid['parent_id']);
                    $this->assign('district',$district);
                }
                //获取行业与展商对应数据
                $servicerelation = $this->ServiceModel->getServiceRelation($userinfo['fk_role_id']);
                $industry = '';
                if (!empty($servicerelation)){
                    //获取行业名称
                    foreach ($servicerelation  as $value){
                        $trade = $this->CategoryModel->getServiceCategoryOne($value['fk_industry_id']);
                        $industry .= "&nbsp;&nbsp;".$trade['name'];
                    }
                }
                $service['data']['category'] = $industry;
                $this->assign('company',$service);
            }
        }

        return $this->fetch();
    }

    /**
     * 主办方发布展会
     */
    public function exhibition_pub(){

        if (request()->post()){
            //获取展会主表数据
            $exh_data['title'] = request()->param('title');
            $exh_data['begin_time'] = strtotime(request()->param('begin_time'));
            $exh_data['end_time'] =  strtotime(request()->param('end_time'));
            $exh_data['ticket'] = request()->param('ticket');
            $exh_data['thumb'] = request()->param('thumb');
            $exh_data['hall'] = request()->param('hall');
            $exh_data['contacts_person'] = request()->param('contacts_person');
            $exh_data['contacts_mobile'] = request()->param('contacts_mobile');
            $exh_data['keywords'] = request()->param('keywords');
            $exh_data['scale'] = request()->param('scale');
            $exh_data['sponsor'] = request()->param('sponsor');
            $exh_data['sponsor_info'] = request()->param('sponsor_info');
            $exh_data['organizer'] = request()->param('organizer');
            $exh_data['co_organizer'] = request()->param('co_organizer');
            $exh_data['exhibition_cycle'] = request()->param('exhibition_cycle');
            $exh_data['exhibits_profile'] = request()->param('exhibits_profile');
            $exh_data['exhibition_info'] = request()->param('exhibition_info');
            $exh_data['contactway'] = request()->param('contactway');
            $exh_data['visiting'] = request()->param('visiting');
            $exh_data['traffic'] = request()->param('traffic');
            //如果选择了门票免费则门票为0
            if(request()->param('free')){
                $exh_data['ticket'] = 0;
            }
            //直辖市处理
            $city = request()->param('city');
            $district = request()->param('district');
            if ($district == '') {
                $exh_data['fk_region_id'] = $city;
            } else {
                $exh_data['fk_region_id'] = $district;
            }
            //主表数据入库
            //获取用户登录信息
            $userId = Cookie::get("userinfo")['id'];
            $user = $this->MemberModel->getMemberInfo($userId);
            $exh_data['pub_type'] = 1;//前台发布
            $exh_data['status'] = 4;//默认待审核状态
            $exh_data['fk_pub_id'] = $user['fk_role_id'];//发布者id
            $exh_data['ctime'] = time();
            $exh_id = $this->ExhibitionModel->postExhibition($exh_data);
            if ($exh_id) {
                //展位图入库
                $floorplan = request()->param('floorplan');
                if ($floorplan != '') {
                    if (is_array(explode(',', $floorplan))) {
                        foreach (explode(',', $floorplan) as $value) {
                            $floor_data['fk_exhibition_id'] = $exh_id;
                            $floor_data['floorplan_url'] = $value;
                            $re = $this->ExhibitionModel->postFloorplan($floor_data);
                            if (!$re) {
                                return json_encode(array(0,'添加展会失败'));
                            }
                        }
                    } else {
                        $floor_data['fk_exhibition_id'] = $exh_id;
                        $floor_data['floorplan_url'] = $floorplan;
                        $re = $this->ExhibitionModel->postFloorplan($floor_data);
                        if (!$re) {
                            return json_encode(array(0,'添加展会失败'));
                        }
                    }
                }
                //轮播图入库
                $img = request()->param('imgurl');
                if ($img != '') {
                    if (is_array(explode(',', $img))) {
                        foreach (explode(',', $img) as $value) {
                            $img_data['fk_exhibition_id'] = $exh_id;
                            $img_data['imgurl'] = $value;
                            $re = $this->ExhibitionModel->postImg($img_data);
                            if (!$re) {
                                return json_encode(array(0,'添加展会失败'));
                            }
                        }
                    } else {
                        $img_data['fk_exhibition_id'] = $exh_id;
                        $img_data['imgurl'] = $img;
                        $re = $this->ExhibitionModel->postImg($img_data);
                        if (!$re) {
                            return json_encode(array(0,'添加展会失败'));
                        }
                    }
                }
                //展位情况数据入库
                $booth = request()->param('booth/a');
                if (!empty($booth)) {
                    $booth = array_chunk($booth, 5);
                    foreach ($booth as $key => $value) {
                        $booth_data['fk_exhibition_id'] = $exh_id;
                        $booth_data['booth_grade'] = $value[0];
                        $booth_data['booth_type'] = $value[1];
                        $booth_data['open_type'] = $value[2];
                        $booth_data['acreage'] = $value[3];
                        $booth_data['price'] = $value[4];
                        $re = $this->ExhibitionModel->postExhibitionBooth($booth_data);
                        if (!$re) {
                            return json_encode(array(0,'添加展会失败'));
                        }
                    }
                }
                //往届回顾数据入库
                $last_exh = request()->param('last_exh/a');
                if (!empty($last_exh)) {
                    $last_exh = array_chunk($last_exh, 5);
                    foreach ($last_exh as $key => $value) {
                        $last_data['fk_exhibition_id'] = $exh_id;
                        $last_data['title'] = $value[0];
                        $last_data['begin_time'] = strtotime($value[1]);
                        $last_data['end_time'] = strtotime($value[2]);
                        $last_data['hall'] = $value[3];
                        $last_data['scale'] = $value[4];
                        $re = $this->ExhibitionModel->postExhibitionReview($last_data);
                        if (!$re) {
                            return json_encode(array(0,'添加展会失败'));
                        }
                    }
                }
                //行业分类入行业分类展会关系表
                $trade = request()->param('trade/a');
                if (!empty($trade)) {
                    foreach ($trade as $value) {
                        $tradedata['fk_industry_id'] = $value;
                        $tradedata['fk_exhibition_id'] = $exh_id;
                        $relationid = $this->ExhibitionModel->postExhibitionRelationTrade($tradedata);
                        if (!$relationid) {
                            return json_encode(array(0,'添加展会失败'));
                        }
                    }
                }
                return json_encode(array(1,'添加展会成功'));
            }else{
                return json_encode(array(0,'添加展会失败'));
            }

        }else{

            //获取地区省份信息
            $province = $this->RegionModel->getProvinceAll();
            //获取行业信息
            $trade = $this->CategoryModel->getTradeFirst();
            foreach ($trade as $key => $value) {
                $tradeson = $this->CategoryModel->getTradeSecond($value['id']);
                $trade[$key]['tradeson'] = $tradeson;
            }
            $this->assign('trade', $trade);
            $this->assign('province',$province);
            return $this->fetch();
        }
    }

    /**
     * 展会管理/获取默认加载的已上线展会
     */
    public function exhibition_manage(){
        //获取用户登录信息
        $userId = Cookie::get("userinfo")['id'];
        $userinfo = $this->MemberModel->getMemberInfo($userId);
        $status = request()->param('status');
        $title = request()->param('title');

        //获取该发布人的所有上线展会
        $exhibition = $this->ExhibitionModel->getExhBySponsor($userinfo['fk_role_id'], $status, $title);

        //获取该发布人的所有上线展会
        $count = $this->ExhibitionModel->getCountBySponsor($userinfo['fk_role_id']);

        //去重处理
        if (!empty($exhibition)) {
            foreach ($exhibition as $value) {
                $value['begin_time'] = date("Y-m-d", $value['begin_time']);
                $value['end_time'] = date("Y-m-d", $value['end_time']);

                $data[$value['id']] = $value;
            }
            foreach ($data as $item) {
                $exh[] = $item;
            }
        }else{
            $exh = '';
        }
        $this->assign('count',$count);
        $this->assign('status',$status);
        $this->assign('exhibition',json_encode($exh));
        return $this->fetch();
    }
    
    /**
     * 承办商-参展商管理
     */
    public function exhibition_company(){
        return $this->fetch();
    }

    /**
     * 承办商-观众管理
     */
    public function exhibition_audience(){
        return $this->fetch();
    }

    /**
     * 承办商-观众管理
     */
    public function exhibition_enroll(){
        return $this->fetch();
    }

    /**
     *填写企业资料
     */
    public function company_add(){
        if (request()->post()){
            //获取用户登录信息
            $userId = Cookie::get("userinfo")['id'];
            $userinfo = $this->MemberModel->getMemberInfo($userId);
            //获取表单数据
            $data['name'] = request()->param('name');
            $data['imgurl'] = request()->param('imgurl');
            $data['nature'] = request()->param('nature');
            $data['scope'] = request()->param('scope');
            $data['founding_time'] = request()->param('founding_time');
            $data['registered'] = request()->param('registered');
            $data['address'] = request()->param('address');
            $data['website'] = request()->param('website');
            $data['wechat_qrcode'] = request()->param('wechat_qrcode');
            $data['client_qrcode'] = request()->param('client_qrcode');
            $data['keywords'] = request()->param('keywords');
            $data['aboutus'] = request()->param('aboutus');
            $data['contacts_person'] = request()->param('contact_person');
            $data['contacts_mobile'] = request()->param('contact_mobile');
            $data['contacts_phone'] = request()->param('contact_phone');
            $data['contacts_title'] = request()->param('contact_title');
            $data['contacts_email'] = request()->param('contact_email');
            if ($userinfo['role'] == 1){
                //主办方资料完善
                //企业资质信息
                $attachment = array(
                    array('type'=>1,'attachment_url'=>request()->param('license_url')),
                    array('type'=>2,'attachment_url'=>request()->param('attachment_url'))
                );
                //直辖市处理
                $city = request()->param('city');
                $district = request()->param('district');
                if ($district == ''){
                    $roledata['fk_region_id'] = $city;
                }else{
                    $roledata['fk_region_id'] = $district;
                }
                //角色主表数据
                $roledata['ctime'] = time();
                $roledata['fk_admin_id'] = $userinfo['role'];
                //添加主办方数据-角色主表
                $sponsor_id = $this->SponsorModel->postSponsor($roledata);
                if ($sponsor_id){
                    $data['fk_role_id'] = $sponsor_id;
                    //获得角色id后入企业资料表
                    $data['role_type'] = 1;
                    $company_id = $this->CompanyModel->postCompanyData($data);
                    if ($company_id){
                        //营业执照及其它资质图片入表
                        foreach ($attachment as $value){
                            if ($value['type'] == 1){
                                //营业执照，单图片
                                $value['fk_company_id'] = $company_id;
                                $id = $this->SponsorModel->postAttachment($value);
                                if (!$id){
                                    return json_encode(array(0,'添加资料失败'));
                                }
                            }else{
                                if ($value['attachment_url'] !=''){
                                    if (explode(',',$value['attachment_url'] )){
                                        //其它资质，多图片
                                        foreach (explode(',',$value['attachment_url']) as $val){
                                            $attdata['fk_company_id'] = $company_id;
                                            $attdata['type'] = $value['type'];
                                            $attdata['attachment_url'] = $val;
                                            $id = $this->SponsorModel->postAttachment($attdata);
                                            if (!$id){
                                                return json_encode(array(0,'添加资料失败'));
                                            }
                                        }
                                    }else{
                                        $attdata['fk_company_id'] = $company_id;
                                        $attdata['type'] = $value['type'];
                                        $attdata['attachment_url'] = $value['attachment_url'];
                                        $id = $this->SponsorModel->postAttachment($attdata);
                                        if (!$id){
                                            return json_encode(array(0,'添加资料失败'));
                                        }
                                    }
                                }
                            }
                        }
                        //资料入表后更新登录表
                        $this->MemberModel->putMember($userinfo['id'],array('fk_role_id'=>$sponsor_id));
                        //更新登录cookie
                        $userinfo['fk_role_id'] = $sponsor_id;
                        // Cookie::set($userinfo);
                        return json_encode(array(1,'添加资料成功'));
                    }else{
                        return json_encode(array(0,'添加资料失败'));
                    }
                }else{
                    return json_encode(array(0,'添加资料失败'));
                }
            }elseif($userinfo['role'] == 2){
                //完善参展商资料
                $trade = request()->param('trade/a');
                //企业资质信息
                $attachment = array('type' => 1, 'attachment_url' => request()->param('license_url'));
                //直辖市处理
                $city = request()->param('city');
                $district = request()->param('district');
                if ($district == '') {
                    $roledata['fk_region_id'] = $city;
                } else {
                    $roledata['fk_region_id'] = $district;
                }
                //角色主表数据
                $roledata['ctime'] = time();
                //添加展商数据-角色主表
                $exhibitor_id = $this->ExhibitorModel->postExhibitor($roledata);

                if ($exhibitor_id) {
                    $data['fk_role_id'] = $exhibitor_id;
                    //获得角色id后入企业资料表
                    $data['role_type'] = 2;
                    $company_id = $this->CompanyModel->postCompanyData($data);
                    if ($company_id) {
                        //营业执照及其它资质图片入表
                        if ($attachment['type'] == 1) {
                            //营业执照，单图片
                            $attachment['fk_company_id'] = $company_id;
                            $id = $this->ExhibitorModel->postAttachment($attachment);
                            if (!$id) {
                                return json_encode(array(0,'添加资料失败'));
                            }
                        }
                    } else {
                        return json_encode(array(0,'添加资料失败'));
                    }
                    //行业分类入行业分类角色关系表
                    if (!empty($trade)){
                        foreach ($trade as $value){
                            $tradedata['fk_industry_id'] = $value;
                            $tradedata['fk_role_exhibitor_id'] = $exhibitor_id;
                            $relationid= $this->ExhibitorModel->postExhRelationTrade($tradedata);
                            if (!$relationid){
                                return json_encode(array(0,'添加资料失败'));
                            }
                        }
                    }
                    //资料入表后更新登录表
                    $this->MemberModel->putMember($userinfo['id'],array('fk_role_id'=>$exhibitor_id));
                    //更新登录cookie
                    $userinfo['fk_role_id'] = $exhibitor_id;
                    // Cookie::set($userinfo);
                    return json_encode(array(1,'添加资料成功'));
                } else {
                    return json_encode(array(0,'添加资料失败'));
                }
            }elseif($userinfo['role'] == 3){
                //完善服务商资料
                $service = request()->param('service/a');
                //企业资质信息
                $attachment = array('type' => 1, 'attachment_url' => request()->param('license_url'));

                $city = request()->param('city');
                $district = request()->param('district');
                if ($district == '') {
                    $roledata['fk_region_id'] = $city;
                } else {
                    $roledata['fk_region_id'] = $district;
                }
                //角色主表数据
                $roledata['ctime'] = time();
                //添加服务商数据-角色主表
                $service_id = $this->ServiceModel->postService($roledata);

                if ($service_id) {
                    $data['fk_role_id'] = $service_id;
                    //获得角色id后入企业资料表
                    $data['role_type'] = 3;
                    $company_id = $this->CompanyModel->postCompanyData($data);
                    if ($company_id) {
                        //营业执照及其它资质图片入表
                        if ($attachment['type'] == 1) {
                            //营业执照，单图片
                            $attachment['fk_company_id'] = $company_id;
                            $id = $this->ServiceModel->postAttachment($attachment);
                            if (!$id) {
                                return json_encode(array(0,'添加资料失败'));
                            }
                        }
                    } else {
                        return json_encode(array(0,'添加资料失败'));
                    }
                    //行业分类入行业分类角色关系表
                    if (!empty($service)){
                        foreach ($service as $value){
                            $servicedata['fk_industry_id'] = $value;
                            $servicedata['fk_role_service_id'] = $service_id;
                            $relationid= $this->ServiceModel->postSerRelationService($servicedata);
                            if (!$relationid){
                                return json_encode(array(0,'添加资料失败'));
                            }
                        }
                    }
                    //资料入表后更新登录表
                    $this->MemberModel->putMember($userinfo['id'],array('fk_role_id'=>$service_id));
                    $userinfo['fk_role_id'] = $service_id;
                    // Cookie::set($userinfo);
                    return json_encode(array(1,'添加资料成功'));
                } else {
                    return json_encode(array(0,'添加资料失败'));
                }
            }
        }else{
            //获取用户登录信息
            $userId = Cookie::get("userinfo")['id'];
            $userinfo = $this->MemberModel->getMemberInfo($userId);
            // dump($userinfo);die;
            // 获取用户企业审核状态
            switch ($userinfo['role']) {
                case 1:
                    $roleStatus = $this->SponsorModel->getSponsor($userinfo['fk_role_id'])['status'];
                    break;
                case 2:
                    $roleStatus = $this->ExhibitorModel->getExhibitor($userinfo['fk_role_id'])['status'];
                    break;
                case 3:
                    $roleStatus = $this->ServiceModel->getService($userinfo['fk_role_id'])['status'];
                    break;
            }

            //获取地区省份信息
            $province = $this->RegionModel->getProvinceAll();
            //获取行业信息
            $service = $this->CategoryModel->getServiceFirst();

            foreach ($service as $key => $value) {
                $serviceson = $this->CategoryModel->getServiceSecond($value['id']);
                $service[$key]['serviceson'] = $serviceson;
            }
            //获取行业信息
            $trade = $this->CategoryModel->getTradeFirst();

            foreach ($trade as $key => $value) {
                $tradeson = $this->CategoryModel->getTradeSecond($value['id']);
                $trade[$key]['tradeson'] = $tradeson;
            }
            $this->assign('trade', $trade);
            $this->assign('service', $service);
            $this->assign('role_status', $roleStatus);
            $this->assign('province', $province);
            return $this->fetch();
        }
    }

    /**
     * 主办方修改展会信息
     */
    public function exhibition_edit(){
        if (request()->post()){
            $id = request()->param('id');

            //获取展会主表数据
            $exh_data['title'] = request()->param('title');
            $exh_data['begin_time'] = strtotime(request()->param('begin_time'));
            $exh_data['end_time'] =  strtotime(request()->param('end_time'));
            $exh_data['ticket'] = request()->param('ticket');
            $exh_data['thumb'] = request()->param('thumb');
            $exh_data['hall'] = request()->param('hall');
            $exh_data['contacts_person'] = request()->param('contacts_person');
            $exh_data['contacts_mobile'] = request()->param('contacts_mobile');
            $exh_data['keywords'] = request()->param('keywords');
            $exh_data['scale'] = request()->param('scale');
            $exh_data['sponsor'] = request()->param('sponsor');
            $exh_data['sponsor_info'] = request()->param('sponsor_info');
            $exh_data['organizer'] = request()->param('organizer');
            $exh_data['co_organizer'] = request()->param('co_organizer');
            $exh_data['exhibition_cycle'] = request()->param('exhibition_cycle');
            $exh_data['exhibits_profile'] = request()->param('exhibits_profile');
            $exh_data['exhibition_info'] = request()->param('exhibition_info');
            $exh_data['contactway'] = request()->param('contactway');
            $exh_data['visiting'] = request()->param('visiting');
            $exh_data['traffic'] = request()->param('traffic');
            //如果选择了门票免费则门票为0
            if(request()->param('free')){
                $exh_data['ticket'] = 0;
            }
            //直辖市处理
            $city = request()->param('city');
            $district = request()->param('district');
            if ($district == '') {
                $exh_data['fk_region_id'] = $city;
            } else {
                $exh_data['fk_region_id'] = $district;
            }
            //主表数据入库
            $exh_data['status'] = 4;
            $exh_data['utime'] = time();
            $result = $this->ExhibitionModel->putExhibition($exh_data,$id);
            
            if ($result){
                //展位图入库
                $floorplan = request()->param('floorplan');

                if ($floorplan !=''){
                    if (is_array(explode(',',$floorplan))){
                        foreach (explode(',',$floorplan ) as $value){
                            $floor_data['fk_exhibition_id'] = $id;
                            $floor_data['floorplan_url'] = $value;
                            $re = $this->ExhibitionModel->postFloorplan($floor_data);
                            if (!$re){
                                return json_encode(array(0,'修改展会失败'));
                            }
                        }
                    }else{
                        $floor_data['fk_exhibition_id'] = $id;
                        $floor_data['floorplan_url'] = $floorplan;
                        $re = $this->ExhibitionModel->postFloorplan($floor_data);
                        if (!$re){
                            return json_encode(array(0,'修改展会失败'));
                        }
                    }
                }

                //轮播图入库
                $img = request()->param('imgurl');
                if ($img !=''){
                    if (is_array(explode(',',$img))){
                        foreach (explode(',',$img ) as $value){
                            $img_data['fk_exhibition_id'] = $id;
                            $img_data['imgurl'] = $value;
                            $re = $this->ExhibitionModel->postImg($img_data);
                            if (!$re){
                                return json_encode(array(0,'修改展会失败'));
                            }
                        }
                    }else{
                        $img_data['fk_exhibition_id'] = $id;
                        $img_data['imgurl'] = $img;
                        $re = $this->ExhibitionModel->postImg($img_data);
                        if (!$re){
                            return json_encode(array(0,'修改展会失败'));
                        }
                    }
                }
                //展位情况数据入库
                $booth = request()->param('booth/a');

                if (!empty($booth)){
                    //先删除原有数据，再执行添加
                    Db::name('exhibition_booth')->where('fk_exhibition_id',$id)->delete();
                    $booth = array_chunk($booth,5);
                    foreach ($booth as $key=>$value){

                        if ($value[0] !='' && $value[1] !='' && $value[2] !='' && $value[3] !='' && $value[4] !=''){
                            $booth_data['fk_exhibition_id'] = $id;
                            $booth_data['booth_grade'] = $value[0];
                            $booth_data['booth_type'] = $value[1];
                            $booth_data['open_type'] = $value[2];
                            $booth_data['acreage'] = $value[3];
                            $booth_data['price'] = $value[4];
                            $re = $this->ExhibitionModel->postExhibitionBooth($booth_data);
                            if (!$re){
                                return json_encode(array(0,'修改展会失败'));
                            }
                        } else {
                            $booth_data['fk_exhibition_id'] = $id;
                            $booth_data['booth_grade'] = '';
                            $booth_data['booth_type'] = 0;
                            $booth_data['open_type'] = 0;
                            $booth_data['acreage'] = '';
                            $booth_data['price'] = '';
                            $re = $this->ExhibitionModel->postExhibitionBooth($booth_data);
                            if (!$re){
                                return json_encode(array(0,'修改展会失败'));
                            }
                        }
                    }
                }
                // dump($booth);die;
                //往届回顾数据入库
                $last_exh = request()->param('last_exh/a');
                if (!empty($last_exh)){
                    //先删除原有数据，再执行添加
                    Db::name('exhibition_review')->where('fk_exhibition_id',$id)->delete();
                    $last_exh = array_chunk($last_exh,5);
                    foreach ($last_exh as $key=>$value) {
                        if ($value[0] != '' && $value[1] != '' && $value[2] != '' && $value[3] != '' && $value[4] != '') {
                            $last_data['fk_exhibition_id'] = $id;
                            $last_data['title'] = $value[0];
                            $last_data['begin_time'] = strtotime($value[1]);
                            $last_data['end_time'] = strtotime($value[2]);
                            $last_data['hall'] = $value[3];
                            $last_data['scale'] = $value[4];
                            $re = $this->ExhibitionModel->postExhibitionReview($last_data);
                            if (!$re) {
                                return json_encode(array(0,'修改展会失败'));
                            }
                        }
                    }
                }
                //行业分类入行业分类展会关系表
                $trade = request()->param('trade/a');
                if (!empty($trade)){
                    //先删除原有数据，再执行添加
                    Db::name('relation_industry_exhibition')->where('fk_exhibition_id',$id)->delete();
                    foreach ($trade as $value){
                        $tradedata['fk_industry_id'] = $value;
                        $tradedata['fk_exhibition_id'] = $id;
                        $relationid= $this->ExhibitionModel->postExhibitionRelationTrade($tradedata);
                        if (!$relationid){
                            return json_encode(array(0,'修改展会失败'));
                        }
                    }
                }
                return json_encode(array(1,'修改展会成功'));

            }else{
                return json_encode(array(0,'修改展会失败'));
            }

        }else{

            $id = request()->param('id');
            //获取展会、展位图片、展会轮播图信息
            $exhibition = $this->ExhibitionModel->getExhibitionOne($id);
            //获取展位情况
            $booth = $this->ExhibitionModel->getBooth($id);
            //获取往届回顾
            $review = $this->ExhibitionModel->getReview($id);
            //获取轮播图
            $floorplan = $this->ExhibitionModel->getFloorplan($id);
            //获取展位图
            $img = $this->ExhibitionModel->getImg($id);
            //获取地区省份信息
            $province = $this->RegionModel->getProvinceAll();

            //获取县区的父id
            $cityid = $this->RegionModel->getArea($exhibition['fk_region_id']);
            //获取县区
            $district = $this->RegionModel->getDistrict($cityid['parent_id']);
            // var_dump($district);
            //获取城市的父id
            $provinceid = $this->RegionModel->getArea($cityid['parent_id']);

            //如果省级id为一则是直辖市，则取id不取parent_id
            if ($provinceid['parent_id'] == 1){
                $proid = $cityid['parent_id'];
            }else{
                $proid = $provinceid['parent_id'];
            }
            //获取城市
            $city = $this->RegionModel->getCity($proid);

            $this->assign('provinceid',$proid);
            $this->assign('province',$province);
            //如果是直辖市的联动处理
            if($city[0]['city'] == ''){
                foreach ($district as $key=>$value){
                    $districts[$key]['id'] = $value['id'];
                    $districts[$key]['city'] = $value['district'];
                }
                $this->assign('city',$districts);
                $this->assign('cityid',$exhibition['fk_region_id']);
                $this->assign('district','');
            }else{
                $this->assign('city',$city);
                $this->assign('cityid',$cityid['parent_id']);
                $this->assign('district',$district);
            }
            //获取行业信息
            $trade = $this->CategoryModel->getTradeFirst();

            foreach ($trade as $key => $value) {
                $tradeson = $this->CategoryModel->getTradeSecond($value['id']);
                $trade[$key]['tradeson'] = $tradeson;
            }
            //获取行业与展商对应数据
            $relationid = $this->ExhibitionModel->getTradeRelation($id);
            if (!empty($relationid)){
                foreach ($relationid as $value){
                    $tradeid[] = $value['fk_industry_id'];
                }
            }else{
                $tradeid = '';
            }

            $this->assign('floorplan',$floorplan);
            $this->assign('img',$img);
            $this->assign('exhibition',$exhibition);
            $this->assign('booth',$booth);
            $this->assign('review',$review);
            $this->assign('tradeid',$tradeid);
            $this->assign('trade', $trade);
            $this->assign('province',$province);
            return $this->fetch();
        }
    }

    /**
     * 回收展会
     */
    public function exhibition_switch(){
        $id = request()->param('id');
        $status = request()->param('status');
        $this->ExhibitionModel->switchExhibition($id,$status);
        echo 1;
    }

    /**
     * 删除展会
     */
    public function exhibition_del(){
        $id = request()->param('id');
        $this->ExhibitionModel->delExhibitionOne($id);

        echo 1;
    }

    /**
     * 预定展会
     */
    public function exhibition_booking(){
        return $this->fetch();
    }

    /**
     * 发布咨询
     */
    public function article_pub(){
        if (request()->post()) {
            //获取用户登录信息
            $userId = Cookie::get("userinfo")['id'];
            $userinfo = $this->MemberModel->getMemberInfo($userId);

            //获取资讯数据
            $data['title'] = request()->param('title');
            if (empty($data['title'])) {
                return json_encode(array(0,'请填写标题'));
            }
            $data['type'] = request()->param('type');
            $data['keywords'] = request()->param('keywords');
            if (empty($data['keywords'])) {
                return json_encode(array(0,'请填写关键词'));
            }
            $data['thumb'] = request()->param('thumb');
            $data['content'] = request()->param('content');
            if (empty($data['content'])) {
                return json_encode(array(0,'请填写资讯内容'));
            }
            $data['fk_member_id'] = $userinfo['id'];
            $data['pubtime'] = time();
            $data['ctime'] = time();
            $data['utime'] = time();

            //确定资讯类型
            if ($data['type'] == 3) {
                $data['news_column'] = 0;
                $data['fk_exhibition_id'] = request()->param('exh_id');
                if (empty($data['fk_exhibition_id'])) {
                    return json_encode(array(0,'请选择展会'));
                }
            }
            if ($data['type'] == 2) {
                
                $data['fk_company_id'] = $userinfo['fk_role_id'];
                $data['news_column'] = 0;
                $data['fk_exhibition_id'] = 0;

            }
            if ($data['type'] == 1) {
                $data['news_column'] = request()->param('article_type');
                $data['fk_exhibition_id'] = 0;
                if (empty($data['news_column'])) {
                    return json_encode(array(0,'请选择资讯栏目'));
                }
            }
            
            $result = $this->ArticleModel->postArticle($data);

            if ($result) {
                return json_encode(array(1,'发布资讯成功'));
            } else {
                return json_encode(array(0,'发布资讯失败'));
            }

        } else {
            //获取用户登录信息
            $userId = Cookie::get("userinfo")['id'];
            $userinfo = $this->MemberModel->getMemberInfo($userId);

            // 如果是普通资讯则显示下面的数据
            $config = include "../application/admin/extra/exhibitor.info.conf.php";
            //获得配置文件里的资讯类型
            $article_type = $config['article']['article_type'];
            //传输默认值
            $this->assign('article_type', $article_type);
          
            //获取该发布人的所有上线展会
            $exhibition = $this->ExhibitionModel->getExhBySponsor($userinfo['fk_role_id'], 1, '');

            //去重处理
            if (!empty($exhibition)) {
                foreach ($exhibition as $value) {
                    $value['begin_time'] = date("Y-m-d", $value['begin_time']);
                    $value['end_time'] = date("Y-m-d", $value['end_time']);
                    $data[$value['id']] = $value;
                }
                foreach ($data as $item) {
                    $exh[] = $item;
                }
            }else{
                $exh = '';
            }

            $this->assign('exhibition',$exh);
            return $this->fetch();

        }
    }

    /**
     * 发布咨询
     */
    public function article_edit(){
        if (request()->post()) {
            //获取用户登录信息
            $userId = Cookie::get("userinfo")['id'];
            $userinfo = $this->MemberModel->getMemberInfo($userId);
            //获取资讯数据
            $data['id'] = request()->param('id');
            $data['title'] = request()->param('title');
            $data['type'] = request()->param('type');
            $data['keywords'] = request()->param('keywords');
            $data['thumb'] = request()->param('thumb');
            $data['content'] = request()->param('content');
            $data['fk_member_id'] = $userinfo['id'];
            $data['pubtime'] = time();
            $data['status'] = 4;
            $data['ctime'] = time();
            $data['utime'] = time();

            //确定资讯类型
            if ($data['type'] == 3) {
                $data['news_column'] = 0;
                $data['fk_exhibition_id'] = request()->param('exh_id');
            }
            if ($data['type'] == 2) {
                $data['fk_company_id'] = $userinfo['fk_role_id'];
                $data['news_column'] = 0;
                $data['fk_exhibition_id'] = 0;
            }
            if ($data['type'] == 1) {
                $data['news_column'] = request()->param('article_type');
                $data['fk_exhibition_id'] = 0;
            }
            
            $result = $this->ArticleModel->updateNews($data);
            if ($result) {
                return json_encode(array(1,'修改资讯成功'));
            } else {
                return json_encode(array(0,'修改资讯失败'));
            }

        } else {
            //获取用户登录信息
            $userId = Cookie::get("userinfo")['id'];
            $userinfo = $this->MemberModel->getMemberInfo($userId);

            // 如果是普通资讯则显示下面的数据
            $config = include "../application/admin/extra/exhibitor.info.conf.php";
            //获得配置文件里的资讯类型
            $article_type = $config['article']['article_type'];
            //传输默认值
            $this->assign('article_type', $article_type);

            // 根据资讯ID获取资讯信息
            $newsId = input('id') ? input('id') : '';
            if (!empty($newsId)) {
                $newsInfo = $this->ArticleModel->getArtDet($newsId);
            } else {
                $newsInfo = '';
            }

            //获取该发布人的所有上线展会
            $exhibition = $this->ExhibitionModel->getExhBySponsor($userinfo['fk_role_id'], 1, '');

            //去重处理
            if (!empty($exhibition)) {
                foreach ($exhibition as $value) {
                    $value['begin_time'] = date("Y-m-d", $value['begin_time']);
                    $value['end_time'] = date("Y-m-d", $value['end_time']);
                    $data[$value['id']] = $value;
                }
                foreach ($data as $item) {
                    $exh[] = $item;
                }
            }else{
                $exh = '';
            }

            $this->assign('newsInfo',$newsInfo);
            $this->assign('exhibition',$exh);
            return $this->fetch();

        }
    }

    /**
     * 回收展会
     */
    public function article_switch(){
        $id = request()->param('id');
        $status = request()->param('status');
        $this->ArticleModel->switchArticle($id,$status);
        echo 1;
    }

    /**
     * 回收服务
     */
    public function service_switch(){
        $id = request()->param('id');
        $status = request()->param('status');
        $this->ServiceModel->switchService($id,$status);
        echo 1;
    }

    /**
     * 删除展会
     */
    public function article_del(){
        $id = request()->param('id');
        $this->ArticleModel->delArticleOne($id);

        echo 1;
    }

    /**
     * 删除展会
     */
    public function service_del(){
        $id = request()->param('id');
        $this->ServiceModel->delServiceOne($id);

        echo 1;
    }

    /**
     * 资讯管理
     */
    public function article_manage(){
        // dump(input('post.'));die;
        $status = request()->param('status') ? request()->param('status') : 1;
        $type = request()->param('type') ? request()->param('type') : 0;
        $title = input('post.title') ? input('post.title') : '';
        //获取用户登录信息
        $userId = Cookie::get("userinfo")['id'];
        $userinfo = $this->MemberModel->getMemberInfo($userId);

        $newsInfo = $this->ArticleModel->getNewsByMemberId($userId, $status, $title, $type);

        //去重处理
        if (!empty($newsInfo)) {
            foreach ($newsInfo as $value) {
                $value['pubtime'] = date("Y-m-d", $value['pubtime']);
                if (!empty($value['fk_exhibition_id'])) {
                    $value['exhibition_title'] = $this->ExhibitionModel->getExhById($value['fk_exhibition_id'])['title'];
                } else {
                    $value['exhibition_title'] = '&nbsp;&nbsp;&nbsp;----';
                }
                $data[$value['id']] = $value;
            }
            foreach ($data as $item) {
                $news[] = $item;
            }
        }else{
            $news = '';
        }
        //获取该用户发布的的所有资讯
        $count = $this->ArticleModel->getCountByMemberId($userId);

        $this->assign('newsInfo',json_encode($news));
        $this->assign('type',$type);
        $this->assign('title',$title);
        $this->assign('status',$status);
        $this->assign('count',$count);
        return $this->fetch();
    }

    /**
     * 会刊列表
     */
    public function journal_list(){
        return $this->fetch();
    }

    /**
     * 会员资料管理
     */
    public function member_info(){
        return $this->fetch();
    }

    /**
     * 观展者资料管理
     */
    public function visitor_info(){
        return $this->fetch();
    }

    /**
     * 观展者资料管理-编辑
     */
    public function visitor_edit(){
        return $this->fetch();
    }

    /**
     * 服务商发布服务
     */
    public function service_pub(){
        //获取用户登录信息
        $userId = Cookie::get("userinfo")['id'];
        $userinfo = $this->MemberModel->getMemberInfo($userId);
        if (request()->post()){

            $data['name'] = request()->param('name');
            $data['thumb'] = request()->param('thumb');
            $data['ser_nature'] = request()->param('ser_nature/a');
            $data['ser_value'] = request()->param('ser_value/a');
            $data['price'] = request()->param('price');
            $data['contact_person'] = request()->param('contact_person');
            $data['contact_phone'] = request()->param('contact_phone');
            $data['keywords'] = request()->param('keywords');
            $data['address'] = request()->param('address');
            $data['service_info'] = request()->param('service_info');

            //直辖市处理
            $city = request()->param('city');
            $district = request()->param('district');
            if ($district == '') {
                $data['fk_region_id'] = $city;
            } else {
                $data['fk_region_id'] = $district;
            }

            
            //处理属性、属性值
            if (!empty($data['ser_nature'])){
                for ($i=0; $i < count($data['ser_nature']); $i++) { 
                    $data['nature'][$i] = array($data['ser_nature'][$i], $data['ser_value'][$i]);
                }
                $data['nature'] = json_encode($data['nature']);
            }
            unset($data['ser_nature']);
            unset($data['ser_value']);
            
            //入服务主表
            $data['fk_role_service_id'] = $userinfo['fk_role_id'];
            $data['ctime'] = time();
            $data['status'] = 4;
            $service_id = $this->ServiceModel->postServer($data);
            if ($service_id){
                //服务轮播图入表
                $img = request()->param('imgurl');
                if ($img !=''){
                    if (is_array(explode(',',$img ))){
                        foreach(explode(',',$img ) as $value){
                            $imgdata['fk_service_id'] = $service_id;
                            $imgdata['imgurl'] = $value;

                            $re = $this->ServiceModel->postServerImg($imgdata);
                            if (!$re){
                                 return json_encode(array(0,'添加服务失败'));
                            }
                        }
                    }else{
                        $imgdata['fk_service_id'] = $service_id;
                        $imgdata['imgurl'] = $img;

                        $re = $this->ServiceModel->postServerImg($imgdata);
                        if (!$re){
                            return json_encode(array(0,'添加服务失败'));
                        }
                    }
                }
                return json_encode(array(1,'添加服务成功'));

            }else{
                return json_encode(array(0,'添加服务失败'));
            }

        }else{
            //获取地区省份信息
            $province = $this->RegionModel->getProvinceAll();

            $this->assign('province',$province);
            return $this->fetch();
        }
    }

    /**
     * 服务商发布服务
     */
    public function service_manage(){
        $status = request()->param('status') ? request()->param('status') : 1;
        $name = input('post.name') ? input('post.name') : '';
        // 获取用户登陆信息
        $userId = Cookie::get("userinfo")['id'];
        $userinfo = $this->MemberModel->getMemberInfo($userId);

        $serviceList = $this->ServiceModel->getServiceById($userinfo['fk_role_id'], $status, $name);

        //去重处理
        if (!empty($serviceList)) {
            foreach ($serviceList as $value) {
                $value['ctime'] = date("Y-m-d", $value['ctime']);
                $value['utime'] = date("Y-m-d", $value['utime']);

                $district = $this->RegionModel->getCode($value['fk_region_id']);
                $city = $this->RegionModel->getCode($district['parent_id']);
                if ($city['parent_id'] == 1) {
                    $value['region'] = $city['province'];
                } else {
                    $region = $this->RegionModel->getCode($city['parent_id']);
                    $value['region'] = $region['province'].','.$city['city'];
                }

                if (empty($value['price'])) {
                    $value['price'] = '价格面仪';
                }
                

                $data[$value['id']] = $value;
            }
            foreach ($data as $item) {
                $ser[] = $item;
            }
        }else{
            $ser = '';
        }

        //获取该用户发布的的所有服务
        $count = $this->ServiceModel->getCountByRoleId($userinfo['fk_role_id']);
        // dump($ser);die;
        $this->assign('status',$status);
        $this->assign('serviceInfo',json_encode($ser));
        $this->assign('name',$name);
        $this->assign('count',$count);
        return $this->fetch();
    }

    
    /**
     * 修改服务
     */
    public function service_edit(){
        //获取用户登录信息
        $userId = Cookie::get("userinfo")['id'];
        $userinfo = $this->MemberModel->getMemberInfo($userId);
        if (request()->post()) {
            $data['id'] = request()->param('id');
            $data['name'] = request()->param('name');
            $data['thumb'] = request()->param('thumb');
            $data['ser_nature'] = request()->param('ser_nature/a');
            $data['ser_value'] = request()->param('ser_value/a');
            $data['price'] = request()->param('price');
            $data['contact_person'] = request()->param('contact_person');
            $data['contact_phone'] = request()->param('contact_phone');
            $data['keywords'] = request()->param('keywords');
            $data['address'] = request()->param('address');
            $data['service_info'] = request()->param('service_info');

            //直辖市处理
            $city = request()->param('city');
            $district = request()->param('district');
            if ($district == '') {
                $data['fk_region_id'] = $city;
            } else {
                $data['fk_region_id'] = $district;
            }

            
            //处理属性、属性值
            if (!empty($data['ser_nature'])){
                for ($i=0; $i < count($data['ser_nature']); $i++) { 
                    $data['nature'][$i] = array($data['ser_nature'][$i], $data['ser_value'][$i]);
                }
                $data['nature'] = json_encode($data['nature']);
            }
            unset($data['ser_nature']);
            unset($data['ser_value']);
            
            //入服务主表
            $data['fk_role_service_id'] = $userinfo['fk_role_id'];
            $data['ctime'] = time();
            $data['status'] = 4;
            $service_id = $this->ServiceModel->updateService($data['id'], $data);
            if ($service_id){
                //服务轮播图入表
                $img = request()->param('imgurl');

                if ($img !=''){
                    if (is_array(explode(',',$img ))){
                        foreach(explode(',',$img ) as $value){
                            $imgdata['fk_service_id'] = $service_id;
                            $imgdata['imgurl'] = $value;
                            $re = $this->ServiceModel->postServerImg($imgdata);
                            if (!$re){
                                 return json_encode(array(0,'添加服务失败'));
                            }
                        }
                    }else{
                        $imgdata['fk_service_id'] = $service_id;
                        $imgdata['imgurl'] = $img;

                        $re = $this->ServiceModel->postServerImg($imgdata);
                        if (!$re){
                            return json_encode(array(0,'修改服务失败'));
                        }
                    }
                }
                return json_encode(array(1,'修改服务成功'));

            }else{
                return json_encode(array(0,'修改服务失败'));
            }

        } else {
            //获取用户登录信息
            $userId = Cookie::get("userinfo")['id'];
            $userinfo = $this->MemberModel->getMemberInfo($userId);

             // 根据资讯ID获取资讯信息
            $serviceId = input('id') ? input('id') : '';
            if (!empty($serviceId)) {
                $service = $this->ServiceModel->getSerDetail($serviceId);
            } else {
                $service = '';
            }
            $img = $this->ServiceModel->getImg($serviceId);

            //获取地区省份信息
            $province = $this->RegionModel->getProvinceAll();

            //获取县区的父id
            $cityid = $this->RegionModel->getArea($service['fk_region_id']);
            //获取县区
            $district = $this->RegionModel->getDistrict($cityid['parent_id']);
            //获取城市的父id
            $provinceid = $this->RegionModel->getArea($cityid['parent_id']);

            //如果省级id为一则是直辖市，则取id不取parent_id
            if ($provinceid['parent_id'] == 1){
                $proid = $cityid['parent_id'];
            }else{
                $proid = $provinceid['parent_id'];
            }
            //获取城市
            $city = $this->RegionModel->getCity($proid);

            $this->assign('provinceid',$proid);
            $this->assign('province',$province);
            //如果是直辖市的联动处理
            if($city[0]['city'] == ''){
                foreach ($district as $key=>$value){
                    $districts[$key]['id'] = $value['id'];
                    $districts[$key]['city'] = $value['district'];
                }
                $this->assign('city',$districts);
                $this->assign('cityid',$service['fk_region_id']);
                $this->assign('district','');
            }else{
                $this->assign('city',$city);
                $this->assign('cityid',$cityid['parent_id']);
                $this->assign('district',$district);
            }
            

           
            $service['nature'] = json_decode($service['nature']);
            // dump($service);die;
            $this->assign('service',$service);
            $this->assign('img',$img);
            $this->assign('province',$province);
            return $this->fetch();

        }
    }

    /**
     * 消息通知
     */
    public function message_manage(){
        return $this->fetch();
    }

    /**
     * 公告
     */
    public function message_notice(){
        return $this->fetch();
    }


    /**
     * 申请广告位
     */
    public function advert_apply(){
        $SystemModel = new SystemModel;
        //获取用户登录信息
        $userId = Cookie::get("userinfo")['id'];
        $user = $this->MemberModel->getMemberInfo($userId);
        $data['fk_exhibition_id'] = request()->param('id');
        $data['fk_member_id'] = $user['id'];
        $data['role_type'] = $user['role'];
        $data['fk_role_id'] = $user['fk_role_id'];
        $data['ctime'] = time();
        $SystemModel->postAdvertApply($data);
    }

    /**
     * ajax获取二级行业
     */
    public function getTradeSon(){
        $id = request()->param('id');
        $tradeson = $this->CategoryModel->getTradeSecond($id);
        if (empty($tradeson)){
            echo false;
        }else{
            echo json_encode($tradeson);
        }
    }

    /**
     * ajax异步删除某一图片
     */
    public function delImg(){
        $src = request()->param('src');

        Db::name('exhibition_img')->where('imgurl',$src)->delete();
        
        Db::name('exhibition_floorplan')->where('floorplan_url',$src)->delete();
    }
}