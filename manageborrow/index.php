<!DOCTYPE html>
<html lang="zh-Hant-TW">
<?php
date_default_timezone_set("Asia/Taipei");
include_once("../func/checkpermission.php");
include_once("../func/sql.php");
include_once("../func/common.php");
include_once("../func/data.php");
include_once("../func/msgbox.php");
$login=checklogin();
$period=periodname();
$showdata=true;
if(isset($_POST["hash"])){
	$query=new query;
	$query->table = "borrow";
	$query->column = array("*");
	$query->where = array(
		array("hash",$_POST["hash"])
	);
	$borrow=fetchone(SELECT($query));
	if($_POST["type"]=="edit"){
		if(checkroompermission($login["id"],$borrow["roomid"])){
			$query=new query;
			$query->table = "borrow";
			$query->value = array(
				array("message",$_POST["message"]),
				array("valid",$_POST["valid"]),
				array("updatetime",date("Y-m-d H:i:s"))
			);
			$query->where = array(
				array("hash",$_POST["hash"])
			);
			UPDATE($query);
			addmsgbox("success","已修改");
		}else addmsgbox("danger","你沒有權限");
	}else if($_POST["type"]=="delete"){
		if(checkborrowpermission($_POST["hash"],$login["id"])){
			$showdata=false;
			$query=new query;
			$query->table = "borrow";
			$query->where = array(
				array("hash",$_POST["hash"])
			);
			DELETE($query);
			$room=getoneroom($borrow["roomid"]);
			$cate=getonecate($room["cate"]);
			addmsgbox("info","已刪除預約 ".$cate["name"]."-".$room["name"]." 日期 ".$borrow["date"]." ".$period[$borrow["class"]]);
		}else addmsgbox("danger","你沒有權限");
	}
}
$query=new query;
$query->table = "borrow";
$query->column = array("*");
$query->where = array(
	array("hash",@$_GET["hash"])
);
$borrow=fetchone(SELECT($query));
if($showdata&&$borrow===null){
	$showdata=false;
	addmsgbox("danger","查無此借用");
}
else if($login===false)addmsgbox("info","登入後才可管理此筆借用");
else if(!checkborrowpermission($_GET["hash"],$login["id"]))addmsgbox("info","你沒有權限管理此筆借用");
else if(!checkroompermission($login["id"],$borrow["roomid"]))addmsgbox("info","你僅能取消此借用");
?>
<head>
<?php
include_once("../res/comhead.php");
?>
<title>借用管理-<?php echo $cfg['site']['name']; ?></title>
</head>
<body Marginwidth="-1" Marginheight="-1" Topmargin="0" Leftmargin="0">
<?php
include_once("../res/header.php");
if($showdata){
?>
<div class="row">
	<div class="col-md-offset-3 col-md-6">
		<h2>借用管理</h2>
		<form method="post">
			<input name="hash" type="hidden" value="<?php echo $borrow["hash"];?>">
			<?php
			$room=getoneroom($borrow["roomid"]);
			$cate=getonecate($room["cate"]);
			$acct=getoneacct($borrow["userid"]);
			?>
			<div class="input-group">
				<span class="input-group-addon">姓名</span>
				<input class="form-control" type="text" disabled value="<?php echo $acct["name"]; ?>">
				<span class="input-group-addon glyphicon glyphicon-user"></span>
			</div>
			<div class="input-group">
				<span class="input-group-addon">場地</span>
				<input class="form-control" type="text" disabled value="<?php echo $cate["name"]."-".$room["name"]; ?>">
				<span class="input-group-addon glyphicon glyphicon-home"></span>
			</div>
			<div class="input-group">
				<span class="input-group-addon">日期</span>
				<input class="form-control" type="text" disabled value="<?php echo $borrow["date"]; ?>">
				<span class="input-group-addon glyphicon glyphicon-calendar"></span>
			</div>
			<div class="input-group">
				<span class="input-group-addon">節次</span>
				<input class="form-control" type="text" disabled value="<?php echo $period[$borrow["class"]]; ?>">
				<span class="input-group-addon glyphicon glyphicon-time"></span>
			</div>
			<div class="input-group">
				<span class="input-group-addon">更新</span>
				<input class="form-control" type="text" disabled value="<?php echo $borrow["updatetime"]; ?>">
				<span class="input-group-addon glyphicon glyphicon-time"></span>
			</div>
			<div class="input-group">
				<span class="input-group-addon">審核</span>
				<select class="form-control" name="valid" <?php echo (checkroompermission($login["id"],$borrow["roomid"])?"":"disabled"); ?>>
					<option value="1" <?php echo ($borrow["valid"]=="1"?"selected":""); ?>>允許</option>
					<option value="-1" <?php echo ($borrow["valid"]=="-1"?"selected":""); ?>>拒絕</option>
					<option value="0" <?php echo ($borrow["valid"]=="0"?"selected":""); ?>>審核中</option>
				</select>
				<span class="input-group-addon glyphicon glyphicon-flag"></span>
			</div>
			<div class="input-group">
				<span class="input-group-addon">訊息</span>
				<input class="form-control" name="message" type="text" value="<?php echo $borrow["message"]; ?>" <?php echo (checkroompermission($login["id"],$borrow["roomid"])?"":"disabled"); ?>>
				<span class="input-group-addon glyphicon glyphicon-info-sign"></span>
			</div>
			<?php
			if(checkroompermission($login["id"],$borrow["roomid"])){
			?>
			<button name="type" value="edit" type="submit" class="btn btn-success">
				<span class="glyphicon glyphicon-pencil"></span>
				修改
			</button>
			<?php
			}
			if(checkborrowpermission($_GET["hash"],$login["id"])){
			?>
			<button name="type" value="delete" type="submit" class="btn btn-danger" onClick="if(!confirm('確認取消?'))return false;">
				<span class="glyphicon glyphicon-trash"></span>
				取消 
			</button>
			<?php
			}
			?>
		</form>
	</div>
</div>
<?php 
}
include("../res/footer.php");
?>
</body>
</html>