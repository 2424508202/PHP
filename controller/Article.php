<?php
namespace app\www\controller;
use app\core\model\ExhibitionModel;
use app\core\model\ArticleModel;
use app\core\model\ShardModel;
use app\core\model\CategoryModel;
use think\Request;
use think\Db;
use think\Paginator;

/**
*主办方控制器
*@author yangzhiwei
*@date : 2017-08-11
*/
class Article extends Login {
    //构造方法
    public function __construct(Request $request){
        parent::__construct($request);
        //加载配置文件
        $info = require "../application/admin/extra/exhibitor.info.conf.php";
        $this->info = $info;
        //加载推荐位配置文件
        $shardconfig = require "../application/www/extra/shard.info.conf.php" ;
        $this->shardconfig = $shardconfig;
        //初始化资讯模型
        $this->ArticleModel = new ArticleModel;
        //初始化展会模型
        $this->ExhibitionModel = new ExhibitionModel;
        //初始化推荐位数据模块
        $this->ShardModel = new ShardModel;
        //实例化分类管理模型
        $this->CategoryModel = new CategoryModel;
        //当前操作位置
        $this->assign('action','news');
    } 
	/**
     * 空操作
     * @author cuibo <79710344@qq.com>
     */
	public function _empty(){
        //解析到前台咨讯
        return $this->article();
    }

