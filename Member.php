<?php
/**
 * 成员列表控制器
 */
class Member extends CI_Controller{

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('member_model', 'member');
    }

    /**
     * 获取成员信息存入redis
     */
    public function set_member_in_redis()
    {
        $search = !empty($this->input->post('search')) ? trim($this->input->post('search')) : '';
        //获取所有成员信息
        $data = $this->member->member_find();
        if ($search == '')
        {
            foreach ($data as $value)
            {
                $key = $value['stu_name'] . $value['stu_num'];
                $this->cache->redis->save($key, $value);
                $temp[] = $this->cache->redis->get($key);
            }
        }
        else
        {
            //使用原声的redis方法keys对于key值进行模糊查询
            $redis = new Redis();
            $redis->connect('127.0.0.1', 6379);
            foreach ($redis->keys('*' . $search . '*') as $key)
            {
                $temp[] = $this->cache->redis->get($key);
            }
        }
        return $temp;
        
    }

    /**
     * 列表页，显示成员信息
     */
    public function index()
    {
        $members = $this->set_member_in_redis();
        $data['search'] = !empty($this->input->post('search')) ? trim($this->input->post('search')) : '';
        $data['username'] = $this->cache->memcached->get('username');
        $data['members'] = $members;
        $this->load->view('list',$data);
    }

    public function add1()
    {
        $this->load->view('member_add');
    }

    /**
     * 添加成员
     */
    public function add()
    {
        $data['name'] = !empty($this->input->post('name')) ? trim($this->input->post('name')) : '';
        $data['num'] = !empty($this->input->post('num')) ? trim($this->input->post('num')) : '';
        $data['class'] = !empty($this->input->post('class')) ? trim($this->input->post('class')) : '';
        $temp = $this->member->add($data);
        if ($temp === true)
        {
            redirect('member/index');
        }
        else
        {
            echo '添加失败！！';
        }
    }

    /**
     * 删除成员
     */
    public function delete($id)
    {
        $id = !empty($this->input->post('id')) ? trim($this->input->post('id')) : '';
        $temp = $this->member->remove($id);
        if ($temp === true)
        {
            redirect('member/index');
        }
    }

    /**
     * 根据id获取成员信息
     */
    public function edit()
    {
        $id = !empty($this->input->post('id')) ? trim($this->input->post('id')) : '';
        $data['member'] = $this->member->find($id);
        $this->load->view('edit', $data);
    }

    /**
     * 更新成员信息
     */
    public function update()
    {
        $id = !empty($this->input->post('id')) ? trim($this->input->post('id')) : '';
        $data['stu_name'] = !empty($this->input->post('name')) ? trim($this->input->post('name')) : '';
        $data['stu_num'] = !empty($this->input->post('num')) ? trim($this->input->post('num')) : '';
        $data['class'] = !empty($this->input->post('class')) ? trim($this->input->post('class')) : '';
        $temp = $this->member->updata_member($id, $data);
        if ($temp === true)
        {
            redirect('member/index');
        }
    }
}