<?php
/**
 * 课程信息控制器
 */
class Courses extends CI_Controller{
    
    /**
     * 构造函数，初始化模型
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('courses_model');
        $this->load->model('faculty_model');
    }

    public function index()
    {
        $faculty_all = $this->faculty_model->get_all();
        $data['faculty_all'] = $faculty_all;
        $this->load->view('courses',$data);

    }

    /**
     * 添加课程
     */
    public function course_add()
    {
        $data['fk_faculty_id'] = !empty($this->input->post('fid')) ? (int)$this->input->post('fid') : '';
        $data['classs'] = !empty($this->input->post('classs')) ? trim($this->input->post('classs')) : '';
        $data['course'] = !empty($this->input->post('course')) ? trim($this->input->post('course')) : '';
        $data['teacher'] = !empty($this->input->post('teacher')) ? trim($this->input->post('teacher')) : '';
        $data['time'] = date("Y-m-d");
        $data['person'] = !empty($this->input->post('person')) ? trim($this->input->post('person')) : '';  
        $row = $this->courses_model->courses_add($data);
        if($row === true)
        {
            echo 1;
        }

    }
}