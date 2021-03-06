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
$data=checklogin();
$powername=array("封禁","使用者","管理員");
if($data==false)header("Location: ../login/?from=manageuser");
else if($data["power"]<=1){
	addmsgbox("danger","你沒有權限");
	?><script>setTimeout(function(){location="../home";},1000);</script><?php
}else if(isset($_POST['suser'])){
	$row = getoneacct($_POST['suser']);
	if($row!==null){
		addmsgbox("warning","已經有人註冊此帳號");
	}else if($_POST["suser"]==""){
		addmsgbox("warning","帳號為空");
	}else if($_POST["spwd"]!=$_POST["spwd2"]){
		addmsgbox("warning","密碼不相符");
	}else if(preg_match("/\s/", $_POST["spwd"])){
		addmsgbox("warning","密碼不可有空白");
	}else if($_POST["sname"]==""){
		addmsgbox("warning","姓名為空");
	}else if($_POST["semail"]!=""&&!preg_match("/^[_a-z0-9-]+([.][_a-z0-9-]+)*@[a-z0-9-]+([.][a-z0-9-]+)*$/",$_POST["semail"])){
		addmsgbox("warning","郵件位址不正確");
	}else{
		$newid=getrandommd5();
		if($_POST["spwd"]!="")$_POST["spwd"]=@crypt($_POST["spwd"]);
		$query=new query;
		$query->table ="account";
		$query->value = array(
			array("id",$newid),
			array("user",$_POST["suser"]),
			array("pwd",$_POST["spwd"]),
			array("email",$_POST["semail"]),
			array("name",$_POST["sname"])
		);
		INSERT($query);
		addmsgbox("success","新增成功");
	}
}else if(isset($_POST["acctdelid"])){
	$row = getoneacct($_POST["acctdelid"]);
	if($data["id"]==$_POST["acctdelid"]){
		addmsgbox("warning","無法刪除自己的帳戶");
	}else{
		$row = getoneacct($_POST['acctdelid']);
		if($row["power"]>$data["power"]){
			addmsgbox("warning","無法刪除比自己權限高的帳戶");
		}else {
			$query=new query;
			$query->table = "account";
			$query->where = array(
				array("id",$_POST["acctdelid"])
			);
			DELETE($query);
			$query=new query;
			$query->table = "roomlist";
			$query->value = array(
				array("admin","")
			);
			$query->where = array(
				array("admin",$_POST["acctdelid"])
			);
			UPDATE($query);
			addmsgbox("info","已刪除帳戶 ".$row["name"]);
		}
	}
}
$acct=getallacct();
?>
<head>
<?php
include_once("../res/comhead.php");
?>
<title>使用者管理-<?php echo $cfg['site']['name']; ?></title>
</head>
<body Marginwidth="-1" Marginheight="-1" Topmargin="0" Leftmargin="0">
<script>
var acct=<?php echo json_encode($acct); ?>;
</script>
<?php
include_once("../res/header.php");
if($data["power"]>=2){
?>
<div class="row">
	<div class="col-md-4">
		<h2>新增使用者</h2>
		<form method="post">
			<div class="input-group">
				<span class="input-group-addon">帳號</span>
				<input class="form-control" name="suser" type="text" id="suser" maxlength="32" placeholder="最長32字" required>
				<span class="input-group-addon glyphicon glyphicon-user"></span>
			</div>
			<div class="input-group">
				<span class="input-group-addon">密碼</span>
				<input class="form-control" name="spwd" type="password" id="spwd" placeholder="若使用TNFSH登入留空">
				<span class="input-group-addon glyphicon glyphicon-asterisk"></span>
			</div>
			<div class="input-group">
				<span class="input-group-addon">確認</span>
				<input class="form-control" name="spwd2" type="password" id="spwd2" placeholder="與密碼相符">
				<span class="input-group-addon glyphicon glyphicon-asterisk"></span>
			</div>
			<div class="input-group">
				<span class="input-group-addon">姓名</span>
				<input class="form-control" name="sname" type="text" id="sname" maxlength="32" placeholder="最長32字" required>
				<span class="input-group-addon glyphicon glyphicon-user"></span>
			</div>
			<div class="input-group">
				<span class="input-group-addon">郵件</span>
				<input class="form-control" name="semail" type="email" id="semail" maxlength="64">
				<span class="input-group-addon glyphicon glyphicon-envelope"></span>
			</div>
			<button name="input" type="submit" class="btn btn-success">
				<span class="glyphicon glyphicon-plus"></span>
				新增 
			</button>
			</table>
		</form>
	</div>
	<div class="col-md-8">
		<h2>使用者管理</h2>
		<div style="display:none">
			<form method="post" id="acctdel">
				<input name="acctdelid" type="hidden" id="acctdelid">
			</form>
		</div>
		<div class="table-responsive">
		<script>
			function checkdelacct(id){
				if(!confirm('確認刪除?'))return false;
				acctdelid.value=id;
				acctdel.submit();
			}
		</script>
		<table cellspacing="0" cellpadding="2" class="table table-hover table-condensed">
		<tr>
			<th>帳號</th>
			<th>姓名</th>
			<th>Email</th>
			<th>權限</th>
			<th>刪除</th>
		</tr>
		<?php
		$acct = getallacct();
		foreach ($acct as $accttemp) {
			?>
			<tr>
				<td><a href="../user/?id=<?php echo $accttemp["id"]; ?>"><?php echo het($accttemp["user"]); ?></a></td>
				<td><?php echo het($accttemp["name"]); ?></td>
				<td><?php echo $accttemp["email"]; ?></td>
				<td><?php echo $powername[$accttemp["power"]]; ?></td>
				<td>
					<button name="input" type="button" class="btn btn-danger" onClick="checkdelacct('<?php echo $accttemp["id"]; ?>');">
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
<?php
	}
	include("../res/footer.php");
?>
</body>
</html>