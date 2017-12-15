<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author:yangzhiwei
 * @date:2017-12-12
 * 用户注册控制器
 */
class Register extends CI_Controller{

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
        $this->load->view('register_message');
    }
    
    /**
     * 添加用户信息
     */
    public function user_add()
    {
        $username = !empty($this->input->post('username')) ? trim($this->input->post('username')) : '';
        $password = !empty($this->input->post('password')) ? trim($this->input->post('password')) : '';
        $_SESSION['username'] = $username;
        $temp = $this->user_model->insert_user($username,$password);
        if ($temp === true)
        {
            $this->load->view('list',$username);
            echo 1;
        }
    }
    /**
     * ajax异步获取用户名验证用户名是否存在
     */
    public function name_check()
    {
        $username = !empty($this->input->post('username')) ? trim($this->input->post('username')) : '';
        $temp = $this->user_model->get_user_by_name($username);
        if (empty($temp))
        {
            echo 1;
        }
        else
        {
            echo 0;
        }
    }
}