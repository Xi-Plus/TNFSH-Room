<!DOCTYPE html>
<html lang="zh-Hant-TW">
<?php
date_default_timezone_set("Asia/Taipei");
include_once("../func/checkpermission.php");
include_once("../func/sql.php");
include_once("../func/common.php");
include_once("../func/data.php");
$data=checklogin();
$period=periodname(true);
?>
<head>
<?php
include_once("../res/comhead.php");
?>
<title>最近預約-<?php echo $cfg['site']['name']; ?></title>
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
			<th>借用</th>
			<th>場地</th>
			<th>月/日-節次</th>
			<th>更新</th>
			<th>審核</th>
			<th>資訊</th>
		</tr>
		<?php
		$room=getallroom();
		$cate=getallcate();
		$acct=getallacct();
		$query=new query;
		$query->table = "borrow";
		$query->column = array("*");
		$query->where = array(
			array("date",date("Y-m-d"),">="),
			array("updatetime",date("Y-m-d H:i:s", time()-86400*7),">=")
		);
		$query->order = array(
			array("updatetime","DESC")
		);
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
			<td><?php
			if (isset($room[$borrow["roomid"]])) {
				?><a href="../search/?roomid=<?php echo $borrow["roomid"]; ?>"><?php echo $room[$borrow["roomid"]]["name"]; ?></a><?php
			} else {
				echo "此場地已被刪除";
			}
			?></td>
			<td><?php echo date("m/d", strtotime($borrow["date"]))."-".$period[$borrow["class"]]; ?></td>
			<td><?php echo timelen(time()-strtotime($borrow["updatetime"]))."前"; ?></td>
			<?php if($borrow["valid"]==1){ ?>
			<td><span class="glyphicon glyphicon-ok" title="允許"></td>
			<?php }else if($borrow["valid"]==-1){ ?>
			<td><span class="glyphicon glyphicon-remove" title="拒絕"></td>
			<?php }else { ?>
			<td><span class="glyphicon glyphicon-question-sign" title="審核中"></td>
			<?php } ?>
			<td>
				<a href="../manageborrow/?hash=<?php echo $borrow["hash"] ?>">資訊</a>
				<?php
				if ($borrow["message"] != "") {
					?><span class="glyphicon glyphicon-comment" title="有訊息"><?php
				}
				?>
			</td>
		</tr>
		<?php
		}
		if($noborrow){
		?>
		<tr>
			<td colspan="7" align="center">無任何預約</td>
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