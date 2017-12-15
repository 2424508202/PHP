<?php
namespace app\www\controller;

use app\core\model\RegionModel;
use app\core\model\SponsorModel;
use app\core\model\ExhibitionModel;
use app\core\model\ArticleModel;
use app\core\model\ShardModel;
use app\core\model\CategoryModel;
use app\core\model\ServiceModel;
use think\Request;

/**
 * 主办方控制器
 * @author:yagnzhiwei
 */
class Sponsor extends Login{
    //构造函数初始化
    public function __construct(Request $request){
        parent::__construct($request);
        //添加展商信息配置文件
        $info = require "../application/admin/extra/exhibitor.info.conf.php";
        $this->info = $info;
        //加载推荐位配置文件
        $shardconfig = require "../application/www/extra/shard.info.conf.php" ;
        $this->shardconfig = $shardconfig;
        //实例化城市模型
        $this->RegionModel = new RegionModel;
        //实例化主办方模型
        $this->SponsorModel = new SponsorModel;
        //实例化展会模型
        $this->ExhibitionModel = new ExhibitionModel;
        //实例化新闻资讯模型
        $this->ArticleModel = new ArticleModel;
        //实例化推荐位模型
        $this->ShardModel = new ShardModel;
        //实例化分类管理模型
        $this->CategoryModel = new CategoryModel;
        //实例化服务模型
        $this->ServiceModel = new ServiceModel;
        //当前操作位置
        $this->assign('action','zhuban');
    }
    /**
     * 空操作
     * @author cuibo <79710344@qq.com>
     */
    public function _empty(){
        //解析到前台主办方列表页
        return $this->sponsor();
    }

    /*
     * 主办方列表页
     */
    public function sponsor(){
        $shardconfig = $this->shardconfig;
        //获取所有省份
        $provincelist=$this->RegionModel->getProvinceAll();
        //接收地区id，显示二级目录
        $id=$this->request->param('region');
        if($id==0){
            //获取地区全部信息
            $region=$this->RegionModel->getCode($id=1);
        }else{
            $region=$this->RegionModel->getCode($id);
        }
        //判断接收id级别
        $temp=substr($region['code'],2,4);
        if($temp=='0000'){
            $proid=$id;
            $citys=$this->RegionModel->getCity($id);
        }else{
            $proid=$region['parent_id'];
            $citys=$this->RegionModel->getCity($region['parent_id']);
        }
        
        //获取地区id，显示主办方列表
        $districtsids=$this->getSpoByCityId($id);
        if(!empty($districtsids)){
            if(is_array($districtsids)){
                //按照地区id数组获取主办方列表
                $sponsorlists=$this->SponsorModel->getSpoByArr($districtsids,$id);
            }else{
                $sponsorlists=$this->SponsorModel->getSponsorById($districtsids);
            }
        }else{
            //获取所有主办方
            $sponsorlists=$this->SponsorModel->getSponsorById($id='');
        }
        //获取当前页     
        $page=request()->param('page');
        //分页显示
        $pageshow=$sponsorlists['list']->render();
        //将主办方对象转换为数组
        $sponsorlist=json_decode( json_encode( $sponsorlists ), true );
        //将主办方信息赋值给三维数组$sponsors
        $sponsornumber='';
        //获取主办方数量
        $sponsornumber=$sponsorlist['num'];
        if($sponsornumber-10*$page>0){
            $number=10;
        }else{
            $number=$sponsornumber-10*$page+10;
        }        
        //将主办方展会数量和所在地区添加进数组
        for ($i=0; $i < count($sponsorlist['list']['data']); $i++) {
            $exhnumber=$this->ExhibitionModel->getExhNum($sponsorlist['list']['data'][$i]['id']);
            $district=$this->getDisById($sponsorlist['list']['data'][$i]['id']);
            $sponsorlist['list']['data'][$i]['contacts_mobile'] = $this->phone_num($sponsorlist['list']['data'][$i]['contacts_mobile']);
            $sponsorlist['list']['data'][$i]['num'] = $exhnumber['num'];
            if(!empty($exhnumber['picture'])){
                $sponsorlist['list']['data'][$i]['picture'] = $exhnumber['picture'];
            }
            $sponsorlist['list']['data'][$i]['area'] = $district;
        }
        //热门展会推荐位
        $exhhotspo = $shardconfig['index']['exhhotspo'];
        foreach ($exhhotspo as $value){
            $exhhotspolist = $this->ShardModel->getExhByShaId($value,5);
        }
        //热门展会服务推荐位
        $serhotspo = $shardconfig['index']['serhotspo'];
        foreach ($serhotspo as $value){
            $serhotspolist = $this->ShardModel->getServer($value,5);
            foreach ($serhotspolist['list'] as $key=>$value) {
                $industry=$this->ServiceModel->getScope($value['fk_role_id']);
                $serhotspolist['list'][$key]['scope']=$industry;
            }
        }
        //热门资讯推荐位
        $newshotspo = $shardconfig['index']['newshotspo'];
        foreach ($newshotspo as $value){
            $newshotspolist = $this->ShardModel->getNewsRec($value,5,1);
        }
        //dump($newshotspolist);die;
        $this->assign('id',$proid);
        $this->assign('page',$page);
        $this->assign('number',$number);
        $this->assign('sponsornumber',$sponsornumber);
        $this->assign('proid',$id);
        $this->assign('city',$citys);
        $this->assign('provincelist',$provincelist);
        $this->assign('region',$region);
        $this->assign('pageshow',$pageshow);
        $this->assign('sponsorlist',$sponsorlist['list']['data']);
        $this->assign('sponsornumber',$sponsornumber);
        $this->assign('exhhotspolist',$exhhotspolist);
        $this->assign('serhotspolist',$serhotspolist);
        $this->assign('newshotspolist',$newshotspolist);        
        //模板输出
        return $this->fetch();
    }
    //获取主办方所属地区
    private function getDisById($id){
        $disid=$this->SponsorModel->getDistrictById($id);
        $district=$this->RegionModel->getArea($disid['fk_region_id']);
        $city=$this->RegionModel->getArea($district['parent_id']);
        //判断是否为直辖市
        if($city['province']==''){
            $province=$this->RegionModel->getArea($city['parent_id']);
            $district['city']=$city['city'];
            $district['province']=$province['province'];
        }else{
            $district['city']=$district['district'];
            $district['province']=$city['province'];
        }
        return $district;
    }
    
