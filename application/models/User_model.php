<?php
/**
 * @author:yangzhiwei
 * @date:2017-12-12
 * 用户信息模型
 */
class User_model extends CI_Model{
    
    /**
     * 添加新的用户
     * @param  string $username 用户名称
     * @param  string $password 用户密码
     * @return 
     */
    public function insert_user($username, $password)
    {
        if ($username != '' && $password != '')
        {
            $sql = "INSERT INTO users SET username = ?, password = ?";
            return $this->db->query($sql, array($username, $password));
        }
    }

    /**
     * 获取用户信息
     * @param  string $username 用户名称
     * @return string 用户密码
     */
    public function get_user_by_name($username)
    {
        if ($username != '')
        {
            $sql = "SELECT password FROM users WHERE username = ?";
            $query = $this->db->query($sql, $username);
            return $query->row_array();
        }
    }
}
