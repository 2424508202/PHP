<?php
/**
 * @author:yangzhiwei
 * @date:2017-12-14
 * 班级课程信息模型
 */
class Courses_model extends CI_Model{

    /**
     * 添加新的课程
     * @param  array $data 课程数组，包含课程所有信息
     */
    public function courses_add($data)
    {
        if (!empty($data))
        {
            $sql = "INSERT into courses 
                    set fk_faculty_id = ?, class = ?, course = ?, teacher = ?, creat_time = ?, creat_person = ?";
            return $this->db->query($sql, $data);
        }
    }

    /**
     * 根据查询条件获取课程信息，如果条件为空，获取所有课程信息
     * @param string $search 查询条件
     * @return array 课程信息
     */
    public function get_courses_by_search($search)
    {
        $sql = "SELECT f.name, c.id, c.class,c.course, c.teacher,c.creat_time,c.creat_person 
                FROM courses c,faculty f 
                WHERE c.fk_faculty_id = f.id";

        $param = [];
        if ($search != '')
        {
            $sql .= " and (c.class like ? or c.course like ? or c.teacher like ?)";
            $param = array('%' . $search . '%', '%' . $search . '%', '%' . $search . '%');
        }

        $query = $this->db->query($sql, $param);
        return $query->result_array();
    }

    /**
     * 根据课程id删除课程信息
     * @param int $id 课程id
     */
    public function course_delete($id)
    {
        if ($id != '')
        {
            $sql = "DELETE FROM courses WHERE id = ?";
            return $this->db->query($sql, $id);
        }
    }
}