    //通过省份或城市id来获取地区id
    private function getSpoByCityId($id){
        if($id!=1){
            //获取此地区的下属地区
            $region=$this->RegionModel->getCity($id);
            //直接获取直辖市下的地区id
            if($region==null){
                return $id;
            }else{
                foreach ($region as $value) {
                    $district[]=$this->RegionModel->getCity($value['id']);
                }
                //通过直辖市或者普通省份下的市id获取地区id
                foreach ($district as $value) {
                    if($value==null){
                        foreach ($region as $value) {
                            $districtid[]=$value['id'];
                        }
                        return $districtid;
                    }else{
                        //通过普通省份id获取地区id
                        foreach ($value as $val) {
                            $districtid[]=$val['id'];
                        }
                    }
                }
                return $districtid;
            }
        }
    }
    //将电话15385748484改为153****8484
    private function Phone_num($num){
        $num=substr($num,0,3).'****'.substr($num,7,4);
        return $num;
    }

    /**
     * 主办方主页
     */
    public function sponsor_page(){
        $id = request()->param('sponsor');
        //右侧主办方信息详情
        $sponsorlist=$this->getSponRight($id);
        //获取最新的六条展会
        $exhSix=$this->ExhibitionModel->getExhMes($id,6 );
        //获取主办方所属行业
        foreach ($exhSix as $key=>$value) {
            $industryIds = array();
            $industryName = array();
            $industry=$this->getIndustry($value['id']);
            $exhSix[$key]['industry']=$industry;
            $res = $this->ExhibitionModel->getTradeRelation($value['id']);
            foreach ($res as $val) {
                $industryIds[] = $val['fk_industry_id'];
            }
            $industryId = join(',', $industryIds);
            $res = $this->ExhibitionModel->getTradeRelationSon($industryId);
            $num = 1;
            $industryName = '';
            for ($i=0; $i < count($res); $i++) {
                if ($num > 3) {
                    $industryName .= ' ...';
                    break;
                }
                if ($num > 1) {
                    $industryName .= ' | ';
                }
                $industryName .= $res[$i]['name'];
                $num++;
            }
            $exhSix[$key]['industry_name'] = $industryName;
        }
        //获取最新的三条企业新闻
        $newsThree=$this->ArticleModel->getNews($id);
        //获取主办方资质荣誉
        $spohonor=$this->SponsorModel->getHonPic($id,1);
        $this->assign('exhSix',$exhSix);
        $this->assign('newsThree',$newsThree);
        $this->assign('sponsor',$sponsorlist);
        $this->assign('spohonor',$spohonor);
        //模板输出
        return $this->fetch();
    }
    //获取所有的主办方信息
    private function getSponRight($id){
        $info=$this->info;
        //获取主办方信息
        $sponsorlist=$this->SponsorModel->getSpoById($id);
        //根据配置文件获取主办方企业性质和规模
        $sponsorlist['nature']=$info['exhibitor']['nature'][$sponsorlist['nature']];
        $sponsorlist['scope']=$info['exhibitor']['scope'][$sponsorlist['scope']];
        //获取主办方所在省份和城市
        $distrctid=$this->SponsorModel->getProBySpoId($id);
        $district=$this->RegionModel->getArea($distrctid['fk_region_id']);
        $city=$this->RegionModel->getArea($district['parent_id']);
        //判断该地区是否为直辖市
        if($city['province']==''){
            $province=$this->RegionModel->getArea($city['parent_id']);
        }else{
            $province['province']=$city['province'];
            $city['city']=$district['district'];
        }
        $sponsorlist['province']=$province;
        $sponsorlist['city']=$city;
        return $sponsorlist;
    }
    //获取展会所属行业
    private function getIndustry($id){
        $tradeid = $this->ExhibitionModel->getTradeRelation($id);
            if (!empty($tradeid)){
                //如果第一个为一级行业，则取该值
                $industry = $this->CategoryModel->getTradeCategoryOne($tradeid[0]['fk_industry_id']);
                if ($industry['parent_id'] != 0){
                    //否则获取该子行业的一级行业
                    $industry = $this->CategoryModel->getTradeCategoryOne($industry['parent_id']);
                }
                return $industry;
            }
    }

