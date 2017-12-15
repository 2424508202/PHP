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
use think\Cookie;
use think\Request;
use think\Db;

/**首页控制器
 * Class Index
 * @package app\www\controller
 */
class Index extends Login{

    /**构造方法，实例化模型，传输公共数据
     * Index constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        parent::__construct($request);
        //加载配置文件
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
        //实例化展会模型
        $this->ExhibitionModel = new ExhibitionModel;
        //实例化用户模型
        $this->MemberModel = new MemberModel;        
        //实例化城市模型
        $this->RegionModel = new RegionModel;
        //实例化服务商模型
        $this->ServiceModel = new ServiceModel;
        //实例化企业模型
        $this->CompanyModel = new CompanyModel;
        //实例化资讯模型
        $this->ArticleModel = new ArticleModel;
        //页面标题
        $this->assign('title','展会网-首页');
        //传输当前操作位置
        $this->assign('action','index');
    }

    /**
     * 首页加载
     */
    public function index(){

        $shardconfig = $this->shardconfig;
        //左侧无限级分类
        $config = $this->config;
        $industry = $config['industry'];
        //公告信息
        $notice = $this->SystemModel->getNoticeOne(0);
        //右侧最新展会
        $exh_new = $this->ExhibitionModel->getExhibition(null);
        //获取城市
        foreach ($exh_new as $key=>$value){
            $parent_id = $this->RegionModel->getArea($value['fk_region_id']);
            $city = $this->RegionModel->getArea($parent_id['parent_id']);
            $exh_new[$key]['city'] = $city['city'];
        }
        //获取城市展会城市配置
        $city =  $config['city'];
        //获取时间轴
        $timebase = $this->getTimeBase();

        //默认城市展会
        $cityExh = $this->getCityExh($city[0]['id']);
        //优秀服务商
        $servicecompany = $this->getServerCompany();
        //服务资讯
        $article = $this->getArtNig(1,9);
        //获取精选展会默认加载数据
        $bestexhtitle = $shardconfig['index']['bestexh'];
        foreach ($bestexhtitle as $key=>$id){
            $bestexh[] = $this->getShardExh($key, 'bestexh',7);
        }
        //获取行业展会默认加载数据
        $tradeexhtitle = $shardconfig['index']['tradeexh'];
        foreach ($tradeexhtitle as $key=>$id){
            $tradeexh[] = $this->getShardExh($key,'tradeexh',6);
        }
        //获取展会服务的默认加载数据
        $exhservicetitle = $shardconfig['index']['exhservice'];
        foreach ($exhservicetitle as $key=>$id){
            $exhservice[] = $this->getShardServer($key,6);
        }
        //获取展会纵横推荐资讯内容
        $exharticle = $shardconfig['index']['exharticle'];
        foreach ($exharticle as $value) {
            $exharticle = $this->ShardModel->getNewsRec($value,9,3);
        }
        //获取首页所有自定义推荐位
        $shardcustom = $this->getShardCustom();
        //展会项目精选推荐位
        $exhsel = $shardconfig['index']['exhsel'];
        foreach ($exhsel as $value) {
            $exhsellist=$this->ShardModel->getExhByShaId($value,7);
        }
        //近期展会推荐位
        $exhrecent = $shardconfig['index']['exhrecent'];
        foreach ($exhrecent as $value) {
            $exhrecentlist=$this->ShardModel->getExhByShaId($value,5);
        }
        foreach ($exhrecentlist as $key => $value) {
            $exhrecentlist[$key]['region']=$this->getProById($value['fk_region_id']);
        }
        //获取所有展会数量
        $exhnum=$this->ExhibitionModel->getNumAll();
        //获取所有展位预订次数
        $standnum=$this->ExhibitionModel->getStandNumAll();
        //获取所有服务商数量
        $sernum=$this->ServiceModel->getSerNumAll();
        //获取所有申请服务的参展商数量
        //$serforexh=$this->
        //获取用户登录信息
        $userId = Cookie::get("userinfo")['id'];
        $userinfo = $this->MemberModel->getMemberInfo($userId);

        $this->assign('shard',$shardcustom);
        $this->assign('exharticle',$exharticle);
        $this->assign('exhservice',$exhservice);
        $this->assign('tradeexh',$tradeexh);
        $this->assign('bestexh',$bestexh);
        //传输默认时间
        $this->assign('time',time());
        $this->assign('servicecompany',$servicecompany);
        $this->assign('article',$article);
        $this->assign('cityExh',$cityExh);
        $this->assign('timebase',$timebase);
        $this->assign('city',$city);
        $this->assign('exh_new',$exh_new);
        $this->assign('notice',$notice);
        $this->assign('industry',$industry);
        $this->assign('exhrecentlist',$exhrecentlist);
        $this->assign('exhsellist',$exhsellist);        
        $this->assign('exhnum',$exhnum);
        $this->assign('standnum',$standnum);
        $this->assign('sernum',$sernum); 
        $this->assign('userinfo',$userinfo);
        return $this->fetch();
    }

