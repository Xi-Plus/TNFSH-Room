<!DOCTYPE html>
<html lang="zh-Hant-TW">
<?php
date_default_timezone_set("Asia/Taipei");
include_once("../func/checkpermission.php");
include_once("../func/sql.php");
include_once("../func/common.php");
include_once("../func/msgbox.php");
function loginsuccess($row){
	global $noshow;
	$cookie=getrandommd5();
	setcookie("TNFSH_Classroom", $cookie, time()+86400*7, "/");
	$query=new query;
	$query->table = "session";
	$query->value = array(
		array("id",$row["id"]),
		array("cookie",$cookie)
	);
	INSERT($query);
	addmsgbox("success","登入成功",false);
	$noshow=false;
	?><script>setTimeout(function(){location="../<?php echo (@$_GET["from"]==""?"home":@$_GET["from"]);?>";},3000)</script><?php
}
$noshow=true;
$nosignup=true;
if(checklogin()){
	addmsgbox("info","你已經登入了",false);
	$noshow=false;
	?><script>setTimeout(function(){location="../home";},1000)</script><?php
}else if(isset($_POST['user'])){
	$query=new query;
	$query->table = "account";
	$query->column = array("id","pwd","power");
	$query->where = array(
		array("user",$_POST['user'])
	);
	$query->limit = array(0,1);
	$row = fetchone(SELECT($query));
	if($row===null){
		addmsgbox("danger","無此帳號");
	}else if(@crypt($_POST['pwd'],$row["pwd"])==$row["pwd"]){
		if($row["power"]<=0){
			addmsgbox("warning","此帳戶已遭封禁，無法登入");
		}else{
			loginsuccess($row);
		}
	}else if($row["pwd"]==""){

$user_id = $_POST['user'];
$user_passwd = $_POST['pwd'];
		$fp = fsockopen ("mail.tnfsh.tn.edu.tw", 110, $errno, $errstr, 10);
		if (!$fp) {
			addmsgbox("danger","連接伺服器發生錯誤: $errstr ($errno)");
		}
		else{
fgets ($fp,128);
fputs ($fp, "USER $user_id
");
fgets ($fp,128);
fputs ($fp, "PASS $user_passwd
");
if (!feof($fp)) {
	if(substr(fgets($fp,128),0,14)=="+OK Logged in."){
		loginsuccess($row);
	}else {
		addmsgbox("danger","密碼錯誤");
	}
}
fputs ($fp, "QUIT
");
}
fclose ($fp);
	}else {
		addmsgbox("danger","密碼錯誤");
	}
}
?>
<head>
<meta charset="UTF-8">
<?php
include_once("../res/comhead.php");
?>
<title>登入-臺南一中教室借用管理系統</title>
<link href="login.css" rel="stylesheet" type="text/css">
</head>
<body Marginwidth="-1" Marginheight="-1" Topmargin="0" Leftmargin="0">
<?php
	include_once("../res/header.php");
	if($noshow){
?>
<div class="row">
	<div class="col-xs-12 col-sm-offset-3 col-sm-6 col-md-offset-4 col-md-4">
		<h2>登入</h2>
			<form method="post">
				<div class="input-group">
					<span class="input-group-addon">帳號</span>
					<input class="form-control" name="user" type="text" value="<?php echo @$_POST['user'];?>" maxlength="32">
					<span class="input-group-addon glyphicon glyphicon-user"></span>
				</div>
				<div class="input-group">
					<span class="input-group-addon">密碼</span>
					<input class="form-control" name="pwd" type="password">
					<span class="input-group-addon glyphicon glyphicon-asterisk"></span>
				</div>
				<button type="submit" class="btn btn-success">
					<span class="glyphicon glyphicon-hand-right"></span>
					登入 
				</button>
			</form>
<?php
	}
	include("../res/footer.php");
?>
</body>
</html>