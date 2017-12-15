<?php
/**
 * 院系信息控制器
 */
class Faculty extends CI_Controller{
    /**
     * 构造方法，初始化所需模型
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('faculty_model');
    }

    public function index(){
        $this->load->view('faculty');
    }

    /**
     * 添加院系
     */
    public function faculty_add()
    {
        $fname = !empty($this->input->post('fname')) ? trim($this->input->post('fname')) : '';
        if (!empty($fname))
        {
            $temp = $this->faculty_model->faculty_add($fname);
            if ($temp === true)
            {
                echo 1;
            }
        }
    }
}