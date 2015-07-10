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
$login=checklogin();
$cate=getallcate();
if(isset($_POST["borrowone"])){
	if($login==false)header("Location: ../login/");
	if($_POST["borrowdate"]<date("Y-m-d")){
		addmsgbox("warning","日期必須是今天以後");
	}else {
		$query=new query;
		$query->table ="borrow";
		$query->where = array(
			array("userid",$login["id"]),
			array("roomid",$_POST["borrowid"]),
			array("date",$_POST["borrowdate"]),
			array("class",$_POST["borrowclass"])
		);
		$row=fetchone(SELECT($query));
		if($row===null){
			$query=new query;
			$query->table ="borrow";
			$query->value = array(
				array("userid",$login["id"]),
				array("roomid",$_POST["borrowid"]),
				array("date",$_POST["borrowdate"]),
				array("class",$_POST["borrowclass"]),
				array("updatetime",date("Y-m-d H:i:s")),
				array("hash",md5(uniqid(rand(),true)))
			);
			if(checkroompermission($login["id"],$_POST["borrowid"]))$query->value[]=array("valid","1");
			INSERT($query);
			$row = getoneroom($_POST['borrowid']);
			addmsgbox("success","已借用 ".$_POST["borrowdate"]." 第".$_POST["borrowclass"]."節 ".$row["name"]);
		}else {
			addmsgbox("warning","已有人借用");
		}
	}
}else if(isset($_POST["borrowadmin"])){
	if($login==false)header("Location: ../login/");
	else if(!checkroompermission($login["id"],$_POST["borrowid"])){
		addmsgbox("danger","你沒有權限");
	}else if($_POST["startdate"]<date("Y-m-d")){
		addmsgbox("warning","起始日期必須是今天以後");
	}else {
		$starttime=strtotime($_POST["startdate"]);
		if($_POST["borrowweek"]>=date("w",$starttime)){
			$starttime+=($_POST["borrowweek"]-date("w",$starttime))*86400;
		}else {
			$starttime+=($_POST["borrowweek"]+7-date("w",$starttime))*86400;
		}
		$endtime=strtotime($_POST["enddate"]);
		if(date("w",$endtime)>=$_POST["borrowweek"]){
			$endtime-=(date("w",$endtime)-$_POST["borrowweek"])*86400;
		}else {
			$endtime-=(date("w",$endtime)+7-$_POST["borrowweek"])*86400;
		}
		date("Y-m-d",$starttime);
		date("Y-m-d",$endtime);
		if($_POST["borrowtype"]=="borrow"){
			for($i=$starttime;$i<=$endtime;$i+=86400*7){
				$query=new query;
				$query->table = "borrow";
				$query->where = array(
					array("roomid",$_POST["borrowid"]),
					array("date",date("Y-m-d",$i)),
					array("class",$_POST["borrowclass"])
				);
				$row = fetchone(SELECT($query));
				if($row!=null){
					$query=new query;
					$query->table = "borrow";
					$query->where = array(
						array("roomid",$_POST["borrowid"]),
						array("date",date("Y-m-d",$i)),
						array("class",$_POST["borrowclass"])
					);
					DELETE($query);
					$room=getoneroom($row["roomid"]);
					addmsgbox("info","已刪除借用 ".$cate[$row["roomid"]]["name"]."-".$room["name"]." 日期 ".$row["date"]." 第".$row["class"]."節<br>");
				}
				$query=new query;
				$query->table ="borrow";
				$query->value = array(
					array("userid",$login["id"]),
					array("roomid",$_POST["borrowid"]),
					array("date",date("Y-m-d",$i)),
					array("class",$_POST["borrowclass"]),
					array("updatetime",date("Y-m-d H:i:s")),
					array("hash",md5(uniqid(rand(),true))),
					array("valid","1")
				);
				INSERT($query);
			}
			$row = getoneroom($_POST['borrowid']);
			addmsgbox("success","已借用 ".date("Y-m-d",$starttime)." 至 ".date("Y-m-d",$endtime)." 星期".$_POST["borrowweek"]." 第".$_POST["borrowclass"]."節 ".$row["name"]);
		}else if($_POST["borrowtype"]=="delete"){
			for($i=$starttime;$i<=$endtime;$i+=86400*7){
				$query=new query;
				$query->table = "borrow";
				$query->where = array(
					array("roomid",$_POST["borrowid"]),
					array("date",date("Y-m-d",$i)),
					array("class",$_POST["borrowclass"])
				);
				DELETE($query);
			}
			$query=new query;
			$query->table = "roomlist";
			$query->column = array("name");
			$query->where = array(
				array("id",$_POST['borrowid'])
			);
			$query->limit = array(0,1);
			$row = fetchone(SELECT($query));
			addmsgbox("info","已刪除 ".date("Y-m-d",$starttime)." 至 ".date("Y-m-d",$endtime)." 星期".$_POST["borrowweek"]." 第".$_POST["borrowclass"]."節 ".$row["name"]);
		}
	}
}
if(isset($_POST["delhash"])){
	$query=new query;
	$query->table = "borrow";
	$query->column = array("*");
	$query->where = array(
		array("hash",$_POST["delhash"])
	);
	$borrow=fetchone(SELECT($query));
	if($borrow===null){
		addmsgbox("warning","查無此借用");
	}else if(checkborrorpermission($_POST["delhash"],$login["id"])){
		$query=new query;
		$query->table = "borrow";
		$query->where = array(
			array("hash",$_POST["delhash"])
		);
		DELETE($query);
		$room=getoneroom($borrow["roomid"]);
		addmsgbox("info","已刪除借用 ".$cate[$borrow["roomid"]]["name"]."-".$room["name"]." 日期 ".$borrow["date"]." 第".$borrow["class"]."節");
	}else {
		addmsgbox("danger","你沒有權限");
	}
}
$acct=getallacct();
$date=@(strtotime($_GET["date"])==false?date("Y-m-d"):$_GET["date"]);
$class=@(is_numeric($_GET["class"])?$_GET["class"]:"1");
$roomid=@$_GET["roomid"];
?>
<head>
<?php
include_once("../res/comhead.php");
?>
<title>所有借用查詢-臺南一中教室借用管理系統</title>
</head>
<body Marginwidth="-1" Marginheight="-1" Topmargin="0" Leftmargin="0">
<?php
include_once("../res/header.php");
?>
<div class="row">
	<div class="col-lg-4">
		<h2>搜尋條件</h2>
		<form method="get" id="search">
			<div class="input-group">
				<span class="input-group-addon">日期</span>
				<input class="form-control" name="date" type="date" id="bookid" value="<?php echo $date;?>" onchange="search.submit();">
				<span class="input-group-addon glyphicon glyphicon-calendar"></span>
			</div>
			<div class="input-group">
				<span class="input-group-addon">教室</span>
				<select class="form-control" name="roomid" onchange="if(this.value!='')search.submit();">
					<option value="" id="chooseone">請選取一個</option>
				<?php
					$room=getallroom();
					foreach ($room as $roomtemp) {
				?>
					<option value="<?php echo $roomtemp["id"]; ?>"<?php echo($roomtemp["id"]==$roomid?" selected":""); ?>><?php echo @$cate[$roomtemp["cat"]]["name"]." ".$roomtemp["name"]; ?></option>
				<?php
					}
				?>
				</select>
				<span class="input-group-addon glyphicon glyphicon-home"></span>
			</div>
		</form>
	<?php
	if($roomid&&checkroompermission($login["id"],$roomid)){
	?>
		<h2>管理員借用</h2>
		<form method="post" id="borrowadminform">
			<input name="borrowadmin" type="hidden">
			<input name="borrowid" type="hidden" value="<?php echo $roomid; ?>">
			<input name="borrowtype" type="hidden" id="borrowtype" value="borrow">
			<div class="input-group">
				<span class="input-group-addon">起始日期</span>
				<input class="form-control" name="startdate" type="date" min="<?php echo date("Y-m-d"); ?>" value="<?php echo date("Y-m-d"); ?>" required>
				<span class="input-group-addon glyphicon glyphicon-calendar"></span>
			</div>
			<div class="input-group">
				<span class="input-group-addon">結束日期</span>
				<input class="form-control" name="enddate" type="date" min="<?php echo date("Y-m-d"); ?>" value="<?php echo date("Y-m-d"); ?>" required>
				<span class="input-group-addon glyphicon glyphicon-calendar"></span>
			</div>
			<div class="input-group">
				<span class="input-group-addon">星期</span>
				<select class="form-control" name="borrowweek">
					<option value="0">日</option>
					<option value="1">一</option>
					<option value="2">二</option>
					<option value="3">三</option>
					<option value="4">四</option>
					<option value="5">五</option>
					<option value="6">六</option>
				?>
				</select>
				<span class="input-group-addon glyphicon glyphicon-calendar"></span>
			</div>
			<div class="input-group">
				<span class="input-group-addon">節數</span>
				<input class="form-control" name="borrowclass" type="number" min="1" max="8" required>
				<span class="input-group-addon glyphicon glyphicon-time"></span>
			</div>
			<button name="input" type="submit" class="btn btn-success" onClick="if(!confirm('確認借用?'))return false;borrowtype.value='borrow';">
				<span class="glyphicon glyphicon-shopping-cart"></span>
				借用 
			</button>
			<button name="input" type="submit" class="btn btn-danger" onClick="if(!confirm('確認刪除?'))return false;borrowtype.value='delete';">
				<span class="glyphicon glyphicon-trash"></span>
				刪除 
			</button>
		</form>
	<?php
	}
	?>
	</div>
	<div class="col-lg-8">
		<?php
		if($roomid==""){
			?><h2>請先選取一間教室</h2><?php
		}else {
		?>
			<div style="display:none">
				<form method="post" id="borrow">
					<input name="borrowone" type="hidden">
					<input name="borrowid" type="hidden" id="borrowid">
					<input name="borrowdate" type="hidden" id="borrowdate">
					<input name="borrowclass" type="hidden" id="borrowclass">
				</form>
				<form method="post" id="delborrow">
					<input name="delhash" type="hidden" id="delhash">
				</form>
			</div>
			<h2>搜尋結果</h2>
			<?php
			$firstdate=strtotime($date)-date("w",strtotime($date))*86400;
			$enddate=strtotime($date)-(date("w",strtotime($date))-6)*86400;
			?>
			目前顯示：<?php echo date("Y-m-d",$firstdate); ?>&nbsp;至&nbsp;<?php echo date("Y-m-d",$enddate); ?>&nbsp;<?php echo $cate[$room[$roomid]["cat"]]["name"]." ".$room[$roomid]["name"]; ?>
			
			<div class="table-responsive">
			<table cellspacing="0" cellpadding="5" style="font-size:20px" class="table table-hover table-condensed">
			<tr>
			<th></th>
			<?php
			$query=new query;
			$query->table = "borrow";
			$query->column = array("*");
			$query->where = array(
				array("roomid",$roomid),
				array("date",date("Y-m-d",$firstdate),">="),
				array("date",date("Y-m-d",$enddate),"<=")
			);
			$row = SELECT($query);
			unset($borrow);
			foreach ($row as $temp) {
				$borrow[$temp["date"]][$temp["class"]]=$temp;
			}
			$week=["日","一","二","三","四","五","六"];
			for($d=0;$d<7;$d++){
			?>
				<th><?php echo date("m/d",$firstdate+86400*$d)."(".$week[$d].")"?></th>
			<?php
			}
			?>
			</tr>
			<?php
			for($c=1;$c<=8;$c++){
			?>
				<tr>
				<td>第<?php echo $c; ?>節</td>
				<?php
				for($d=0;$d<7;$d++){
				?>
					<td align="center"><?php
					if(isset($borrow[date("Y-m-d",$firstdate+86400*$d)][$c])){
						if(checkborrorpermission($borrow[date("Y-m-d",$firstdate+86400*$d)][$c]["hash"],$login["id"])&&date("Y-m-d",$firstdate+86400*$d)>=date("Y-m-d")){
						?><a href="" onClick="if(!confirm('確認取消?'))return false;delhash.value='<?php echo $borrow[date("Y-m-d",$firstdate+86400*$d)][$c]["hash"]; ?>';delborrow.submit();return false;"><?php echo $acct[$borrow[date("Y-m-d",$firstdate+86400*$d)][$c]["userid"]]["name"]; ?></a><?php
						}else {
							echo @$acct[$borrow[date("Y-m-d",$firstdate+86400*$d)][$c]["userid"]]["name"];
						}
					}else {
						if($login!=false){
						?>
							<button name="input" type="submit" class="btn btn-success" value="借用" onClick="if(!confirm('確認借用?'))return false;borrowid.value='<?php echo $roomid; ?>';borrowdate.value='<?php echo date("Y-m-d",$firstdate+86400*$d); ?>';borrowclass.value='<?php echo $c; ?>';borrow.submit();" <?php echo ($firstdate+86400*$d<time()-86400?"disabled":"")?>>
								<span class="glyphicon glyphicon-shopping-cart"></span>
								借用 
							</button>
						<?php
						}
					}
				}
				?>
				</tr>
			<?php
			}
			?>
			</table>
			</div>
		<?php
		}
		?>
	</div>
</div>
<?php
	include("../res/footer.php");
?>
</body>
</html>