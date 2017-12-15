<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html>
    <body>
            <div>院系名称：<input type="text" id="fname"></div>
            <div><input type="button" id="" value="提交" onclick="get_faculty()"></div>
        
    </body>
<script type="text/javascript" src="<?php echo base_url('/public/js/jquery-3.2.1.min.js')?>"></script>
<script>
    function get_faculty()
    {
        var fname=$("#fname").val();
        var temp = 1;
        if(fname==''){
            temp = 0;
        }
        if(temp==1){
            $.ajax({
                url:"<?php echo site_url('faculty/faculty_add');?>",
                type:'post',
                data:{fname:fname},
                success:function (result) {
                    if (result){
                        location.href="<?php echo site_url('course_list/index');?>"; 
                    }
                }
            })
        }
}
</script>
</html>