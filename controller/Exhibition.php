<?php
namespace app\www\controller;

use app\core\model\ArticleModel;
use app\core\model\CompanyModel;
use app\core\model\ExhibitionModel;
use app\core\model\RegionModel;
use app\core\model\ServiceModel;
use app\core\model\SystemModel;
use app\core\model\MemberModel;
use app\core\model\CategoryModel;
use app\core\model\ShardModel;
use think\Request;

/**展会控制器
 * Class Exhibition
 * @package app\www\controller
 */

class Exhibition extends Login{

    public function __construct(Request $request){
        parent::__construct($request);
        //加载配置文件
        $info = require "../application/admin/extra/exhibitor.info.conf.php";
        $this->info = $info;
        //所属行业配置文件
        $config = require "../application/www/extra/www.info.conf.php";
        $this->config = $config;        
        //加载推荐位配置文件
        $shardconfig = require "../application/www/extra/shard.info.conf.php" ;
        $this->shardconfig = $shardconfig;
        //实例化分类模型
        $this->CategoryModel = new CategoryModel;
        //实例化推荐位模型
        $this->ShardModel = new ShardModel;
        //实例化系统模型
        $this->SystemModel = new SystemModel;
        //实例化会员模型
        $this->MemberModel = new MemberModel;
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
        $this->assign('title','展会网-展会信息');
        //传输当前操作位置
        $this->assign('action','exhibition');
    }

