<?php
/**
 * @author:yangzhiwei
 * @date:2017-12-14
 * 院系信息模型
 */
class Faculty_model extends CI_Model{

    /**
     * 添加新的院系
     * @param  string $fname 院系名称
     * @return 
     */
    public function faculty_add($fname)
    {
        if ($fname != '')
        {
            $sql = "INSERT INTO faculty SET name = ?";
            return $this->db->query($sql, $fname);
        }
    }

    /**
     * 获取所有院系
     * @return array 包含所有院系id和名字
     */
    public function get_all()
    {
        $query = $this->db->query("SELECT id, name from faculty");
        return $query->result_array();
    }
}
