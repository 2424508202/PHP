<?php
namespace app\www\controller;

use think\Controller;
use Org\Util\FileUpload;

/**前台公共父类
 * Class Base
 * @package app\www\controller
 */
class Base extends Controller{
	

	/**
     * 密码加密函数
     * @param  string $password 明文密码
     * @return string 密文密码
     */
    protected function password($password, $hash) {
        return md5(md5($password) . $hash);
    }
    /**
     * 获取客户端ip
     */
    function get_client_ip($type = 0) {

	    $type       =  $type ? 1 : 0;
	    static $ip  =   NULL;
	    if ($ip !== NULL) return $ip[$type];
	    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
	        $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
	        $pos    =   array_search('unknown',$arr);
	        if(false !== $pos) unset($arr[$pos]);
	        $ip     =   trim($arr[0]);
	    }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
	        $ip     =   $_SERVER['HTTP_CLIENT_IP'];
	    }elseif (isset($_SERVER['REMOTE_ADDR'])) {
	        $ip     =   $_SERVER['REMOTE_ADDR'];
	    }
	    // IP址合法验证
	    $long = ip2long($ip);
	    $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
	    return $ip[$type];
	}

	/**
	 * 文件上传公共方法（图片）
	 * @param $path文件上传路径,$filename $_FILE[$filename][name]
	 * @return array
	 */
	protected function upload($path,$filename){
		//实例化文件上传类
		$upload = new FileUpload;
		$upload->set('path',$path);
		$upload->set('maxsize',1024 * 1024 * 2 );
		$upload->set('allowtype',array('jpg','jpeg','png','gif'));
		$upload->set('israndname',true);
		if($upload->upload($filename)){

			return $upload->getFileName();
		}else{
			$this->error('文件上传失败,请检查文件类型、大小!');
		}
	}
}