    /**
     * 展会信息首页加载
     */
    public function index(){

        //加载推荐位配置文件
        $shardconfig = $this->shardconfig;
        //左侧无限级分类
        $config = $this->config;
        $industry = $config['industry'];
        //左侧无限极分类广告位
        $advertise = $shardconfig['index']['advertisel'];
        foreach ($advertise as $value){
            $advertiselist = $this->ShardModel->getMessage($value,2);
        }
        //轮播图推荐位
        $pictureon = $shardconfig['index']['picon'];
        foreach ($pictureon as $value){
            $piconlist = $this->ShardModel->getPicOn($value,3);
        }
        //热门展会推荐位
        $exhhotlisttitle = $shardconfig['index']['exhhotlist'];
        foreach ($exhhotlisttitle as $key=>$id){
            $exhhotlist[] = $this->getShardExh($key, 'exhhotlist',4);
        }
        //展会排行榜推荐位
        $exhtoplisttitle = $shardconfig['index']['exhtoplist'];
        foreach ($exhtoplisttitle as $key=>$id){
            $exhtoplist[] = $this->getShardExh($key, 'exhtoplist',4);
        }
        //排行榜下的广告位推荐位
        $picunder = $shardconfig['index']['picunder'];
        foreach ($picunder as $value){
            $picunderlist = $this->ShardModel->getMessage($value,1);
        }
        //展会浮层-北京展会推荐位
        $exhbj = $shardconfig['index']['exhbj'];
        foreach ($exhbj as $key=>$id){
            $exhbjlist[] = $this->getShardExh($key, 'exhbj',8);
        }
        //展会浮层-上海展会推荐位
        $exhsh = $shardconfig['index']['exhsh'];
        foreach ($exhsh as $key=>$value){
            $exhshlist[] = $this->getShardExh($key,'exhsh',7);
        }
        //dump($exhshlist);die;
        //热门展会资讯推荐位
        $newshot = $shardconfig['index']['exhnewshot'];
        foreach ($newshot as $value){
            $newshotlist = $this->ShardModel->getNewsRec($value,5,3);
        }
        //大家都在搜推荐位
        $searchall = $shardconfig['index']['searchall'];
        foreach ($searchall as $value){
            $searchalllist = $this->ShardModel->getMessage($value,12);
        }
        //热门展会服务推荐位
        $serhot = $shardconfig['index']['serhot'];
        foreach ($serhot as $value){
            $serhotspolist = $this->ShardModel->getServer($value,5);
            foreach ($serhotspolist['list'] as $key=>$value) {
                //获取服务商服务范围
                $serhotspolist['list'][$key]['scope'] = $this->ServiceModel->getScope($value['fk_role_id']);
            }
        }
        $exhJan=$this->ExhibitionModel->getExhBet(1483200000,1485792000);
        $exhJuly=$this->ExhibitionModel->getExhBet(1498838400,1501430400);
        $this->assign('advertiselist',$advertiselist);
        $this->assign('piconlist',$piconlist);
        $this->assign('industry',$industry);
        $this->assign('exhhotlist',$exhhotlist);
        $this->assign('exhtoplist',$exhtoplist);
        $this->assign('picunderlist',$picunderlist);
        $this->assign('serhotspolist',$serhotspolist);
        $this->assign('exhbjlist',$exhbjlist);
        $this->assign('exhshlist',$exhshlist);        
        $this->assign('newshotlist',$newshotlist);
        $this->assign('searchalllist',$searchalllist);
        $this->assign('exhJan',$exhJan);
        $this->assign('exhJuly',$exhJuly);
        return $this->fetch();
    }
    //ajax接收月份获取展会排期  
    public function getTime(){
        $time=request()->param('time');
        $timen=request()->param('timen');
        if($timen==01){
            $end="2018-$timen-01";
        }else{
            $end="2017-$timen-01";
        }
        $begin="2017-$time-01";
        $begin=strtotime($begin);
        $end=strtotime($end)-1;
        $exhlist=$this->ExhibitionModel->getExhBet($begin,$end);
        //dump($exhlist);die;
        return json_encode($exhlist);
    }
    //ajax获取无限极分类菜单信息
    public function getTradeData(){

        $ids= request()->param('trades');
        $ids = explode(',',$ids);

        //获取所有分类及其子分类
        foreach ($ids as $id) {
            $trade[] = $this->CategoryModel->getTradeAll($id);
        }

        //拼接html
        $html = '';
        foreach ($trade as $value){
            $html .= "<div>";
            $html .="<h3>".$value['name']."展览会</h3>";
            $html .="<ul>";
            foreach ($value['son'] as $val){
                $html.="<li><a href=/zhxx/".$val['id']."_0_0_0_0_0_1_1.html>".$val['name']."</a></li>";
            }
            $html .="</ul>";
            $html .="</div>";
        }


        return $html;
    }    
    /**
     * 展会信息列表页加载
     */
    public function lists(){
        // 查询所有省份
        $allProvince = $this->RegionModel->getProvinceAll();
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
            $proid='';
            $citys=0;
            $region=0;
        }
        $tradeid=request()->param('industry');
        // 一级行业分类
        $tradeFirst = $this->CategoryModel->getTradeFirst();
        //获取二级行业列表
        if($tradeid!=0){
            $trade=$this->CategoryModel->getTradeCategoryOne($tradeid);
            if($trade['parent_id']==0){
                $tradelist=$this->CategoryModel->getTradeSecond($trade['id']);
            }else{
                $tradelist=$this->CategoryModel->getTradeSecond($trade['parent_id']);
                $tradeid=$trade['parent_id'];
            }
        }else{
            $tradelist='';
        }
        //获取所有参数
        $temps['industry']   = $this->request->param('industry');
        $temps['region']   = $this->request->param('region');
        $temps['regionlist']   =$this->getSpoByCityId($temps['region']);
        $temps['status']   = $this->request->param('status');
        $temps['area']   = $this->request->param('area');
        $temps['begin_time']   = $this->request->param('begin_time');
        $temps['end_time']   = $this->request->param('end_time');
        $temps['sort']   = $this->request->param('sort');
        $temps['page']   = $this->request->param('page');

        $temps['industry'] = $temps['industry'] ? $temps['industry'] : 0;
        $temps['region'] = $temps['region'] ? $temps['region'] : 0;//回头换成北京的
        $temps['status'] = $temps['status'] ? $temps['status'] : 0;
        $temps['area'] = $temps['area'] ? $temps['area'] : 0;
        $temps['sort'] = $temps['sort'] ? 1 : 0;
        $temps['page'] = $temps['page'] ? $temps['page'] : 1;
        
