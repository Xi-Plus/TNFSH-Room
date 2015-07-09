<!DOCTYPE html>
<html lang="zh-Hant-TW">
<?php
date_default_timezone_set("Asia/Taipei");
include_once("../func/checkpermission.php");
include_once("../func/sql.php");
include_once("../func/common.php");
include_once("../func/data.php");
?>
<head>
<?php
include_once("../res/comhead.php");
?>
<title>最近借用-臺南一中教室借用管理系統</title>
</head>
<body topmargin="0" leftmargin="0" bottommargin="0">
<?php
include_once("../res/header.php");
?>
<div class="row">
	<div class="col-md-offset-3 col-md-6">
		<h2>最近借用</h2>
		<table width="0" border="0" cellspacing="10" cellpadding="0" class="table">
		<tr>
			<th>姓名</th>
			<th>教室</th>
			<th>日期</th>
			<th>課堂</th>
			<th>審核</th>
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
			<td><?php echo $acct[$borrow["userid"]]["name"]; ?></td>
			<td><?php echo $cate[$room[$borrow["roomid"]]["cat"]]["name"]."-".$room[$borrow["roomid"]]["name"]; ?></td>
			<td><?php echo $borrow["date"]; ?></td>
			<td><?php echo $borrow["class"]; ?></td>
			<td><?php echo ($borrow["valid"]?"通過":"審核中"); ?></td>
		</tr>
		<?php
		}
		if($noborrow){
		?>
		<tr>
			<td colspan="5" align="center">無任何借用</td>
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