<?php
namespace app\www\controller;

use app\core\model\ArticleModel;
use app\core\model\SponsorModel;
use app\core\model\CompanyModel;
use app\core\model\ExhibitionModel;
use app\core\model\RegionModel;
use app\core\model\ServiceModel;
use app\core\model\SystemModel;
use app\core\model\CategoryModel;
use app\core\model\ShardModel;
use think\Cookie;
use think\Request;
use think\Db;

/**展会服务控制器
 * Class ExhibitionServer
 * @package app\www\controller
 */

class ExhibitionServer extends Login{


    public function __construct(Request $request){
        parent::__construct($request);
        $info = require "../application/admin/extra/exhibitor.info.conf.php";
        $this->info = $info;        
        //加载配置文件
        $config = require "../application/www/extra/www.info.conf.php";
        $this->config = $config;
        //加载推荐位配置文件
        $shardconfig = require "../application/www/extra/shard.info.conf.php" ;
        $this->shardconfig = $shardconfig;
        //实例化分类模型
        $this->CategoryModel = new CategoryModel;
        //实例化主办方模型
        $this->SponsorModel = new SponsorModel;
        //实例化推荐位模型
        $this->ShardModel = new ShardModel;
        //实例化系统模型
        $this->SystemModel = new SystemModel;
        //实例化展会模型
        $this->ExhibitionModel = new ExhibitionModel;
        //实例化城市模型
        $this->RegionModel = new RegionModel;
        //实例化服务商模型
        $this->ServiceModel = new ServiceModel;
        //实例化企业模型
        $this->CompanyModel = new CompanyModel;
        //实例化资讯模型
        $this->ArticleModel = new ArticleModel;
        //页面标题
        $this->assign('title','展会网-展会服务');
        //传输当前操作位置
        $this->assign('action','exhibition_server');
    }

    /**
     * 展会服务首页加载
     */
    public function index(){

        //加载推荐位配置文件
        $shardconfig = $this->shardconfig;
        //左侧无限级分类
        $config = $this->config;
        $industry = $config['server'];
        //无限极菜单广告推荐位
        $menuadv=$shardconfig['index']['menuadv'];
        foreach ($menuadv as $value) {
            $menuadvlist=$this->ShardModel->getMessage($value,3);
        }        
        //轮播图推荐位
        $banpic=$shardconfig['index']['banpic'];
        foreach ($banpic as $value) {
            $banpiclist=$this->ShardModel->getBanPic($value,3);
        }
        //实力展会服务商推荐位
        $exhserstr=$shardconfig['index']['exhserstr'];
        foreach ($exhserstr as $key=>$value) {
            $exhserstrlist[]=$this->getShardSer($key,'exhserstr',8);
        }
        //实力展会服务下广告位
        $advunder=$shardconfig['index']['advunder'];
        foreach ($advunder as $value) {
            $advunder=$this->ShardModel->getMessage($value,1);
        }
        //服务商快搜行业
        $tradeFirst = $this->CategoryModel->getServiceFirst();
        foreach ($tradeFirst as $key => $value) {
            //获取每个行业服务商个数
            $tradeFirst[$key]['num']=$this->CategoryModel->getSerNum($value['id']);
        }
        //明星服务商-设计搭建
        $sersjdj=$shardconfig['index']['sersjdj'];
        foreach ($sersjdj as $value) {
            $sersjdjlist=$this->ShardModel->getServer($value,4);
        }
        //明星服务商-展览工厂
        $serzlgc=$shardconfig['index']['serzlgc'];
        foreach ($serzlgc as $value) {
            $serzlgclist=$this->ShardModel->getServer($value,4);
        }
        //明星服务商广告位-1
        $advunder1=$shardconfig['index']['advunder1'];
        foreach ($advunder1 as $value) {
            $advunder1list=$this->ShardModel->getMessage($value,1);
        }
        //明星服务商-展具租赁
        $serzjzl=$shardconfig['index']['serzjzl'];
        foreach ($serzjzl as $value) {
            $serzjzllist=$this->ShardModel->getServer($value,4);
        }
        //明星服务商-礼仪模特
        $serlimt=$shardconfig['index']['serlimt'];
        foreach ($serlimt as $value) {
            $serlimtlist=$this->ShardModel->getServer($value,4);
        }
        //明星服务商-宾馆酒店
        $serbgjd=$shardconfig['index']['serbgjd'];
        foreach ($serbgjd as $value) {
            $serbgjdlist=$this->ShardModel->getServer($value,4);
        }
        //明星服务商广告位
        $advunder2=$shardconfig['index']['advunder2'];
        foreach ($advunder2 as $value) {
            $advunder2list=$this->ShardModel->getMessage($value,1);
        }

        $this->assign('menuadvlist',$menuadvlist);                                 
        $this->assign('industry',$industry);
        $this->assign('banpiclist',$banpiclist);
        $this->assign('exhserstrlist',$exhserstrlist);
        $this->assign('advunder',$advunder);
        $this->assign('tradeFirst',$tradeFirst);
        $this->assign('sersjdjlist',$sersjdjlist);
        $this->assign('serzlgclist',$serzlgclist);
        $this->assign('advunder1list',$advunder1list);
        $this->assign('serzjzllist',$serzjzllist);
        $this->assign('serlimtlist',$serlimtlist);
        $this->assign('serbgjdlist',$serbgjdlist);
        $this->assign('advunder2list',$advunder2list);

        return $this->fetch();
    }
    //ajax获取无限极分类菜单信息
    public function getServiceData(){

        $ids= request()->param('trades');
        $ids = explode(',',$ids);
        //获取所有分类及其子分类
        foreach ($ids as $id) {
            $trade[] = $this->CategoryModel->getServiceAll($id);
        }
        //拼接html
        $html = '';
        foreach ($trade as $value){
            $html .= "<div>";
            $html .= "<h3>".$value['name']."服务</h3>";
            $html .= "<ul>";
            foreach ($value['son'] as $val){
                $html.="<li><a href='/fuwu/".$val['id']."_0_1.html'>".$val['name']."</a></li>";
            }
            $html .= "</ul>";
            $html .= "</div>";
        }


        return $html;
    }    

