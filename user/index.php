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
if($login==false)header("Location: ../login/?from=user");
else {
$powername=array("封禁","使用者","管理員");
$editid=@$login["id"];
if(@is_numeric($_GET["id"]))$editid=$_GET["id"];
$editdata = getoneacct($editid);
$showdata=true;
if(isset($_POST["sid"])&&$editid!=$_POST["sid"]){
	addmsgbox("danger","有預設資料遭到修改，沒有任何修改動作被執行");
	$showdata=false;
}else if($editdata==null){
	$error="無此ID";
	$showdata=false;
}else{
	if($editid!=$login["id"]&&$login["power"]<=1){
		addmsgbox("warning","你沒有權限更改別人的資料");
		$showdata=false;
	}
	else if($login["power"]<$editdata["power"]){
		addmsgbox("warning","無法更改較高權限的帳戶");
		$showdata=false;
	}
	else{
		if($editid!=$login["id"])addmsgbox("info","注意!你正在更改其他人的資料");
		if(@$_POST['spwd1']!=""){
			if($_POST["spwd1"]!=$_POST["spwd2"]){
				addmsgbox("warning","密碼不符");
			}else if(preg_match("/\s/", $_POST["spwd1"])){
				addmsgbox("waring","密碼不可有空白");
			}else{
				if($_POST['spwd1']!="")$_POST['spwd1']=@crypt($_POST['spwd1']);
				$query=new query;
				$query->table = "account";
				$query->value = array(
					array("pwd",$_POST['spwd1'])
				);
				$query->where = array(
					array("id",$editid)
				);
				UPDATE($query);
				addmsgbox("success","已更新密碼");
			}
		}
		if(@$_POST['sname']!=""&&@$_POST['sname']!=$editdata["name"]){
			$query=new query;
			$query->table = "account";
			$query->value = array(
				array("name",$_POST['sname'])
			);
			$query->where = array(
				array("id",$editid)
			);
			UPDATE($query);
			addmsgbox("success","已更新姓名");
		}
		if(@$_POST['semail']!=""&&@$_POST['semail']!=$editdata["email"]){
			if(!preg_match("/^[_a-z0-9-]+([.][_a-z0-9-]+)*@[a-z0-9-]+([.][a-z0-9-]+)*$/", $_POST["semail"])){
				addmsgbox("warning","郵件位址不正確");
			}else{
				$query=new query;
				$query->table = "account";
				$query->value = array(
					array("email",$_POST['semail'])
				);
				$query->where = array(
					array("id",$editid)
				);
				UPDATE($query);
				addmsgbox("success","已更新郵件");
			}
		}
	}
}
$cate=getallcate();
if(isset($_POST["delhash"])){
	$borrow=getoneborrow($_POST["delhash"]);
	if($borrow===null){
		addmsgbox("warning","查無此預約");
	}else if(checkborrowpermission($_POST["delhash"],$login["id"])){
		$query=new query;
		$query->table = "borrow";
		$query->where = array(
			array("hash",$_POST["delhash"])
		);
		DELETE($query);
		$room=getoneroom($borrow["roomid"]);
		addmsgbox("info","已刪除預約 ".$cate[$room["cate"]]["name"]."-".$room["name"]." 日期 ".$borrow["date"]." 第".$borrow["class"]."節");
	}else {
		addmsgbox("danger","你沒有權限");
	}
}
$room=getallroom();
$editdata = getoneacct($editid);
}
?>
<head>
<?php
include_once("../res/comhead.php");
?>
<title>個人預約查詢-場地預約管理系統</title>
</head>
<body Marginwidth="-1" Marginheight="-1" Topmargin="0" Leftmargin="0">
<?php
include_once("../res/header.php");
if($showdata){
?>
<div class="row">
	<div class="col-md-5">
		<div style="display:none">
			<form method="post" id="delborrow">
				<input name="delhash" type="hidden" id="delhash">
			</form>
		</div>
		<h2>目前預約</h2>
		<script>
			function checkdelborrow(id){
				if(!confirm('確認取消?'))return false;
				delhash.value=id;
				delborrow.submit();
			}
		</script>
		<table width="0" border="0" cellspacing="10" cellpadding="0" class="table">
		<tr>
			<th>分類</td>
			<th>場地</td>
			<th>日期</td>
			<th>課堂</td>
			<th>審核</td>
			<th>管理</td>
		</tr>
		<?php
		$query=new query;
		$query->table = "borrow";
		$query->column = array("*");
		$query->where = array(
			array("userid",$editid),
			array("date",date("Y-m-d"),">=")
		);
		$query->order = array(
			array("date","DESC"),
			array("class"),
			array("roomid")
		);
		$row = SELECT($query);
		$noborrow=true;
		foreach ($row as $borrow) {
			$noborrow=false;
		?>
		<tr>
			<td><?php echo $cate[$room[$borrow["roomid"]]["cate"]]["name"]; ?></td>
			<td><?php echo $room[$borrow["roomid"]]["name"]; ?></td>
			<td><?php echo $borrow["date"]; ?></td>
			<td><?php echo $borrow["class"]; ?></td>
			<td><?php echo ($borrow["valid"]?"通過":"審核中"); ?></td>
			<td>
			<button name="input" type="button" class="btn btn-danger" onClick="checkdelborrow('<?php echo $borrow["hash"]; ?>');">
				<span class="glyphicon glyphicon-trash"></span>
				取消 
			</button>
			</td>
		</tr>
		<?php
		}
		if($noborrow){
		?>
		<tr>
			<td colspan="6" align="center">無任何預約</td>
		</tr>
		<?php
		}
		?>
		</table>
	</div>
	<div class="col-md-4">
		<h2>歷史預約</h2>
		<div id="norecord">
		<button type="button" class="btn btn-default" id="button0" onclick="next(-1);">
			<span class="glyphicon glyphicon-chevron-left"></span>
			前10筆 
		</button>
		<button type="button" class="btn btn-default" id="button1" onclick="next(1);">
			<span class="glyphicon glyphicon-chevron-right"></span>
			後10筆 
		</button>
		<span id="showpage"></span>
		</div>
		<?php
		$query=new query;
		$query->table = "borrow";
		$query->column = array("roomid","date","class","valid");
		$query->where = array(
			array("userid",$editid),
			array("date",date("Y-m-d"),"<"),
			array("valid","1")
		);
		$query->order = array(
			array("date","DESC"),
			array("class"),
			array("roomid")
		);
		$row = SELECT($query);
		$noborrow=true;
		$count=count($row);
		?>
		<script>
			var count=<?php echo (int)(($count+9)/10); ?>;
			var now=0;
			function hide(){
				if(count!=0)document.all["table"+now].style.display="none";
			}
			function show(){
				if(count!=0)document.all["table"+now].style.display="";
			}
			function next(n){
				hide();
				if(n==1){
					if(now<count-1)now++;
				}else if(n==-1){
					if(now>0)now--;
				}
				button0.disabled=false;
				button1.disabled=false;
				if(now==0)button0.disabled=true;
				if(now>=count-1)button1.disabled=true;
				show();
				showpage.innerHTML="第"+(now+1)+"頁/共"+count+"頁";
			}
		</script>
		<?php
		foreach ($row as $i => $borrow){
			if($i%10==0){
		?>
			<div id="table<?php echo (int)($i/10); ?>" style="display:none;">
				<table width="0" border="0" cellspacing="10" cellpadding="0" class="table">
				<tr>
					<th>分類</th>
					<th>場地</th>
					<th>日期</th>
					<th>課堂</th>
				</tr>
				<?php
					}
					$noborrow=false;
				?>
				<tr>
					<td><?php echo $cate[$room[$borrow["roomid"]]["cate"]]["name"]; ?></td>
					<td><?php echo $room[$borrow["roomid"]]["name"]; ?></td>
					<td><?php echo $borrow["date"]; ?></td>
					<td><?php echo $borrow["class"]; ?></td>
				</tr>
				<?php
					if($i==$count-1||$i%10==9){
				?>
				</table>
			</div>
		<?php
			}
		}
		if($noborrow){
		?>
			<table width="0" border="0" cellspacing="10" cellpadding="0" class="table">
			<tr>
				<th>分類</th>
				<th>場地</th>
				<th>日期</th>
				<th>課堂</th>
			</tr>
			<tr>
				<td colspan="4" align="center">無任何預約</td>
			</tr>
			</table>
		<?php
		}
		?>
		<script>
			next();
			if(count==0)norecord.style.display="none";
		</script>
	</div>
	<div class="col-md-3">
		<h2>更新資料</h2>
		<form method="post">
			<input name="sid" type="hidden" id="sid" value="<?php echo $editid;?>">
			<?php
			if(@$editdata["pwd"]!=""){
			?>
			<div class="input-group">
				<span class="input-group-addon">新密碼</span>
				<input class="form-control" name="spwd1" type="password" id="spwd">
				<span class="input-group-addon glyphicon glyphicon-asterisk"></span>
			</div>
			<div class="input-group">
				<span class="input-group-addon">再確認</span>
				<input class="form-control" name="spwd2" type="password" id="spwd2">
				<span class="input-group-addon glyphicon glyphicon-asterisk"></span>
			</div>
			<?php
			}else {
				?>
			<div class="input-group">
				<span class="input-group-addon">密碼</span>
				<input class="form-control" type="text" value="無法在此更改密碼" disabled>
				<span class="input-group-addon glyphicon glyphicon-asterisk"></span>
			</div>
				<?php
			}
			?>
			<div class="input-group">
				<span class="input-group-addon">姓名</span>
				<input class="form-control" name="sname" type="text" id="sname" value="<?php echo het($editdata["name"]);?>" maxlength="32" required>
				<span class="input-group-addon glyphicon glyphicon-user"></span>
			</div>
			<div class="input-group">
				<span class="input-group-addon">郵件</span>
				<input class="form-control" name="semail" type="email" id="semail" value="<?php echo $editdata["email"];?>" maxlength="64">
				<span class="input-group-addon glyphicon glyphicon-envelope"></span>
			</div>
			<button name="input" type="submit" class="btn btn-success">
				<span class="glyphicon glyphicon-pencil"></span>
				更新資料 
			</button>
		</form>
		<br>
		<ul class="list-group">
			<li class="list-group-item list-group-item-info">全域權限</li>
			<li class="list-group-item"><?php echo $powername[$editdata["power"]]; ?></li>
			<li class="list-group-item list-group-item-info">場地管理權限</li>
		<?php
			if($editdata["power"]>=2){ ?><li class="list-group-item">全部</li><?php
			}else {
				$query=new query;
				$query->table = "roomlist";
				$query->column = array("*");
				$query->where = array(
					array("admin",$editid)
				);
				$row = SELECT($query);
				$noadmin=true;
				foreach ($row as $temp) {
					$noadmin=false;
					?><li class="list-group-item"><?php echo $cate[$temp["cate"]]["name"]." ".$temp["name"]."<br>"; ?></li><?php
				}
				if($noadmin){ ?><li class="list-group-item">無</li><?php }
			}
		?>
		</ul>
	</div>
</div>
<?php 
	}
	include("../res/footer.php");
?>
</body>
</html>