    //根据地区id获取省份名称
    private function getProById($id){
        $district=$this->RegionModel->getArea($id);
        $parent=$this->RegionModel->getArea($district['parent_id']);
        if(!empty($parent['province'])){
            return $parent['province'];
        }else{
            $province=$this->RegionModel->getArea($parent['parent_id']);
            return $province['province'];
        }
    }    
    //ajax获取预定展位时的展位图片
    public function getPic(){
        $id=$this->request->param('id');
        $exhstand=$this->ExhibitionModel->getFloorplan($id);
        return json_encode($exhstand);
    }    
    /**
     * 城市展会
     */
    private function getCityExh($cityid){
        //获取默认城市信息
        //设置城市id cookie
        cookie::set('cityid',$cityid);
        $districts = $this->RegionModel->getDistrict($cityid);
        //获取展会信息
        foreach ($districts as $district){
            $result= $this->ExhibitionModel->getExhByArea($district['id']);
            if (empty($result)){
                continue;
            }else{
                foreach ($result as $value){
                    $exh[] = $value;
                }
            }
        }
        //获取行业信息
        if (isset($exh)){
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
                    }
            }
            return $exh;

        }
    }
    /**
     * 获取精选展会,行业展会内容
     */
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
            return array($title,$exh);
        }
    }

    /**
     * 获取展会服务推荐位数据
     */
    private function getShardServer($id,$limit){
        $shardconfig = $this->shardconfig;
        $exhshard = $shardconfig['index']['exhservice'];
        //获取推荐位的标签名称
        foreach ($exhshard as $val){
            $title[] = $this->ShardModel->getShardRemark($val);

        }
        //获取推荐位及推荐位数据
        $shard = $this->ShardModel->getShardandShardData($exhshard[$id],$limit);
        if (!empty($shard['sharddata'])){
            //获取服务商信息
            foreach ($shard['sharddata'] as $value){
                $servercompany[] = $this->CompanyModel->getCompany($value['fk_type_id'],3);
            }
            foreach ($servercompany as $key=>$item){
                //获取该服务商所选择的服务
                $serviceid = $this->ServiceModel->getServiceRelation($item['fk_role_id']);
                if (!empty($serviceid)){
                    $service = array();
                    foreach ($serviceid as $val) {
                        $server = $this->CategoryModel->getServiceCategoryOne($val['fk_industry_id']);
                        if ($server['parent_id'] == 0){
                            //只取一级服务
                            $service[] = $server['name'];
                        }
                    }
                    $servercompany[$key]['service'] = join(' | ',$service );
                    //使用完后销毁，防止下次循环带上本次循环的服务数据
                    unset($service);
                }
                
            }

            return array($title,$servercompany);
        }
    }

    /**
     * 获取所有自定义推荐位
     */
    private function getShardCustom(){
        $shard = $this->ShardModel->getShardCustom();
        $data = '';
        foreach ($shard as $value){
            $data[$value['fk_group_id']][] = $value;
        }
        return $data;
    }

    /**
     * 优秀服务商
     */
    private function getServerCompany(){

        $server = $this->ServiceModel->getServiceNew();

        foreach ($server as $key=>$id){
            $companyname = $this->CompanyModel->getCompany($id['id'], 3);
            $server[$key]['name'] = $companyname['name'];
            $server[$key]['audit'] = $companyname['audit'];
        }

        return $server;
    }
    /**
     * 普通资讯
     */
    private function getArtNig($type,$limit){

        $article = $this->ArticleModel->getArtNig($type,$limit);

        return $article;
    }

    /**ajax获取城市展会
     *
     */

    public function getCityExhibition(){
        $city = request()->param('city');
        $time = request()->param('time');
        //如果第一次请求的是时间则读取默认的城市
        if ($city == ''){
           $city = cookie::get('cityid');
        }
        $time = str_replace('年','-' ,$time);
        $time = str_replace('月','' ,$time );
        if ($time !=''){
            //当前月份第一天的日期
            $firstDay = date("Y-m-01",strtotime($time));
            //当前月最后一天的日期
            $lastDay = date("Y-m-d",strtotime("{$firstDay} +1 month "));
            //转换成时间戳
            $firstDate = strtotime($firstDay);
            $lastDate = strtotime($lastDay);
        }else{
            $firstDate = '';
            $lastDate = '';
        }
        //获取该城市下的所有县区
        $districts = $districts = $this->RegionModel->getDistrict($city);
        //获取展会信息
        foreach ($districts as $district){
            $result= $this->ExhibitionModel->getExhibition(array($district['id'],$firstDate,$lastDate));
            if (empty($result)){
                continue;
            }else{
                foreach ($result as $value){
                    $exh[] = $value;
                }
            }
        }
        //获取行业信息
        if (isset($exh)){
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
                    $exh[$key]['industry'] = '暂无';
                }
            }
            //输出页面内容
            $html = '';
            foreach ($exh as $value){
                $html .= "<li>";
                $html .="<a href='' class='b1' title=".$value['industry'].">[".$value['industry']."]</a>";
                $html .="<a href='' class='b2' title=".$value['title']."><span class='text'>".mb_substr($value['title'],0,20,'utf-8')."</span>";
                if ($value['official'] == 1){
                    $html .="<span class=\"img\"><img src=\"/static/www/images/cszh_dai.jpg\" alt=\"\"></span>";
                }elseif ($value['official'] == 2){
                    $html .="<span class=\"img\"><img src=\"/static/www/images/cszh_guan.jpg\" alt=\"\"></span>";
                }
                if ($value['attestation'] == 1){
                    $html .="<span class=\"img\"><img src=\"/static/www/images/cszh_v.jpg\" alt=\"\"></span>";
                }
                $html .="</a>";
                $html .="<span class=\"b3\">".date("Y-m-d",$value['begin_time'])."～" .date("Y-m-d",$value['end_time'])."</span>";
                $html .="<span class=\"b4\">".$value['hall']."</span>";
                if ($value['begin_time']<time() && $value['end_time']>time()){
                    $html .="<a href='javascript:' class=\"b5\" style='color: grey'>预订展位</a><a href='javascript:'class=\"b5\" style='color: grey'>预订门票</a>";
                }else{
                    if ($value['end_time']<time()){
                        $html .="<a href='javascript:'class=\"b5\" style='color: grey'>预订展位</a><a href='javascript:'class=\"b5\" style='color: grey'>预订门票</a>";
                    }else{
                        $html .="<a href=\"\" class=\"b5\">预订展位</a><a href=\"\" class=\"b6\">预订门票</a>";
                    }
                }

                $html .="</li>";
            }
            return $html;
        }
    }

    /**
     * 获取当前时间的前后半年
     */
    private function getTimeBase(){
        //获取当前月的前半年
        for ($i=1;$i<7;$i++){
            $last_date[$i] = date('Y年m月',strtotime('-'.$i.'month'));
        }
        $last_date = array_reverse($last_date);
        $last_date[7] = date('Y年m月');
        for ($i=1;$i<18;$i++){
            $next_date[$i] = date('Y年m月',strtotime('+'.$i.'month'));
        }
        $date = array_merge($last_date,$next_date);
        return $date;
    }


    /**
     * ajax获取无限极分类菜单信息
     */
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
                $html.="<li><a href=''>".$val['name']."</a></li>";
            }
            $html .="</ul>";
            $html .="</div>";
        }


        return $html;
    }

}