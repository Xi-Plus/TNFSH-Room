<!DOCTYPE html>
<html lang="zh-Hant-TW">
<?php
date_default_timezone_set("Asia/Taipei");
include_once("../func/checkpermission.php");
include_once("../func/sql.php");
include_once("../func/common.php");
include_once("../func/data.php");
$data=checklogin();
?>
<head>
<?php
include_once("../res/comhead.php");
?>
<title>最近預約-臺南一中場地預約管理系統</title>
</head>
<body topmargin="0" leftmargin="0" bottommargin="0">
<?php
include_once("../res/header.php");
?>
<div class="row">
	<div class="col-md-offset-1 col-md-10">
		<h2>最近預約</h2>
		<table width="0" border="0" cellspacing="10" cellpadding="0" class="table">
		<tr>
			<th>姓名</th>
			<th>場地</th>
			<th>日期</th>
			<th>課堂</th>
			<th>審核</th>
			<th>訊息</th>
			<th>管理</th>
		</tr>
		<?php
		$room=getallroom();
		$cate=getallcate();
		$acct=getallacct();
		$query=new query;
		$query->table = "borrow";
		$query->column = array("*");
		$query->where = array(
			array("date",date("Y-m-d"),">=")
		);
		$query->order = array(
			array("updatetime","DESC")
		);
		$query->limit = array(0,10);
		$row = SELECT($query);
		$noborrow=true;
		foreach ($row as $borrow) {
			$noborrow=false;
		?>
		<tr>
			<?php if($data["power"]>=2){ ?>
			<td><a href="../user/?id=<?php echo $borrow["userid"] ?>"><?php echo $acct[$borrow["userid"]]["name"]; ?></a></td>
			<?php }else { ?>
			<td><?php echo $acct[$borrow["userid"]]["name"]; ?></td>
			<?php } ?>
			<td><a href="../search/?roomid=<?php echo $borrow["roomid"]; ?>"><?php echo $cate[$room[$borrow["roomid"]]["cate"]]["name"]."-".$room[$borrow["roomid"]]["name"]; ?></a></td>
			<td><?php echo $borrow["date"]; ?></td>
			<td><?php echo $borrow["class"]; ?></td>
			<?php if($borrow["valid"]==1){ ?>
			<td>允許</td>
			<?php }else if($borrow["valid"]==-1){ ?>
			<td>拒絕</td>
			<?php }else if($data["power"]>=2){ ?>
			<td><a href="../validborrow">審核中</a></td>
			<?php }else { ?>
			<td>審核中</td>
			<?php } ?>
			<td><?php echo $borrow["message"]; ?></td>
			<td><a href="../manageborrow/?hash=<?php echo $borrow["hash"] ?>">管理</a></td>
		</tr>
		<?php
		}
		if($noborrow){
		?>
		<tr>
			<td colspan="5" align="center">無任何預約</td>
		</tr>
		<?php
		}
		?>
		</table>
	</div>
</div>
<?php
	include("../res/footer.php");
?>
</body>
</html>