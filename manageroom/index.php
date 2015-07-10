<!DOCTYPE html>
<html lang="zh-Hant-TW">
<?php
date_default_timezone_set("Asia/Taipei");
include_once("../func/sql.php");
include_once("../func/common.php");
include_once("../func/checkpermission.php");
include_once("../func/consolelog.php");
include_once("../func/data.php");
include_once("../func/msgbox.php");
$error="";
$message="";
$data=checklogin();
if($data==false)header("Locateion: ../login/?from=manageroom");
else if($data["power"]<=1){
	addmsgbox("danger","你沒有權限");
	?><script>setTimeout(function(){locateion="../home";},1000);</script><?php
}
else if(isset($_POST["catedelid"])){
	$row=getonecate($_POST['catedelid']);
	$query=new query;
	$query->table = "category";
	$query->where = array(
		array("id",$_POST["catedelid"])
	);
	DELETE($query);
	addmsgbox("info","已刪除分類 名稱為 ".$row["name"]);
}
else if(isset($_POST["addcate"])){
	if($_POST["name"]=="")addmsgbox("warning","名稱為空");
	else{
		$newid=getrandommd5();
		$query=new query;
		$query->table ="category";
		$query->value = array(
			array("id",$newid),
			array("name",$_POST["name"])
		);
		INSERT($query);
		addmsgbox("success","已增加分類 名稱為 ".$_POST["name"]);
	}
}
else if(isset($_POST["editcate"])){
	if($_POST["name"]=="")addmsgbox("warning","名稱為空");
	else {
		$query=new query;
		$query->table = "category";
		$query->value = array(
			array("name",$_POST["name"])
		);
		$query->where = array(
			array("id",$_POST["id"])
		);
		UPDATE($query);
		addmsgbox("success","已修改分類 名稱為 ".$_POST["name"]);
	}
}
$cate=getallcate();
$acct=getallacct();
if(isset($_POST["roomdelid"])){
	$row = getoneroom($_POST["roomdelid"]);
	$query=new query;
	$query->table = "roomlist";
	$query->where = array(
		array("id",$_POST["roomdelid"])
	);
	DELETE($query);
	addmsgbox("info","已刪除教室 名稱為 ".$row["name"]);
}
else if(isset($_POST["addroom"])){
	if($_POST["name"]=="")addmsgbox("warning","名稱為空");
	else if($_POST["cate"]=="")addmsgbox("warning","分類為空");
	else{
		$newid=getrandommd5();
		$query=new query;
		$query->table ="roomlist";
		$query->value = array(
			array("id",$newid),
			array("name",$_POST["name"]),
			array("cate",$_POST["cate"]),
			array("admin",$_POST["admin"])
		);
		INSERT($query);
		addmsgbox("success","已增加教室 名稱為 ".$_POST["name"]." 分類為 ".$cate[$_POST["cate"]]["name"]." 管理者為 ".($_POST["admin"]==""?"無":$acct[$_POST["admin"]]["name"]));
	}
}
else if(isset($_POST["editroom"])){
	if($_POST["name"]=="")addmsgbox("warning","名稱為空");
	else {
		$query=new query;
		$query->table = "roomlist";
		$query->value = array(
			array("name",$_POST["name"]),
			array("cate",$_POST["cate"]),
			array("admin",$_POST["admin"])
		);
		$query->where = array(
			array("id",$_POST["id"])
		);
		UPDATE($query);
		$row=getoneroom($_POST['id']);
		addmsgbox("success","已修改教室 名稱為 ".$row["name"]." 分類為 ".$cate[$row["cate"]]["name"]." 管理者為 ".($row["admin"]==""?"無":$acct[$row["admin"]]["name"]));
	}
}
$room=getallroom();
?>
<head>
<meta charset="UTF-8">
<?php
include_once("../res/comhead.php");
?>
<title>教室管理-臺南一中教室借用管理系統</title>
</head>
<body Marginwidth="-1" Marginheight="-1" Topmargin="0" Leftmargin="0">
<script>
var cate=<?php echo json_encode($cate); ?>;
var acct=<?php echo json_encode($acct); ?>;
var room=<?php echo json_encode($room); ?>;
</script>
<?php
include_once("../res/header.php");
if($data["power"]>=2){
?>
<div class="row">
	<div class="col-md-6">
		<h2>分類管理</h2>
		<div class="row">
			<div class="col-sm-6">
				<form method="post">
					<input name="addcate" type="hidden" value="">
					<h2>新增</h2>
					<div class="input-group">
						<span class="input-group-addon">名稱</span>
						<input class="form-control" name="name" type="text" id="name" required>
						<span class="input-group-addon glyphicon glyphicon-pencil"></span>
					</div>
					<button name="input" type="submit" class="btn btn-success">
						<span class="glyphicon glyphicon-plus"></span>
						新增 
					</button>
				</form>
			</div>
			<div class="col-sm-6">
				<form method="post">
					<input name="editcate" type="hidden" value="">
					<h2>修改</h2>
					<div class="input-group">
						<span class="input-group-addon">原始</span>
						<select class="form-control" name="id" id="editcate" onChange="editcatechange(this.value);">
						<?php
							foreach($cate as $i => $catetemp){
						?>
							<option value="<?php echo $i; ?>"><?php echo $catetemp["name"]; ?></option>
						<?php
							}
						?>
						</select>
						<span class="input-group-addon glyphicon glyphicon-tag"></span>
					</div>
					<div class="input-group">
						<span class="input-group-addon">名稱</span>
						<input class="form-control" name="name" type="text" id="editcatename" required>
						<span class="input-group-addon glyphicon glyphicon-pencil"></span>
					</div>
					<script>
					function editcatechange(id){
						editcatename.value=cate[id]["name"];
					}
					editcatechange(editcate.value);
					</script>
					<button name="input" type="submit" class="btn btn-success">
						<span class="glyphicon glyphicon-pencil"></span>
						修改 
					</button>
				</form>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<div class="table-responsive">
				<table border="1" cellspacing="0" cellpadding="2" class="table table-hover table-condensed">
				<div style="display:none">
					<form method="post" id="catedel">
						<input name="catedelid" type="hidden" id="catedelid">
					</form>
				</div>
				<tr>
					<th>名稱</th>
					<th>刪除</th>
				</tr>
				<?php
				foreach($cate as $i => $catetemp){
				?>
				<tr>
					<td><?php echo $catetemp["name"]; ?></td>
					<td>
						<button name="input" type="button" class="btn btn-danger" onClick="if(!confirm('確認刪除?'))return false;catedelid.value='<?php echo $i; ?>';catedel.submit();" >
						<span class="glyphicon glyphicon-trash"></span>
						刪除 
						</button>
					</td>
				</tr>
				<?php
				}
				?>
				</table>
				</div>
			</div>
		</div>
	</div>
	<div class="col-md-6">
		<h2>教室管理</h2>
		<div class="row">
			<div class="col-sm-6">
				<form method="post">
					<input name="addroom" type="hidden" value="true">
					<h2>新增</h2>
					<div class="input-group">
						<span class="input-group-addon">名稱</span>
						<input class="form-control" name="name" type="text" required>
						<span class="input-group-addon glyphicon glyphicon-pencil"></span>
					</div>
					<div class="input-group">
						<span class="input-group-addon">分類</span>
						<select class="form-control" name="cate">
						<?php
							foreach($cate as $i => $catetemp){
						?>
							<option value="<?php echo $i; ?>"><?php echo $catetemp["name"]; ?></option>
						<?php
							}
						?>
						</select>
						<span class="input-group-addon glyphicon glyphicon-tag"></span>
					</div>
					<div class="input-group">
						<span class="input-group-addon">管理員</span>
						<select class="form-control" name="admin">
							<option value="">無</option>
						<?php
							foreach($acct as $i => $accttemp){
						?>
							<option value="<?php echo $i; ?>"><?php echo $accttemp["name"]; ?></option>
						<?php
							}
						?>
						</select>
						<span class="input-group-addon glyphicon glyphicon-user"></span>
					</div>
					<button name="input" type="submit" class="btn btn-success">
						<span class="glyphicon glyphicon-plus"></span>
						新增 
					</button>
				</form>
			</div>
			<div class="col-sm-6">
				<form method="post">
					<input name="editroom" type="hidden" value="true">
					<h2>修改</h2>
					<div class="input-group">
						<span class="input-group-addon">原始</span>
						<select class="form-control" name="id" id="editroom" onChange="editroomchange(this.value);">
						<?php
							foreach($room as $i => $roomtemp){
						?>
							<option value="<?php echo $i; ?>"><?php echo $roomtemp["name"]; ?></option>
						<?php
							}
						?>
						</select>
						<span class="input-group-addon glyphicon glyphicon-home"></span>
					</div>
					<div class="input-group">
						<span class="input-group-addon">名稱</span>
						<input class="form-control" name="name" type="text" id="editroomname" required>
						<span class="input-group-addon glyphicon glyphicon-pencil"></span>
					</div>
					<div class="input-group">
						<span class="input-group-addon">分類</span>
						<select class="form-control" name="cate" id="editroomcate">
						<?php
							foreach($cate as $i => $catetemp){
						?>
							<option value="<?php echo $i; ?>"><?php echo $catetemp["name"]; ?></option>
						<?php
							}
						?>
						</select>
						<span class="input-group-addon glyphicon glyphicon-tag"></span>
					</div>
					<div class="input-group">
						<span class="input-group-addon">管理員</span>
						<select class="form-control" name="admin" id="editroomadmin">
							<option value="">無</option>
						<?php
							foreach($acct as $i => $accttemp){
						?>
							<option value="<?php echo $i; ?>" <?php echo ($roomtemp["admin"]==$i?"selected":"")?>><?php echo $accttemp["name"]; ?></option>
						<?php
							}
						?>
						</select>
						<span class="input-group-addon glyphicon glyphicon-user"></span>
					</div>
					<script>
					function getindexbyvalue(array,value){
						for(var i=0;i<array.length;i++){
							if(array[i].value==value)return i;
						}
						return -1;
					}
					function editroomchange(id){
						editroomname.value=room[id]["name"];
						editroomcate.selectedIndex=getindexbyvalue(editroomcate,room[id]["cate"]);
						editroomadmin.selectedIndex=getindexbyvalue(editroomadmin,room[id]["admin"]);
					}
					editroomchange(editroom.value);
					</script>
					<button name="input" type="submit" class="btn btn-success">
						<span class="glyphicon glyphicon-pencil"></span>
						修改 
					</button>
				</form>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<div style="display:none">
					<form method="post" id="roomdel">
						<input name="roomdelid" type="hidden" id="roomdelid">
					</form>
				</div>
				<div class="table-responsive">
				<table border="1" cellspacing="0" cellpadding="2" class="table table-hover table-condensed">
				<tr>
					<th>分類</th>
					<th>名稱</th>
					<th>管理員</th>
					<th>刪除</th>
				</tr>
				<?php
				foreach ($room as $roomtemp) {
				?>
					<tr>
						<td><?php echo @$cate[$roomtemp["cate"]]["name"]; ?></td>
						<td><a href="../search/?roomid=<?php echo $roomtemp["id"]; ?>"><?php echo htmlspecialchars($roomtemp["name"],ENT_QUOTES); ?></a></td>
						<td><?php echo ($roomtemp["admin"]==""?"無":$acct[$roomtemp["admin"]]["name"]); ?></td>
						<td>
							<button name="input" type="button" class="btn btn-danger" onClick="if(!confirm('確認刪除?'))return false;roomdelid.value='<?php echo $roomtemp["id"]; ?>';roomdel.submit();">
								<span class="glyphicon glyphicon-trash"></span>
								刪除 
							</button>
						</td>
					</tr>
					<?php
				}
				?>
			</table>
				</div>
			</div>
		</div>
	</div>
</div>
<?php
	}
	include("../res/footer.php");
?>
</body>
</html>