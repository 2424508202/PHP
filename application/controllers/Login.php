<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author:yangzhiwei
 * @date:2017-12-12
 * 用户登录控制器
 */
class Login extends CI_Controller{
    
    /**
     * 构造函数，初始化模型
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('user_model');
    }

    public function index()
    {
        $this->load->view('login');
    }

    /**
     * 验证用户登录信息，并重定向到列表页
     */
    public function get_user()
    {
        $username = !empty($this->input->post('username')) ? trim($this->input->post('username')) : '';
        $temp = $this->user_model->get_user_by_name($username);
        $password = !empty($this->input->post('password')) ? trim($this->input->post('password')) : '';
        // var_dump($temp);die;
        $data['username'] = $username;
        if ($temp['password'] == $password)
        {
            $_SESSION['username']=$username;
            redirect('course_list/index');
        }
        else
        {
            echo '密码错误';
        }       
    }
}