    /**
     * 展会服务信息列表页加载
     */
    public function lists(){
        $shardconfig = $this->shardconfig;
        //获取行业id
        $tradeid=request()->param('serviceid');
        // 一级行业分类
        $tradeFirst = $this->CategoryModel->getServiceFirst();
        //获取二级行业列表
        if($tradeid!=0){
            $trade=$this->CategoryModel->getServiceCategoryOne($tradeid);
            if($trade['parent_id']==0){
                $tradelist=$this->CategoryModel->getServiceSecond($trade['id']);
            }else{
                $tradelist=$this->CategoryModel->getServiceSecond($trade['parent_id']);
            }
        }else{
            $tradelist='';
        }     
        //获取页码
        $page=$this->request->param('page');
        //获取所有省份
        $provincelist=$this->RegionModel->getProvinceAll();
        //接收地区id，显示二级目录
        $id=$this->request->param('region');       
        if($id!=0){
            //获取地区全部信息
            $region=$this->RegionModel->getCode($id);
            //判断接收id级别
            $temp=substr($region['code'],2,4);
            if($temp=='0000'){
                $proid=$id;
                $citys=$this->RegionModel->getCity($id);
            }else{
                $proid=$region['parent_id'];
                $citys=$this->RegionModel->getCity($region['parent_id']);
            }
        }else{
            $proid=0;
            $citys='';
            $region=0;
        }
        //获取地区id，显示服务商列表
        $districtsids=$this->getSpoByCityId($id);
        if(is_array($districtsids)){
            $serlist=$this->ServiceModel->getSerByIdArr($tradeid,$districtsids,$id);
        }else{
            //分页获取服务商信息
            $serlist=$this->ServiceModel->getServicer($tradeid,$id);            
        }
        //分页显示
        $pageshow=$serlist['list']->render();
        if($serlist['num']-7*$page>0){
            $number=7;
        }else{
            $number=$serlist['num']-7*$page+7;
        }         
        //将服务商转化为数组形式并添加服务范围信息
        $serlist=json_decode(json_encode($serlist),true);
        foreach ($serlist['list']['data'] as $key => $value) {
            //添加服务范围
            $serlist['list']['data'][$key]['scope']=$this->ServiceModel->getScope($value['fk_role_id']);
            //添加服务图片
            $serlist['list']['data'][$key]['pic']=$this->ServiceModel->getSerPic($value['fk_role_id']);
            $serlist['list']['data'][$key]['contacts_mobile']=$this->phoneChange($value['contacts_mobile']);
            //添加服务地区
            $serlist['list']['data'][$key]['region']=$this->getSerReg($value['fk_role_id']);
        }
        //展会服务排行榜推荐位
        $exhserten=$shardconfig['index']['exhserten'];
        foreach ($exhserten as $value) {
            $exhsertenlist=$this->ShardModel->getServer($value,10);
        }
        foreach ($exhsertenlist['list'] as $key => $value) {
            $exhsertenlist['list'][$key]['scope']=$this->ServiceModel->getScope($value['fk_role_id']);
        }
        $this->assign('tradeid',$tradeid);
        $this->assign('tradelist',$tradelist);        
        $this->assign('tradeFirst',$tradeFirst);
        $this->assign('page',$page);
        $this->assign('proid',$proid);
        $this->assign('region',$region);
        $this->assign('id',$id);        
        $this->assign('provincelist',$provincelist);
        $this->assign('citys',$citys);
        $this->assign('pageshow',$pageshow);
        $this->assign('number',$number);
        $this->assign('serlist',$serlist);
        $this->assign('exhsertenlist',$exhsertenlist['list']);

        return $this->fetch();
        
    }
    //通过省份或城市id来获取地区id
    private function getSpoByCityId($id){
        if($id!=0){
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
    //将电话格式改为132****3456
    private function phoneChange($number){
        $number=substr($number,0,3).'****'.substr($number,7,4);
        return $number;
    }
    //获取服务商的服务地区
    private function getSerReg($id){
        $region=array();
        $districtid=$this->ServiceModel->getRegById($id);
        $district=$this->RegionModel->getArea($districtid['fk_region_id']);
        $city=$this->RegionModel->getArea($district['parent_id']);
        if($city['province']!=''){
            $region['pro']=$city['province'];
            $region['city']=$district['district'];
        }else{
            $province=$this->RegionModel->getArea($city['parent_id']);
            $region['pro']=$province['province'];
            $region['city']=$city['city'];
        }
        return $region;
    }

    /**
     * 展会服务公司主页
     */
    public function company_index(){
        $id=$this->request->param('serid');
        //右侧服务商详细信息
        $service=$this->getSerDet($id);
        //获取八条服务信息
        $sereight=$this->ServiceModel->getSerById($id);
        //获取三条企业新闻
        $newsthree=$this->ArticleModel->getNews($id);
        //获取企业荣誉资质
        $serhonor=$this->SponsorModel->getHonPic($id,3);
        $this->assign('service',$service);
        $this->assign('sereight',$sereight);
        $this->assign('newsthree',$newsthree);
        $this->assign('serhonor',$serhonor);        

        return $this->fetch();
        
    }
    //获取所有服务商信息
    private function getSerDet($id){
        $info=$this->info;
        //获取服务商基本信息
        $service=$this->ServiceModel->getSerMes($id);
        //根据配置文件获取服务商性质和规模
        $service['nature']=$info['exhibitor']['nature'][$service['nature']];
        $service['scope']=$info['exhibitor']['scope'][$service['scope']];
        //获取服务商所在省份和城市
        $distrctid=$this->ServiceModel->getRegById($id);
        $district=$this->RegionModel->getArea($distrctid['fk_region_id']);
        $city=$this->RegionModel->getArea($district['parent_id']);
        //判断该地区是否为直辖市
        if($city['province']==''){
            $province=$this->RegionModel->getArea($city['parent_id']);
        }else{
            $province['province']=$city['province'];
            $city['city']=$district['district'];
        }
        $service['province']=$province;
        $service['city']=$city;
        return $service;        
    }     

    /**
     * 展会服务公司服务展示页
     */
    public function more(){
        $info=$this->info;
        $id=$this->request->param('serid');
        //右侧服务商详细信息
        $service=$this->getSerDet($id);        
        //分页获取12个服务
        $sertwelve=$this->ServiceModel->getSerTwe($id);
        $page=request()->param('page');
        if($sertwelve['num']-12*$page>0){
            $number=12;
        }else{
            $number=$sertwelve['num']-12*$page+12;
        }
        //分页显示
        $pageshow=$sertwelve['list']->render();
        $this->assign('service',$service);
        $this->assign('sertwelve',$sertwelve);
        $this->assign('page',$page);
        $this->assign('number',$number);
        $this->assign('pageshow',$pageshow);               

        return $this->fetch();
        
    }

    /**
     * 服务信息详情页加载
     */
    public function detail(){
        $serid=$this->request->param('serid');
        //右侧服务商详细信息
        $service=$this->getSerDet($serid);
        $id=$this->request->param('serviceid');
        //获取服务详细信息
        $serdetail=$this->ServiceModel->getSerDetail($id);
        $serdetail['nature']=json_decode($serdetail['nature']);
        // dump($serdetail);die;
        $serdetail['contact_phone']=$this->phoneChange($serdetail['contact_phone']);
        $this->assign('service',$service);
        $this->assign('serdetail',$serdetail);

        return $this->fetch();
        
    }

    /**
     * 服务商新闻列表页加载
     */
    public function company_news(){
        $info=$this->info;
        $id=$this->request->param('serid');
        //获取当前页码和本页数据量
        $page=request()->param('page');        
        //右侧服务商详细信息
        $service=$this->getSerDet($id);
        //分页获取六条企业新闻
        $newssix=$this->ArticleModel->getNewsAll($id);
        //分页显示
        $pageshow=$newssix['list']->render();
        if($newssix['num']-6*$page>0){
            $number=6;
        }else{
            $number=$newssix['num']-6*$page+6;
        }
        //dump($newssix);die;             
        $this->assign('page',$page);
        $this->assign('service',$service);
        $this->assign('newssix',$newssix);
        $this->assign('pageshow',$pageshow);
        $this->assign('number',$number);

        return $this->fetch();
        
    }

    /**
     * 服务商新闻详情页加载
     */
    public function news_detail(){
        $id = request()->param('newsid');
        $serid = request()->param('serid');
        //获取右侧主办方信息
        $service=$this->getSerDet($serid);
        //获取企业新闻的详细信息
        $newsdetails=$this->ArticleModel->getNewsDetails($id);
        $this->assign('news',$newsdetails);
        $this->assign('service',$service);        

        return $this->fetch();
        
    }

    public function exhibition_lists(){
        $userinfo = Cookie::get('userinfo');
        $status = request()->param('status');
        $title = request()->param('title');
        //获取该发布人的所有上线展会
        $exhibition = $this->ExhibitionModel->getExhBySponsor($userinfo['fk_role_id'],$status,$title);
        //var_dump($exhibition);exit();
         //去重处理
        foreach ($exhibition as $value){
            $value['begin_time'] = date("Y-m-d",$value['begin_time']);
            $value['end_time'] = date("Y-m-d",$value['end_time']);
            $data[$value['id']] = $value;
        }
        foreach ($data as $item){
            $exh[] = $item;
        }

        $this->assign('exhibition',json_encode($exh));
        return $this->fetch();
    }



    //展会服务商推荐位处理
    private function getShardSer($id,$data,$limit){
        $shardconfig = $this->shardconfig;
        $exhshard = $shardconfig['index'][$data];
        //获取推荐位的标签名称
        foreach ($exhshard as $val){
            $title[] = $this->ShardModel->getShardRemark($val);

        }
        //获取推荐位及推荐位数据
        $shard = $this->ShardModel->getShardandShardData($exhshard[$id],$limit);
        if (!empty($shard['sharddata'])){
            //获取推荐位推荐的服务商数据
            foreach ($shard['sharddata'] as $key=>$value){
                $ser[] = $this->ServiceModel->getMessage($value['fk_type_id']);
                $ser[$key]['scope']=$this->ServiceModel->getScope($value['fk_type_id']);
            }
            return array($title,$ser);
        }
    }


    //热门展会服务推荐位
    private function getShardExhServer($id,$limit,$type){
        $res = $this->ExhibitionModel->getExhSeverByTitle($id,$limit,$type);
        // var_dump($res);exit;
        
        return $res;
    }




}