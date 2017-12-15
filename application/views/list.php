<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
    <p>欢迎<?php echo $username?>登录</p>
</head>
<body>
    <div>
        <div>
            <!-- <p>院系:</p>
            <select id="dept">
                <?php foreach($faculty_all as $value):?>
                <option value="<?php echo $value['id'];?>"><?php echo $value['name'];?></option>
                <?php endforeach;?>
            </select> -->
            <p><a href="<?php echo site_url('faculty/index');?>"><input type="button" value="添加院系"></a></p>
            <p><a href="<?php echo site_url('courses/index');?>"><input type="button" value="添加课程"></a></p>
                <form action="<?php echo site_url('course_list/index')?>" method="post">
                    <span><input type="text" name="search" placeholder="搜索" value="<?php echo $search;?>"></span>
                    <span><input type="submit" value="搜索"></span>
                </form>
            </p>
        </div>
    <div>
    <div>
        <table id="courses">
            <tr>
            <th>院系</th><th>班级</th><th>课程</th><th>教师</th><th>创建时间</th><th>创建人</th>
            </tr>
            <?php foreach ($courses as $value):?>
            <tr>
                <td><?php echo $value['name'];?></td>
                <td><?php echo $value['class'];?></td>
                <td><?php echo $value['course'];?></td>
                <td><?php echo $value['teacher'];?></td>
                <td><?php echo $value['creat_time'];?></td>
                <td><?php echo $value['creat_person'];?></td>
                <form action="<?php echo site_url('course_list/delete')?>" method="post">
                    <input type="hidden" name="id" value="<?php echo $value['id']?>">
                    <td><input type="submit" value="删除" onclick="remove()"></td>
                </form>
            </tr>
            <?php endforeach;?>
        </table>
    </div>
<script type="text/javascript" src="<?php echo base_url('/public/js/jquery-3.2.1.min.js')?>"></script>
<script>
    function remove()
    {
        $this->parent().remove();

    }
    
</script>
</body>
</html>