<!DOCTYPE html>
<html lang="zh-Hant-TW">
<?php
date_default_timezone_set("Asia/Taipei");
include_once("../func/checkpermission.php");
include_once("../func/sql.php");
include_once("../func/shortcut.php");
include_once("../func/data.php");
include_once("../func/msgbox.php");
$login=checklogin();
if($login===false)header("Location: ../login/?from=validborrow");
else if(!checkroompermission($login["id"])){
	addmsgbox("danger","你沒有權限");
	?><script>setTimeout(function(){location="../home";},1000);</script><?php
}
$room=getallroom();
$cate=getallcate();
$acct=getallacct();
if(isset($_POST['valid'])){
	$row=getoneborrow($_POST["hash"]);
	if(!checkroompermission($login["id"],$row["roomid"])){
		addmsgbox("danger","你沒有權限");
	}else if(@$_POST['valid']=="true"){
		$query=new query;
		$query->table = "borrow";
		$query->value = array(
			array("valid",1),
			array("updatetime",date("Y-m-d H:i:s"))
		);
		$query->where = array(
			array("hash",$_POST["hash"])
		);
		UPDATE($query);
		addmsgbox("success","已允許申請 ".$cate[$room[$row["roomid"]]["cat"]]["name"]."-".$room[$row["roomid"]]["name"]." ".$row["date"]." 第".$row["class"]."節",true);
	}else if(@$_POST['valid']=="false"){
		$query=new query;
		$query->table = "borrow";
		$query->where = array(
			array("hash",$_POST["hash"])
		);
		DELETE($query);
		addmsgbox("info","已拒絕申請 ".$cate[$room[$row["roomid"]]["cat"]]["name"]."-".$room[$row["roomid"]]["name"]." ".$row["date"]." 第".$row["class"]."節",true);
	}
}
?>
<head>
<?php
include_once("../res/comhead.php");
?>
<title>借用審核-臺南一中教室借用管理系統</title>
</head>
<body Marginwidth="-1" Marginheight="-1" Topmargin="0" Leftmargin="0">
<?php
	include_once("../res/header.php");
	if(checkroompermission($login["id"])){
?>
	<div class="col-md-offset-3 col-md-6">
		<div style="display:none">
			<form method="post" id="validborrow">
				<input name="hash" type="hidden" id="hash">
				<input name="valid" type="hidden" id="valid">
			</form>
		</div>
		<h2>借用審核</h2>
		<table width="0" border="0" cellspacing="10" cellpadding="0" class="table">
		<tr>
			<th>姓名</th>
			<th>教室</th>
			<th>日期</th>
			<th>課堂</th>
			<th>審核</th>
		</tr>
		<?php
		$query=new query;
		$query->table = "borrow";
		$query->column = array("*");
		$query->where = array(
			array("valid","0"),
			array("date",date("Y-m-d"),">=")
		);
		$query->order = array(
			array("date"),
			array("class"),
			array("roomid")
		);
		$row = SELECT($query);
		$noborrow=true;
		foreach ($row as $borrow) {
			if(!checkroompermission($login["id"],$borrow["roomid"]))continue;
			$noborrow=false;
		?>
		<tr>
			<td><?php echo $acct[$borrow["userid"]]["name"]; ?></td>
			<td><?php echo $cate[$room[$borrow["roomid"]]["cat"]]["name"]."-".$room[$borrow["roomid"]]["name"]; ?></td>
			<td><?php echo $borrow["date"]; ?></td>
			<td><?php echo $borrow["class"]; ?></td>
			<td>
			
			<button name="input" type="button" class="btn btn-success" onClick="if(!confirm('確認允許?'))return false;hash.value='<?php echo $borrow["hash"]; ?>';valid.value='true';validborrow.submit();" >
				<span class="glyphicon glyphicon-ok"></span>
				允許 
			</button>
			<button name="input" type="button" class="btn btn-danger" onClick="if(!confirm('確認拒絕?'))return false;hash.value='<?php echo $borrow["hash"]; ?>';valid.value='false';validborrow.submit();" >
				<span class="glyphicon glyphicon-remove"></span>
				拒絕 
		</tr>
		<?php
		}
		if($noborrow){
		?>
		<tr>
			<td colspan="5" align="center">無任何審核</td>
		</tr>
		<?php
		}
		?>
		</table>
	</div>
<?php
	}
	include("../res/footer.php");
?>
</body>
</html>