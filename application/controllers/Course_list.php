<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 列表页控制器
 */
class Course_list extends CI_Controller{

    /**
     * 构造函数，初始化模型
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('user_model');
        $this->load->model('faculty_model');
        $this->load->model('courses_model');
    }
    /**
     * 获取列表页显示数据信息
     */
    public function index()
    {
        //获取所有院系
        $faculty_all = $this->faculty_model->get_all();
        //获取课程信息列表
        $search = !empty($this->input->post('search')) ? trim($this->input->post('search')) : '';
        $courses = $this->courses_model->get_courses_by_search($search);

        $data['username'] = $this->session->userdata('username');
        $data['faculty_all'] = $faculty_all;
        $data['courses'] = $courses;
        $data['search'] = $search;
        $this->load->view('list',$data);

    }

    /**
     * 通过课程id删除课程信息
     */
    public function delete()
    {
        $id = !empty($this->input->post('id')) ? trim($this->input->post('id')) : '';
        $temp = $this->courses_model->course_delete($id);
        if ($temp === true)
        {
            redirect('course_list/index');
        }
    }
}