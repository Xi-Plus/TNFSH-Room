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
if($login===false)header("Location: ../login/?from=validborrow");
else if(!checkroompermission($login["id"])){
	addmsgbox("danger","你沒有權限");
	?><script>setTimeout(function(){locateion="../home";},1000);</script><?php
}
$room=getallroom();
$cate=getallcate();
$acct=getallacct();
if(isset($_POST['valid'])){
	$row=getoneborrow($_POST["borrow"][0]);
	if($row===null){
	}else if(!checkroompermission($login["id"],$row["roomid"])){
		addmsgbox("danger","你沒有權限");
	}else {
		if($_POST['valid']=="true"){
			$vaild=1;
			$message="已允許申請";
			$megboxtype="success";
		}else if($_POST['valid']=="false"){
			$vaild=-1;
			$message="已拒絕申請";
			$megboxtype="info";
		}
		foreach ($_POST['borrow'] as $hash){
			$row=getoneborrow($hash);
			$query=new query;
			$query->table = "borrow";
			$query->value = array(
				array("message",$_POST["message"]),
				array("valid",$vaild),
				array("updatetime",date("Y-m-d H:i:s"))
			);
			$query->where = array(
				array("hash",$hash)
			);
			UPDATE($query);
			$message.="<br>".$cate[$room[$row["roomid"]]["cate"]]["name"]."-".$room[$row["roomid"]]["name"]." ".$row["date"]." 第".$row["class"]."節";
		}
		$message.="<br>訊息：".$_POST["message"];
		addmsgbox($megboxtype,$message,true);
	}
}
?>
<head>
<?php
include_once("../res/comhead.php");
?>
<title>預約審核-<?php echo $cfg['site']['name']; ?></title>
</head>
<body Marginwidth="-1" Marginheight="-1" Topmargin="0" Leftmargin="0">
<?php
	include_once("../res/header.php");
	if(checkroompermission($login["id"])){
?>
	<div class="col-md-offset-3 col-md-6">
		<form method="post">
			<h2>預約審核</h2>
			<table width="0" border="0" cellspacing="10" cellpadding="0" class="table">
			<tr>
				<th>姓名</th>
				<th>場地</th>
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
				<td><?php echo $cate[$room[$borrow["roomid"]]["cate"]]["name"]."-".$room[$borrow["roomid"]]["name"]; ?></td>
				<td><?php echo $borrow["date"]; ?></td>
				<td><?php echo $period[$borrow["class"]]; ?></td>
				<td>
				<input type="checkbox" name="borrow[]" value="<?php echo $borrow["hash"]; ?>">
				<!--
				<button name="input" type="button" class="btn btn-success" onClick="checkvaildborrow('<?php echo $borrow["hash"]; ?>',true);" >
					<span class="glyphicon glyphicon-ok"></span>
					允許
				</button>
				<button name="input" type="button" class="btn btn-danger" onClick="checkvaildborrow('<?php echo $borrow["hash"]; ?>',false);" >
					<span class="glyphicon glyphicon-remove"></span>
					拒絕
				</button>
				-->
			</tr>
			<?php
			}
			if($noborrow){
			?>
			<tr>
				<td colspan="5" align="center">無任何審核</td>
			</tr>
			<?php
			}else {
			?>
			<tr>
				<td colspan="5" align="center">
					<div class="input-group">
						<span class="input-group-addon">訊息</span>
						<input class="form-control" name="message" type="text" size="80">
						<span class="input-group-addon glyphicon glyphicon-info-sign"></span>
					</div>
					<button name="valid" type="submit" class="btn btn-success" value="true">
						<span class="glyphicon glyphicon-ok"></span>
						允許
					</button>
					<button name="valid" type="submit" class="btn btn-danger" value="false">
						<span class="glyphicon glyphicon-remove"></span>
						拒絕
					</button>
				</td>
			</tr>
			<?php
			}
			?>
			</table>
		</form>
	</div>
<?php
	}
	include("../res/footer.php");
?>
</body>
</html>