        if (($temps['begin_time']) == 0) {
            $beginTime = "开始时间";
        } else {
            $beginTime = $this->realTime($temps['begin_time']);
        }
        if (($temps['end_time']) == 0) {
            $endTime = "结束时间";
        } else {
            $endTime = $this->realTime($temps['end_time']);
        }
        //分页获取所有展会信息
        $allExhibition = $this->ExhibitionModel->getAllExhibition($temps,12,$id);
        // dump($allExhibition);die;
        $exhnumber=$allExhibition['num'];
        if($exhnumber-12*$temps['page']>0){
            $number=12;
        }else{
            $number=$exhnumber-12*$temps['page']+12;
        }         
        $page = $allExhibition['list']->render();
        $allExhibition = json_decode( json_encode( $allExhibition ), true );
        //时间、行业分类处理
        $industryIds = array();
        $industryName = array();
        foreach ($allExhibition['list']['data'] as $key=>$value){
            $res = $this->ExhibitionModel->getTradeRelation($value['id']);
            foreach ($res as $val) {
                $industry[]=$this->ExhibitionModel->getTradeDetail($val['fk_industry_id']);
            }
            foreach ($industry as $value) {
                $industryIds[] = $value['id'];
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
            $allExhibition['list']['data'][$key]['industry_name'] = $industryName;
        }
        // dump($allExhibition);die;
        //加载推荐位配置文件
        $shardconfig = $this->shardconfig;
        //热门展会服务推荐位
        $exhhotsever = $shardconfig['index']['exhhotsever'];
        $exhhotsever = $this->ExhibitionModel->getExhSeverByTitle($exhhotsever[0], 10, 3);
        $serviceName = '';
        $serviceRes = array();
        foreach ($exhhotsever as $value) {
            $res = $this->ExhibitionModel->getTradeServiceSon($value['service_id']);
            $num = 1;
            for ($i=0; $i < count($res); $i++) {
                if ($num > 3) {
                    $serviceName .= ' ...';
                    break;
                }
                if ($num > 1) {
                    $serviceName .= ' | ';
                }
                $serviceName .= $res[$i]['name'];
                $num++;
            }
            $value['service_name'] = $serviceName;
            
            $serviceRes[$value['id']] = $value;
        }
        $serviceData='';
        foreach ($serviceRes as $item){
            $serviceData[] = $item;
        }
        //热门展会资讯推荐位
        $exhhotinfotitle = $shardconfig['index']['exhhotinfo'];
        $exhhotinfo = $this->ShardModel->getNewsRec($exhhotinfotitle[0], 6, 3);
        $this->assign('tradeid',$tradeid);
        $this->assign('tradelist',$tradelist);
        $this->assign('allProvince',$allProvince);
        $this->assign('citys',$citys);
        $this->assign('region',$region);
        $this->assign('proid',$proid);
        $this->assign('tradeFirst',$tradeFirst);
        $this->assign('temps',$temps);
        $this->assign('exhibition',$allExhibition['list']['data']);
        $this->assign('page',$page);
        $this->assign('number',$number);
        $this->assign('count',$allExhibition['num']);
        $this->assign('exhhotsever',$serviceData);
        $this->assign('exhhotinfo',$exhhotinfo);
        $this->assign('beginTime',$beginTime);
        $this->assign('endTime',$endTime);        

        return $this->fetch();
        
    }
    //通过省份或城市id来获取地区id
    private function getSpoByCityId($id){
        if($id==0){
            $id=1;
        }else{
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

    /**
     * 展会信息详情页加载
     */
    public function detail(){
        $info=$this->info;
        //获取展会举办频率
        $cycle=$info['exhibition']['exhibition_cycle'];
        $shardconfig = $this->shardconfig;
        $id=request()->param('exhid');
        //获取展会详细信息
        $exhdetlist=$this->ExhibitionModel->getExhDet($id);
        //获取展位照片
        $exhpic=$this->ExhibitionModel->getExhPic($id);
        //获取所属行业
        $exhdetlist['industry']=$this->getIndustry($id);
        //获取展位图片
        $standpic=$this->ExhibitionModel->getStaPic($id);
        //获取展位情况
        $standdital=$this->ExhibitionModel->getStaDet($id);
        //获取展会往届回顾
        $exhbefore=$this->ExhibitionModel->getExhBef($id);
        foreach ($exhbefore as $key => $value) {
            $exhbefore[$key]['begin_time']=$this->realTime($exhbefore[$key]['begin_time']);
            $exhbefore[$key]['end_time']=$this->realTime($exhbefore[$key]['end_time']);
        }
        //获取主办方信息
        $spodetail=$this->ExhibitionModel->getSpoDet($id);
        //dump($spodetail);die;
        //展会资讯推荐位
        $exhnews=$shardconfig['index']['exhnews'];
        foreach ($exhnews as $value) {
            $exhnewslist=$this->ShardModel->getNewsRec($value,5,3);
        }
        //同类热门展会推荐位
        $exhnewshotsame=$shardconfig['index']['exhnewshotsame'];
        foreach ($exhnewshotsame as $value) {
            $exhnewshotsamelist=$this->ShardModel->getExhByShaId($value,5);
        }
        //展会服务推荐位
        $exhser = $shardconfig['index']['exhser'];
        foreach ($exhser as $value){
            $exhserlist = $this->ShardModel->getServer($value,15);
            foreach ($exhserlist['list'] as $key=>$value) {
                //获取服务商服务范围
                $exhserlist['list'][$key]['scope'] = $this->ServiceModel->getScope($value['fk_role_id']);
            }
        }        
        // dump($exhdetlist);die;
        $this->assign('cycle',$cycle); 
        $this->assign('exhpic',$exhpic);      
        $this->assign('exhdetlist',$exhdetlist);
        $this->assign('standpic',$standpic);
        $this->assign('standdital',$standdital);
        $this->assign('exhbefore',$exhbefore);
        $this->assign('spodetail',$spodetail);       
        $this->assign('exhnewslist',$exhnewslist);
        $this->assign('exhnewshotsamelist',$exhnewshotsamelist);
        $this->assign('exhserlist',$exhserlist);

        return $this->fetch();
        
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
    //ajax获取预定展位时的展位图片
    public function getPic(){
        $id=$this->request->param('id');
        $exhstand=$this->ExhibitionModel->getFloorplan($id);
        return json_encode($exhstand);
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

    //展会排行榜推荐位、热门展会推荐位 处理
    private function getShardExh($id,$data,$limit){
        $shardconfig = $this->shardconfig;
        $exhshard = $shardconfig['index'][$data];
        //获取推荐位的标签名称
        foreach ($exhshard as $val){
            $title[] = $this->ShardModel->getShardRemark($val);
        }
        //获取推荐位及推荐位数据
        $shard = $this->ShardModel->getShardandShardData($exhshard[$id],$limit);

        if (!empty($shard['sharddata'])){
            //获取推荐位推荐的展会数据
            foreach ($shard['sharddata'] as $value){
                $exh[] = $this->ExhibitionModel->getExhById($value['fk_type_id']);
            }
            foreach ($exh as $key => $value) {
                $pic=$this->ExhibitionModel->getStaPic($value['id']);
                $exh[$key]['pic'] = json_encode($pic);
                $industryIds = array();
                $industryName = array();
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
                $exh[$key]['industry_name'] = $industryName;                
            }
            //获取该推荐位行业
            foreach ($exh as $key=>$item){
                //获取该展会所选择的行业
                $tradeid = $this->ExhibitionModel->getTradeRelation($item['id']);
                if (!empty($tradeid)){
                    //如果第一个为一级行业，则取该值
                    $industry = $this->CategoryModel->getTradeCategoryOne($tradeid[0]['fk_industry_id']);
                    if ($industry['parent_id'] != 0){
                        //否则获取该子行业的一级行业
                        $industry = $this->CategoryModel->getTradeCategoryOne($industry['parent_id']);
                    }
                    $exh[$key]['industry'] = $industry['name'];
                }
            }
            // dump($exh);die;

            return array($title,$exh);
        }
    }

    //把 20170823 处理为 2017-08-23
    private function realTime($time){
        $res = substr($time, 0,4).'-'.substr($time, 4,2).'-'.substr($time, 6,2);        
        return $res;
    }

    /**
     * 展位预订
     */
    public function standBook(){
        $stand_data['fk_exhibition_id'] = request()->param('exhid');
        $stand_data['corpname'] = request()->param('corpname');
        $stand_data['contact_person'] = request()->param('contacts_person');
        $stand_data['contact_phone'] = request()->param('contact_phone');
        $stand_data['ctime'] = time();
        $stand_data['status'] = 1;
        $id=$this->ExhibitionModel->addStand($stand_data);

    }

    /**
     * 门票预定
     */
    public function ticketBook(){
        $stand_data['fk_exhibition_id'] = request()->param('exhid');
        $visitor_data['visit_type'] = request()->param('visit_type');
        $visitor_data['contact_person'] = request()->param('contact_name');
        $visitor_data['contact_phone'] = request()->param('contact_phone');
        $visitor_data['email'] = request()->param('email');
        $visitor_data['company'] = request()->param('company');
        $visitor_data['position'] = request()->param('position');
        $visitor_data['purpose'] = request()->param('purpose');
        $visitor_data['status'] = 1;
        $visitor_data['ctime'] = time();
        //数据入表
        $v=$this->MemberModel->visitorAdd($visitor_data);
        dump($v);die;
    }

}