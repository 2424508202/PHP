<?php
namespace app\www\controller;

use think\Cookie;

/**登录验证控制器
 * Class Login
 * @package app\www\controller
 */
class Login extends Base{

    public function __construct()
    {
        parent::__construct();
        $userinfo = Cookie::get('userinfo');

        $this->assign('userinfo',$userinfo);
        
    }
}