<?php
//登录用户信息验证
class Login_check{
    private $CI;            
    public function __construct() 
    {        
        $this->CI = &get_instance();     
    }
    public function user_check()
    {
        $this->CI->load->helper('url');
        if (!preg_match("/login.*/i", uri_string()))
        {
            if(!$this->CI->session->userdata('username'))
            {
                redirect('login/index');
            }
        }
        
    }
}
?>