    /**
     * 主办方展会
     */
    public function sponsor_exhibition(){
        //获取主办方公司地区id
        $id = request()->param('sponsor');
        //右侧主办方详细信息
        $sponsorlist=$this->getSponRight($id);
        //获取当前主办方所有展会
        $exhibitionlist1=$this->ExhibitionModel->getExhAllFront($id);
        //获取当前页码和本页数据量
        $page=request()->param('page');
        if($exhibitionlist1['num']-9*$page>0){
            $number=9;
        }else{
            $number=$exhibitionlist1['num']-9*$page+9;
        }
        //分页显示
        $pageshow=$exhibitionlist1['list']->render();
        //将查询出的对象转换为数组
        $exhibitionlist=array();
        foreach ($exhibitionlist1['list'] as $key => $value) {
            $exhibitionlist[$key]=$value;
        }
        //dump($exhibitionlist);exit;
        //获取详细行业
        foreach ($exhibitionlist as $key=>$value) {
            $industryIds = array();
            $industryName = array();
            $industry=$this->getIndustry($value['id']);
            $exhibitionlist[$key]['industry']=$industry;
            $res = $this->ExhibitionModel->getTradeRelation($value['id']);
            foreach ($res as $val) {
                $industryIds[] = $val['fk_industry_id'];
            }
            $industryId = join(',', $industryIds);  
            $res = $this->ExhibitionModel->getTradeRelationSon($industryId);
            $num = 1;
            $industryName = '';
            for ($i=0; $i < count($res); $i++) {
                if ($num > 3) {
                    $industryName .= ' ...';
                    break;
                }
                if ($num > 1) {
                    $industryName .= ' | ';
                }
                $industryName .= $res[$i]['name'];
                $num++;
            }
            $exhibitionlist[$key]['industry_name'] = $industryName;
        }
        
        //dump($exhibitionlist);exit();
        $this->assign('pageshow',$pageshow);
        $this->assign('page',$page);
        $this->assign('number',$number);
        $this->assign('exhibitionlist',$exhibitionlist);
        $this->assign('exhnum',$exhibitionlist1['num']);
        $this->assign('sponsor',$sponsorlist);
        //模板输出
        return $this->fetch();
    }

    /**
     * 主办方新闻
     */
    public function sponsor_news(){
        $id = request()->param('sponsor');
        $sponsorlist=$this->getSponRight($id);
        //根据id获取主办方新闻
        $newslist=$this->ArticleModel->getNewsAll($id);
        //获取当前页码和本页数据量
        $page=request()->param('page');
        $pageshow=$newslist['list']->render();
        if($newslist['num']-6*$page>0){
            $number=6;
        }else{
            $number=$newslist['num']-6*$page+6;
        }
        $this->assign('page',$page);
        $this->assign('pageshow',$pageshow);
        $this->assign('number',$number);
        $this->assign('newslist',$newslist['list']);
        $this->assign('newsnum',$newslist['num']);
        $this->assign('sponsor',$sponsorlist);
        //模板输出
        return $this->fetch();
    }
    /**
     * 新闻详情
     */
    public function sponsor_news_details(){
        $id = request()->param('newsid');
        $sponid = request()->param('sponsor');
        //获取右侧主办方信息
        $sponsorlist=$this->getSponRight($sponid);
        //获取企业新闻的详细信息
        $newsdetails=$this->ArticleModel->getNewsDetails($id);
        //dump($newsdetails);die;
        $this->assign('news',$newsdetails);
        $this->assign('sponsor',$sponsorlist);
        //模板输出
        return $this->fetch();
    }
}