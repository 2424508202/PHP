<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Welcome to CodeIgniter</title>

	<style type="text/css">

	::selection { background-color: #E13300; color: white; }
	::-moz-selection { background-color: #E13300; color: white; }

	body {
		background-color: #fff;
		margin: 40px;
		font: 13px/20px normal Helvetica, Arial, sans-serif;
		color: #4F5155;
	}

	a {
		color: #003399;
		background-color: transparent;
		font-weight: normal;
	}

	h1 {
		color: #444;
		background-color: transparent;
		border-bottom: 1px solid #D0D0D0;
		font-size: 19px;
		font-weight: normal;
		margin: 0 0 14px 0;
		padding: 14px 15px 10px 15px;
	}

	code {
		font-family: Consolas, Monaco, Courier New, Courier, monospace;
		font-size: 12px;
		background-color: #f9f9f9;
		border: 1px solid #D0D0D0;
		color: #002166;
		display: block;
		margin: 14px 0 14px 0;
		padding: 12px 10px 12px 10px;
	}

	#body {
		margin: 0 15px 0 15px;
	}

	p.footer {
		text-align: right;
		font-size: 11px;
		border-top: 1px solid #D0D0D0;
		line-height: 32px;
		padding: 0 10px 0 10px;
		margin: 20px 0 0 0;
	}

	#container {
		margin: 10px;
		border: 1px solid #D0D0D0;
		box-shadow: 0 0 8px #D0D0D0;
	}
	#register {
		color: #fff;
		background: blue;
		font-size: 16px;
		width:180px;
		height=20px;
	}
	</style>
</head>
<body>

<div id="container">
	<h1>用户注册</h1>
	<form id="body" action="" method="">
		<code><input id="username" type="text" name="username" placeholder="请输入用户名" onblur="check();"><a id=judge style="display:none">用户名已存在</a></code>
        <code><input id="password" type="password" name="password" placeholder="请输入密码"></code>
        <code><input id="repassword" type="password" name="repassword" placeholder="请确认密码"><a id="repet" style="display:none">请重新输入</a></code>
		<p><input id="register" type="button" value="注册" onclick="compare()"></p>
	</form>
	</div>
</div>
<script type="text/javascript" src="<?php echo base_url('/public/js/jquery-3.2.1.min.js')?>"></script>
<script>
function check()
{
	var username=$("#username").val();
	$.ajax({
		url:"<?php echo site_url('register/name_check');?>",
		data:{username:username},
		type:"post",
		success:function (result)
		{
			if(result==0)
			{
				$('#judge').show();
			}else{
				$('#judge').hide();
			}
		}
	});
}
function compare()
{
	var username = $("#username").val();
	var password = $("#password").val();
	var repassword = $("#repassword").val();
	var temp = 1;
	if(username=='')
	{	
		temp = 0;
	}
	if(password!=repassword)
	{
		$('#repet').show();
		temp = 0;
	}else{
		$('#repet').hide();
	}
	if(temp==1)
	{
		$.ajax({
			url:"<?php echo site_url('register/user_add');?>",
			type:'post',
			data:{username:username,password:password},
			success:function (result) {
				if (result){
					location.href="<?php echo site_url('course_list/index');?>";
				}
			}
		})
	}
}
</script>
</body>
</html>