<!DOCTYPE html>
<html lang="zh-Hant-TW">
<?php
date_default_timezone_set("Asia/Taipei");
include_once("../config/config.php");
include_once("../func/sql.php");
include_once("../func/common.php");
include_once("../func/checkpermission.php");
include_once("../func/consolelog.php");
include_once("../func/data.php");
include_once("../func/msgbox.php");
$roomid=@$_GET["roomid"];
$login=checklogin();
if (!$login) header("Location: ../login/");
$room = getoneroom($roomid);
$period=periodname();
$cate=getallcate();
$show = true;
if (!isset($roomid)) {
	addmsgbox("danger","必須提供場地ID");
	$show = false;
} else if(is_null($room)){
	addmsgbox("danger","查無場地");
	$show = false;
} else if(!checkroompermission($login["id"], $roomid)) {
	addmsgbox("danger","您沒有權限");
	$show = false;
}
if (isset($_POST["edit"])) {
	$periodlist = [];
	$periodlistname = [];
	if (isset($_POST["borrow_accept_period"])) {
		foreach ($_POST["borrow_accept_period"] as $key => $value) {
			$periodlist []= $key;
			$periodlistname []= $period[$key];
		}
	}
	$query=new query;
	$query->table ="roomlist";
	$query->value = array(
		array("borrow_daylimit_min",$_POST["borrow_daylimit_min"]),
		array("borrow_daylimit_max",$_POST["borrow_daylimit_max"]),
		array("borrow_accept_period",json_encode($periodlist)),
		array("default_layout",$_POST["default_layout"])
	);
	$query->where = array(
		array("id",$roomid)
	);
	UPDATE($query);
	addmsgbox("success","已更新借用期限為 ".$_POST["borrow_daylimit_min"]." 天至 ".$_POST["borrow_daylimit_max"]." 天，接受借用時間為".implode("、", $periodlistname).
		"，預設顯示版面為 ".$cfg['text']['layout'][$_POST["default_layout"]]);
}
$room = getoneroom($roomid);
?>
<head>
<?php
include_once("../res/comhead.php");
?>
<title>場地設定-<?php echo $cfg['site']['name']; ?></title>
</head>
<body Marginwidth="-1" Marginheight="-1" Topmargin="0" Leftmargin="0">
<?php
include_once("../res/header.php");
if ($show) {
	
?>
<div class="container">
	<h2>場地設定 <?=$cate[$room["cate"]]["name"]?>-<?=$room["name"]?></h2>
	<form method="post">
		<div class="row">
			<label class="col-sm-3 col-md-2 form-control-label"><i class="fa fa-calendar filtericon" aria-hidden="true"></i> 借用最小期限</label>
			<div class="col-sm-9 col-md-10 form-inline">
				<input class="form-control" name="borrow_daylimit_min" type="number" min="0" value="<?php echo $room['borrow_daylimit_min']; ?>" required>
			</div>
		</div>
		<div class="row">
			<label class="col-sm-3 col-md-2 form-control-label"><i class="fa fa-calendar filtericon" aria-hidden="true"></i> 借用最大期限</label>
			<div class="col-sm-9 col-md-10 form-inline">
				<input class="form-control" name="borrow_daylimit_max" type="number" min="0" value="<?php echo $room['borrow_daylimit_max']; ?>" required>
			</div>
		</div>
		<div class="row">
			<label class="col-sm-3 col-md-2 form-control-label"><i class="fa fa-tags filtericon" aria-hidden="true"></i> 允許借用時間</label>
			<div class="col-sm-9 col-md-10">
				<div class="checkbox">
					<?php
					foreach ($period as $key => $name) {
						?><label class="checkbox-inline">
							<input type="checkbox" name="borrow_accept_period[<?=$key?>]" <?php echo (in_array($key, $room["borrow_accept_period"])?"checked":"") ?>><?=$name?>
						</label><?php
					}
					?>
				</div>
			</div>
		</div>
		<div class="row">
			<label class="col-sm-3 col-md-2 form-control-label"><i class="fa fa-tags filtericon" aria-hidden="true"></i> 預設顯示版面</label>
			<div class="col-sm-9 col-md-10">
				<div class="form-check form-check-inline">
					<?php
					for ($i=1; $i <= 2; $i++) { 
					?>
					<label class="form-check-label">
						<input class="form-check-input" type="radio" name="default_layout" value="<?=$i?>" <?php echo ($room["default_layout"]==$i?"checked":"") ?>><?=$cfg['text']['layout'][$i]?>
					</label>
					<?php
					}
					?>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2">
				<button type="submit" class="btn btn-success" name="edit">
					<span class="glyphicon glyphicon-pencil"></span>
					修改 
				</button>
			</div>
		</div>
	</form>
	<a href="../search/?roomid=<?=$roomid?>">返回到預約查詢</a>
</div>
<?php
}
include("../res/footer.php");
?>
</body>
</html>
