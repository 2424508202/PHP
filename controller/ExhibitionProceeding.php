<?php
namespace app\www\controller;

use app\core\model\ExhibitionModel;
use app\core\model\RegionModel;
use app\core\model\CategoryModel;
use think\Request;

/**
 * 展会会刊控制器
 */
class ExhibitionProceeding extends Login{

	public function __construct(Request $request){
		parent::__construct($request);
		//实例化展会模型
        $this->ExhibitionModel = new ExhibitionModel;
        //实例化城市模型
        $this->RegionModel = new RegionModel;
        //实例化分类模型
        $this->CategoryModel = new CategoryModel;	
		//页面标题
        $this->assign('title','展会网-展会会刊');
        //传输当前操作位置
        $this->assign('action','exhibition_proceeding');		

	}

	/**
	 * 展会会刊列表页
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
            $proid=1;
            $citys='';
            $region=0;
        }
        $tradeid=request()->param('industry');
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
        // 一级行业分类
        $tradeFirst = $this->CategoryModel->getTradeFirst();
        //获取所有参数
        $temps['industry']   = $this->request->param('industry');
        $temps['region']   = $this->request->param('region');
        $temps['begin_time']   = $this->request->param('begin_time');
        $temps['end_time']   = $this->request->param('end_time');
        $temps['sort']   = $this->request->param('sort');
        $temps['page']   = $this->request->param('page');

        $temps['industry'] = $temps['industry'] ? $temps['industry'] : 0;
        $temps['region'] = $temps['region'] ? $temps['region'] : 1;
        $temps['sort'] = $temps['sort'] ? 1 : 0;
        $temps['page'] = $temps['page'] ? $temps['page'] : 1;
		if (!empty($temps['begin_time'])&&(mb_strlen($temps['begin_time']) == 8)) {
            $beginTime = $this->realTime($temps['begin_time']);
        }
		if (!empty($temps['end_time'])&&(mb_strlen($temps['end_time']) == 8)) {
            $endTime = $this->realTime($temps['end_time']);
        }
        $districtsids=$this->getSpoByCityId($temps['region']);
        if(is_array($districtsids)){
            $temps['regionlist'] = $districtsids;
        }
        //分页获取展会会刊信息
        $exhibitionlist=$this->ExhibitionModel->getExhAllPro($temps,12,$id);               
        $page=$this->request->param('page');
        $pageshow=$exhibitionlist['list']->render();
		$exhnumber=$exhibitionlist['num'];
        if($exhnumber-12*$temps['page']>0){
            $number=12;
        }else{
            $number=$exhnumber-12*$temps['page']+12;
        }
        $exhibition=json_decode( json_encode( $exhibitionlist ), true );
        foreach ($exhibition['list']['data'] as $key => $value) {
            $exhibition['list']['data'][$key]['region']=$this->getProById($value['fk_region_id']);
            $exhibition['list']['data'][$key]['industry']=$this->getIndFir($value['fk_industry_id']);
        }
        //获取当前时间和一年前时间
        $beginTime = (date('Y',time())-1).date('md',time());
        $endTime = (date('Y',time())).date('md',time());  
        $this->assign('tradeid',$tradeid);
        $this->assign('tradelist',$tradelist);        
		$this->assign('allProvince',$allProvince);
        $this->assign('citys',$citys);
        $this->assign('region',$region);
        $this->assign('id',$id);
        $this->assign('tradeFirst',$tradeFirst);
        $this->assign('temps',$temps);
        $this->assign('beginTime',$beginTime);
        $this->assign('endTime',$endTime); 
        $this->assign('exhibition',$exhibition);   
		$this->assign('page',$page);
		$this->assign('pageshow',$pageshow);
		$this->assign('number',$number);
        $this->assign('beginTime',$beginTime);
        $this->assign('endTime',$endTime);
		return $this->fetch();
	}
	//把 20170823 处理为 2017-08-23
    private function realTime($time){
        $res = substr($time, 0,4).'-'.substr($time, 4,2).'-'.substr($time, 6,2);        
        return $res;
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
	//通过省份或城市id来获取地区id
    private function getSpoByCityId($id){
        if($id!=''){
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
    //通过所属行业id获取一级行业
    private function getIndFir($id){
        $industry=$this->CategoryModel->getParentid($id);
        if($industry['parent_id']==0){
            return $industry['name'];
        }else{
            $parent=$this->CategoryModel->getParentid($industry['parent_id']);
            return $parent['name'];
        }
    }  	

	/**
	 * 会刊购买页
	 */
	public function pay(){
        $id=request()->param('exhmes');
        // $id=$_POST["huikan"];
        dump($id);
		return $this->fetch();
	}
}