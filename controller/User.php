<?php
namespace app\www\controller;


use think\Cookie;
use think\Db;
use think\Request;
use think\captcha\Captcha;
use app\core\model\MemberModel;
use app\core\model\SponsorModel;
use think\Session;

/**会员注册控制器
 * Class Register
 * @package app\www\controller
 */
class User extends Login{

    /**
     * 会员注册
     */
    public function register(){
        $MemberModel = new MemberModel;
        if(request()->post()){
            $data['role'] = request()->param('role');
            $data['phone'] = request()->param('phone');
            $data['password'] = request()->param('password');
            //获取哈希值
            $data['hash'] = substr(uniqid(rand()),-4);
            //密码加密
            $data['password'] = $this->password($data['password'],$data['hash']);
            $data['ctime'] = time();
            $result = $MemberModel->postMember($data);
            if ($result){
                return true;
            }else{
                return false;
            }
        }else{

            $captcha = new Captcha();
            return $this->fetch();
        }

    }
    /**会员登录
     * @return bool
     */
    public function login(){
        $MemberModel = new MemberModel;
        $SponsorModel = new SponsorModel;
        if (request()->post()){
            $phone = request()->param('phone');
            $password = request()->param('password');
            $checked = request()->param('checked');
            //查询该用户的hash值
            $user = Db::name('member')->where('phone',$phone)->find();
            if (empty($user)){
                return array(false,'用户不存在');
            }
            $password = $this->password($password,$user['hash']);

            if ($password !== $user['password']){
                return array(false,'密码有误');
            }
            //如果用户选择了记住用户名则存进session
            if ($checked){
                Session::set('username',$user['phone']);
            }
            //如果登陆成功则存储cookie
            $user['role_status'] = $SponsorModel->getSponsor($user['fk_role_id'])['status'];
            Cookie::set('userinfo',$user);
            //更新会员表
            $data['last_logintime'] = time();
            $data['last_loginip'] = $this->get_client_ip();
            $MemberModel->putMember($user['id'],$data);
            return array(true,'登陆成功');
        }else{

            $username = Session::get('username');
            $captcha = new Captcha();
            $this->assign('username',$username);
            return $this->fetch();
        }
    }
    /**退出登录
     * @return bool
     */
    public function logout(){
        Cookie::delete('userinfo');
        $this->redirect("/");
    }
    //验证码ajax验证
    public function checkValidate(){
        
        $code = request()->param('code');
        if (!captcha_check($code)){

            return false;
        }else{

            return true;
        }
    }
    //Aajx验证注册手机号是否已被注册
    public function checkPhone(){
        $phone = request()->param('phone');

        $res = Db::name('member')->where('phone',$phone)->count();

        return $res;
    }
}