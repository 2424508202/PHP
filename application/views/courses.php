<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html>
    <body>
        <p>院系:</p>
        <select id="dept">
            <?php foreach($faculty_all as $value):?>
            <option value="<?php echo $value['id'];?>"><?php echo $value['name'];?></option>
            <?php endforeach;?>
        </select>
        <form>
            <div>班级：<input type="text" id="classs"></div>
            <div>课程：<input type="text" id="course"></div>
            <div>教师：<input type="text" id="teacher"></div>
            <div>创建人<input type="text" id="person"></div>
            <div><input type="button" id="" value="提交" onclick="get_course()"></div>
        </form>
    </body>
<script type="text/javascript" src="<?php echo base_url('/public/js/jquery-3.2.1.min.js')?>"></script>
<script>
function get_course()
{
    var fid = $('#dept').val();
    var classs = $("#classs").val();
    var course = $("#course").val();
    var teacher = $("#teacher").val();
    var person = $("#person").val();
    $.ajax({
        url:"<?php echo site_url('courses/course_add');?>",
        type:'post',
        data:{fid:fid,classs:classs,course:course,teacher:teacher,person:person},
        success:function (result) {
            if (result == 1){
                console.log(result);
                location.href="<?php echo site_url('course_list/index');?>"; 
            }
        }
    })
}
</script>
</html>