    /**
     * 资讯首页
     */
    public function article(){
        //加载推荐位配置文件
        $shardconfig = $this->shardconfig;
        //轮播图推荐位
        $pictop = $shardconfig['index']['pictop'];
        foreach ($pictop as $value) {
            $pictoplist=$this->ShardModel->getMessage($value,3);
        }
        //轮播图下方推荐位
        $picdown = $shardconfig['index']['picdown'];
        foreach ($picdown as $value) {
            $picdownlist=$this->ShardModel->getMessage($value,3);
        }

        //7x24小时快讯标签推荐位
        $fastnews = $shardconfig['index']['newsfast'];
        foreach ($fastnews as $value) {
            //根据推荐位id获取数据
            $fastnewslist=$this->ShardModel->getNewsRec($value,7,1);
        }
        foreach ($fastnewslist as $key => $value) {
            $temp=date("YmdHm",$value['pubtime']);
            $fastnewslist[$key]['pubtime']=$this->getTime($temp);
        }
        //热门展会资讯标签推荐位
        $exhhotnews=$shardconfig['index']['exhhotnews'];
        foreach ($exhhotnews as $key=>$value) {
            //根据推荐位id获取数据
            $exhhotnewslist[]=$this->getShardExh($key,'exhhotnews',6);
        }
        //热门资讯下方广告位
        $advertise = $shardconfig['index']['advertise'];
        foreach ($advertise as $value) {
            $advertiselist=$this->ShardModel->getMessage($value,2);
        }
        //展会热门资讯右侧展会推荐推荐位
        $exhrec=$shardconfig['index']['exhrec'];
        foreach ($exhrec as $value) {
            //根据推荐位id获取数据
            $exhreclist=$this->ShardModel->getExhByShaId($value,6);
        }
        //资讯推荐推荐位
        $newsrec=$shardconfig['index']['newsrec'];
        foreach ($newsrec as $key=>$value) {
            //根据推荐位id获取数据
            $newsreclist[]=$this->getShardExh($key,'newsrec',6);
        }
        foreach ($newsreclist as $keyy => $value) {
            foreach ($value[1] as $key => $val) {
                $temp=date("YmdHm",$val['pubtime']);
                $newsreclist[$keyy][1][$key]['pubtime']=$this->getTime($temp); 
            }
        }
        // dump($newsreclist);die;
        //热门资讯推荐位
        $newshot=$shardconfig['index']['newshot'];
        foreach ($newshot as $value) {
            //根据推荐位id获取数据
            $newshotlist=$this->ShardModel->getHotNews($value,10,3);
        }
        foreach ($newshotlist as $key => $value) {
            $temp=date("YmdHm",$newshotlist[$key]['pubTime']);
            $newshotlist[$key]['pubtime']=$this->getTime($temp);
        }
        $this->assign('pictoplist',$pictoplist);
        $this->assign('picdownlist',$picdownlist);
        $this->assign('fastnewslist',$fastnewslist);
        $this->assign('exhhotnewslist',$exhhotnewslist);
        $this->assign('advertisetop',$advertiselist[0]);
        $this->assign('advertisedown',$advertiselist[1]);        
        $this->assign('exhreclist',$exhreclist);
        $this->assign('newsreclist',$newsreclist);
        $this->assign('newshotlist',$newshotlist);
        return $this->fetch();
    }
    //热门展会推荐、资讯推荐
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
            //获取推荐位推荐的展会资讯数据
            foreach ($shard['sharddata'] as $value){
                $news[] = $this->ArticleModel->getArtDet($value['fk_type_id']);
            }
            return array($title,$news);
        }
    }

    /**
     * 资讯-普通资讯
     */
    public function article_common(){
        $shardconfig = $this->shardconfig;
        //通过配置文件添加栏目目录
        $info=$this->info;
        $column=request()->param('column');
        $columnlist=$info['article']['article_type'];
        if($column=='zhbd'){
            $colid=1;
        }elseif($column=='rwbd'){
            $colid=2;
        }elseif($column=='jrhz'){
            $colid=3;
        }elseif($column=='zsml'){
            $colid=4;
        }else{
            $colid=5;
        }
        //获取普通资讯并分页
        $exharticlelist=$this->ArticleModel->getArt($column,$colid);
        $exhartlist=json_decode( json_encode( $exharticlelist ), true );
        //dump($exhartlist);die;
        foreach ($exhartlist['list']['data'] as $key=>$value) {
            $temp=date("YmdHm",$value['pubtime']);
            $exhartlist['list']['data'][$key]['pubtime']=$this->getTime($temp);
        }
        //获取页码
        $page=request()->param('page');
        //分页显示
        $pageshow=$exharticlelist['list']->render();
        //获取当前页数据量
        if($exharticlelist['num']-10*$page>0){
            $number=10;
        }else{
            $number=$exharticlelist['num']-10*$page+10;
        }
        //轮播图推荐位
        $picture=$shardconfig['index']['picture'];
        foreach ($picture as $value) {
            $picturelist=$this->ShardModel->getMessage($value,3);
        }
        //大家都在搜推荐位
        $search=$shardconfig['index']['search'];
        foreach ($search as $value) {
            $searchlist=$this->ShardModel->getMessage($value,12);
        }
        //热门排行推荐位
        $ranking=$shardconfig['index']['ranking'];
        foreach ($ranking as $value) {
            $rankinglist=$this->ShardModel->getHotNews($value,10,1);
        }
        //热门展会推荐位
        $exhhot=$shardconfig['index']['exhhot'];
        foreach ($exhhot as $value) {
            $exhhotlist=$this->ShardModel->getExhByShaId($value,5);
        }
        //dump($column);exit();
        $this->assign('column',$column);
        $this->assign('page',$page);        
        $this->assign('number',$number);
        $this->assign('pageshow',$pageshow);
        $this->assign('artcomnum',$exharticlelist['num']);       
        $this->assign('columnlist',$columnlist);
        $this->assign('exharticlelist',$exhartlist['list']['data']);
        $this->assign('picturelist',$picturelist);
        $this->assign('searchlist',$searchlist);
        $this->assign('rankinglist',$rankinglist);
        $this->assign('exhhotlist',$exhhotlist);       
    	return $this->fetch();
    }

    /**
     * 资讯详情页
     */
    public function article_common_details(){ 
        $shardconfig = $this->shardconfig; 
        $id=request()->param('newsid');
        //获取资讯详情
        $artdetail=$this->ArticleModel->getArtDet($id);
        //相关阅读
        $read=$this->ArticleModel->getArticleNew();
        //热门排行推荐位
        $rankhot=$shardconfig['index']['rankhot'];
        foreach ($rankhot as $value) {
            $rankhotlist=$this->ShardModel->getArtHot($value,10);
        }
        //爆文推荐推荐位
        $goodart=$shardconfig['index']['goodart'];
        foreach ($goodart as $value) {
            $goodartlist=$this->ShardModel->getMessage($value,5);
        }
        $this->assign('artdetail',$artdetail);
        $this->assign('read',$read);
        $this->assign('rankhotlist',$rankhotlist);
        $this->assign('goodartlist',$goodartlist);
        return $this->fetch();
    }   

    /**
     * 展会列表页
     */
    public function exhibition_list(){
        $shardconfig = $this->shardconfig; 
        //获取展会信息
        $exhlist1=$this->ExhibitionModel->getExhAll();
        //获取页码
        $page=request()->param('page');
        //获取当前页数据量
        if($exhlist1['num']-9*$page>0){
            $number=9;
        }else{
            $number=$exhlist1['num']-9*$page+9;
        }
        //分页显示
        $pageshow=$exhlist1['list']->render();
        $exhlist=array();
        foreach ($exhlist1['list'] as $key=>$value) {
            $exhlist[$key]=$value;
        }
        foreach ($exhlist as $key => $value) {
            $industry=$this->getIndustry($value['id']);
            $exhlist[$key]['industry']=$industry;
            $temp=date("YmdHm",$value['pub_time']);
            $exhlist[$key]['pub_time']=$this->getTime($temp);
        }
        //轮播图推荐位
        $picture=$shardconfig['index']['picture'];
        foreach ($picture as $value) {
            $picturelist=$this->ShardModel->getMessage($value,3);
        }
        //大家都在搜推荐位
        $search=$shardconfig['index']['exhsearch'];
        foreach ($search as $value) {
            $searchlist=$this->ShardModel->getMessage($value,12);
        }
        //热门排行推荐位
        $ranking=$shardconfig['index']['exhranking'];
        foreach ($ranking as $value) {
            $rankinglist=$this->ShardModel->getNewsRec($value,10,1);
        }
        //热门展会推荐位
        $exhhot=$shardconfig['index']['eexhhot'];
        foreach ($exhhot as $value) {
            $exhhotlist=$this->ShardModel->getExhByShaId($value,5);
        }
        $this->assign('page',$page);
        $this->assign('pageshow',$pageshow);
        $this->assign('number',$number);
        $this->assign('exhlist',$exhlist);
        $this->assign('exhnum',$exhlist1['num']);
        $this->assign('picturelist',$picturelist);
        $this->assign('searchlist',$searchlist);
        $this->assign('rankinglist',$rankinglist);
        $this->assign('exhhotlist',$exhhotlist);       
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
    //计算发布时间
    private function getTime($date){
        $timenow=date("YmdHm");
        if(substr($timenow,3,2)!=substr($date,3,2)){
            return (substr($timenow,3,2)-substr($date,3,2)).'年前';
        }elseif(substr($timenow,5,2)!=substr($date,5,2)){
            return (substr($timenow,5,2)-substr($date,5,2)).'月前';
        }elseif(substr($timenow,7,2)!=substr($date,7,2)){
            return (substr($timenow,7,2)-substr($date,7,2)).'天前';
        }elseif(substr($timenow,9,2)!=substr($date,9,2)){
            return (substr($timenow,9,2)-substr($date,9,2)).'小时前';
        }elseif(substr($timenow,11,2)!=substr($date,11,2)){
            return (substr($timenow,11,2)-substr($date,11,2)).'分钟前';
        }
    }

    /**
     * 某展会的资讯列表页
     */
    public function article_exhibition_list(){
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
        $industry=$this->getIndustry($id);
        $exhdetlist['industry']=$industry;
        //获取展会资讯信息
        $exhartlist=$this->ArticleModel->getArtById($id);
        //获取页码
        $page=request()->param('page');
        //分页显示
        $pageshow=$exhartlist['list']->render();
        //获取当前页数据量
        if($exhartlist['num']-10*$page>0){
            $number=10;
        }else{
            $number=$exhartlist['num']-10*$page+10;
        }
        //轮播图推荐位
        $picture=$shardconfig['index']['picture'];
        foreach ($picture as $value) {
            $picturelist=$this->ShardModel->getMessage($value,3);
        }
        //大家都在搜推荐位
        $search=$shardconfig['index']['detsearch'];
        foreach ($search as $value) {
            $searchlist=$this->ShardModel->getMessage($value,12);
        }
        //热门排行推荐位
        $ranking=$shardconfig['index']['detranking'];
        foreach ($ranking as $value) {
            $rankinglist=$this->ShardModel->getNewsRec($value,10,1);
        }
        //热门展会推荐位
        $exhhot=$shardconfig['index']['detexhhot'];
        foreach ($exhhot as $value) {
            $exhhotlist=$this->ShardModel->getExhByShaId($value,5);
        }
        $this->assign('cycle',$cycle);
        $this->assign('exhpic',$exhpic);
        $this->assign('page',$page);
        $this->assign('pageshow',$pageshow);
        $this->assign('number',$number);
        $this->assign('exhnum',$exhartlist['num']);        
        $this->assign('exhdetlist',$exhdetlist);
        $this->assign('exhartlist',$exhartlist['list']);
        $this->assign('picturelist',$picturelist);
        $this->assign('searchlist',$searchlist);
        $this->assign('rankinglist',$rankinglist);
        $this->assign('exhhotlist',$exhhotlist);       
        return $this->fetch();
    }
    //ajax获取预定展位时的展位图片
    public function getPic(){
        $id=$this->request->param('id');
        $exhstand=$this->ExhibitionModel->getFloorplan($id);
        return json_encode($exhstand);
    }    
  
}