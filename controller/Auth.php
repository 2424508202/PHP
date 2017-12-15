<?php
namespace app\www\controller;

use think\Cookie;
use think\Db;
use app\core\model\MemberModel;

/**权限控制控制器
 * Class Auth
 * @package app\www\controller
 */
class Auth extends Base{

    public function __construct()
    {
        parent::__construct();
        //获取权限配置文件
        $memconfig = require "../application/www/extra/mem.auth.conf.php";
        //获取登录Cookie
        $userinfo = Cookie::get('userinfo');
        if (empty($userinfo)){
            //如果用户信息为空则重定向到登录页面
            $this->redirect("User/login");
        }else{
            //加载权限节点
            if ($userinfo['role'] == 1){
                //主办方
                $menu = $memconfig['sponsor'];
            }elseif ($userinfo['role'] == 2){
                //参展商
                $menu = $memconfig['exhibitor'];
            }elseif ($userinfo['role'] == 3){
                //服务商
                $menu = $memconfig['service'];
            }else{
                //观展者
                $menu = $memconfig['visitor'];
            }
            $this->assign('menu',$menu);

            //获取用户登录信息
            $MemberModel = new MemberModel;
            $userinfo = $MemberModel->getMemberInfo($userinfo['id']);
            $this->assign('userinfo',$userinfo);
        }
    }
}