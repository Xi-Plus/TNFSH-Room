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
$login=checklogin();
$period=periodname();
$cate=getallcate();
if(isset($_POST["borrowone"])){
	if($login==false)header("Location: ../login/");
	$room = getoneroom($_POST['borrowid']);
	if(strtotime($_POST["borrowdate"])<strtotime(date("Y-m-d"))+86400*$room['borrow_daylimit_min']){
		addmsgbox("warning","日期必須是 ".$room['borrow_daylimit_min']." 天以後");
	}else if(strtotime($_POST["borrowdate"])>strtotime(date("Y-m-d"))+86400*$room['borrow_daylimit_max']){
		addmsgbox("warning","日期必須是 ".$room['borrow_daylimit_max']." 天以前");
	}else {
		$query=new query;
		$query->table ="borrow";
		$query->where = array(
			array("userid",$login["id"]),
			array("roomid",$_POST["borrowid"]),
			array("date",$_POST["borrowdate"]),
			array("class",$_POST["borrowclass"])
		);
		$borrow=fetchone(SELECT($query));
		if($borrow===null){
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
			addmsgbox("success","已預約 ".$_POST["borrowdate"]." ".$period[$_POST["borrowclass"]]." ".$room["name"]);
		}else {
			addmsgbox("warning","已有人預約");
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
					addmsgbox("info","已刪除預約 ".$cate[$row["roomid"]]["name"]."-".$room["name"]." 日期 ".$row["date"]." ".$period[$row["class"]]."<br>");
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
			addmsgbox("success","已預約 ".date("Y-m-d",$starttime)." 至 ".date("Y-m-d",$endtime)." 星期".$_POST["borrowweek"]." ".$period[$_POST["borrowclass"]]." ".$row["name"]);
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
			addmsgbox("info","已刪除 ".date("Y-m-d",$starttime)." 至 ".date("Y-m-d",$endtime)." 星期".$_POST["borrowweek"]." ".$period[$_POST["borrowclass"]]." ".$row["name"]);
		}
	}
}
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
		addmsgbox("info","已刪除預約 ".$cate[$room["cate"]]["name"]."-".$room["name"]." 日期 ".$borrow["date"]." ".$period[$borrow["class"]]);
	}else {
		addmsgbox("danger","你沒有權限");
	}
}
$acct=getallacct();
$room=getallroom();
$date=@(strtotime($_GET["date"])==false?date("Y-m-d"):$_GET["date"]);
$class=@(is_numeric($_GET["class"])?$_GET["class"]:"1");
$roomid=@$_GET["roomid"];
if (!isset($_GET["layout"])) {
	$_GET["layout"] = 0;
}
$layout = $_GET["layout"];
if (!in_array($layout, ["1", "2"])) {
	$layout = $room[$roomid]["default_layout"] ?? 1;
}
?>
<head>
<?php
include_once("../res/comhead.php");
?>
<title>所有預約查詢-<?php echo $cfg['site']['name']; ?></title>
</head>
<body Marginwidth="-1" Marginheight="-1" Topmargin="0" Leftmargin="0">
<?php
include_once("../res/header.php");
?>
<script type="text/javascript">
var period = <?php echo json_encode($period); ?>
</script>
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
				<span class="input-group-addon">場地</span>
				<select class="form-control" name="roomid" onchange="if(this.value!='')search.submit();">
					<option value="" id="chooseone">請選取一個</option>
				<?php
					foreach ($room as $roomtemp) {
				?>
					<option value="<?php echo $roomtemp["id"]; ?>"<?php echo($roomtemp["id"]==$roomid?" selected":""); ?>><?php echo @$cate[$roomtemp["cate"]]["name"]." ".$roomtemp["name"]; ?></option>
				<?php
					}
				?>
				</select>
				<span class="input-group-addon glyphicon glyphicon-home"></span>
			</div>
			<div class="input-group">
				<span class="input-group-addon">顯示版面</span>
				<select class="form-control" name="layout" onchange="if(this.value!='')search.submit();">
					<option value="0"<?php echo($_GET["layout"]==0?" selected":"");?>>使用預設</option>
					<option value="1"<?php echo($_GET["layout"]==1?" selected":"");?>>一律<?=$cfg['text']['layout'][1]?></option>
					<option value="2"<?php echo($_GET["layout"]==2?" selected":"");?>>一律<?=$cfg['text']['layout'][2]?></option>
				</select>
				<span class="input-group-addon glyphicon glyphicon-time"></span>
			</div>
		</form>
	<?php
	if($roomid&&checkroompermission($login["id"],$roomid)){
	?>
		<h2>管理員預約</h2>
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
				</select>
				<span class="input-group-addon glyphicon glyphicon-calendar"></span>
			</div>
			<div class="input-group">
				<span class="input-group-addon">時間</span>
				<select class="form-control" name="borrowclass">
					<?php
					foreach ($room[$roomid]['borrow_accept_period'] as $key) {
						echo '<option value="'.$key.'">'.$period[$key].'</option>';
					}
					?>
				</select>
				<span class="input-group-addon glyphicon glyphicon-time"></span>
			</div>
			<button name="input" type="submit" class="btn btn-success" onClick="if(!confirm('確認預約？'))return false;borrowtype.value='borrow';">
				<span class="glyphicon glyphicon-shopping-cart"></span>
				預約 
			</button>
			<button name="input" type="submit" class="btn btn-danger" onClick="if(!confirm('確認取消預約？'))return false;borrowtype.value='delete';">
				<span class="glyphicon glyphicon-trash"></span>
				取消預約 
			</button>
		</form>
		<h2>場地設定</h2>
		<a href="../editroom/?roomid=<?=$roomid?>">前往場地設定</a>
	<?php
	}
	?>
	</div>
	<div class="col-lg-8">
		<?php
		if($roomid==""){
			?><h2>請先選取一個場地</h2><?php
		}else if(!isset($room[$roomid])){
			?><h2>查無場地，請重新選取</h2><?php
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
			if ($layout == 1) {
				$firstdate=mktime(0, 0, 0, date("n",strtotime($date)), date("j",strtotime($date))-date("w",strtotime($date)), date("Y",strtotime($date)));
				$enddate=mktime(23, 59, 59, date("n",strtotime($date)), date("j",strtotime($date))-date("w",strtotime($date))+6, date("Y",strtotime($date)));
			} else if ($layout == 2) {
				$firstdate=mktime(0, 0, 0, date("n",strtotime($date)), 1, date("Y",strtotime($date)));
				$enddate=mktime(23, 59, 59, date("n",strtotime($date)), date("t",strtotime($date)), date("Y",strtotime($date)));
			}	
			?>
			目前顯示：<?php echo date("Y-m-d",$firstdate); ?>&nbsp;至&nbsp;<?php echo date("Y-m-d",$enddate); ?>&nbsp;<?php echo $cate[$room[$roomid]["cate"]]["name"]." ".$room[$roomid]["name"]; ?>（可借用距今<?=$room[$roomid]["borrow_daylimit_min"]?>～<?=$room[$roomid]["borrow_daylimit_max"]?>天）
			
			<div class="table-responsive">
			<script>
				function checkborrow(id,date,cla){
					if(!confirm('確認預約 '+date+' '+period[cla]+'？'))return false;
					borrowid.value=id;
					borrowdate.value=date;
					borrowclass.value=cla;
					borrow.submit();
				}
				function checkdelborrow(id,other){
					if(!confirm('確認取消？'+(other?'\n注意！這是其他人的預約':'')))return false;
					delhash.value=id;
					delborrow.submit();
					return false;
				}
			</script>
			<table cellspacing="0" cellpadding="5" style="font-size:20px" class="table table-hover table-condensed">
			<tr>
			<th>
				<a href="?date=<?php
				if ($layout == 1) {
					echo date("Y-m-d", strtotime($date)-86400*7);
				} else if ($layout == 2) {
					echo date("Y-m-d", strtotime($date.' -1 month'));
				}
				?>&roomid=<?=$roomid?>&layout=<?=$_GET["layout"]?>" class="btn btn-default btn-xs" role="button">
					＜
				</a>
				<a href="?date=<?php echo date("Y-m-d");?>&roomid=<?=$roomid?>&layout=<?=$_GET["layout"]?>" class="btn btn-default btn-xs" role="button">
					●
				</a>
				<a href="?date=<?php
				if ($layout == 1) {
					echo date("Y-m-d", strtotime($date)+86400*7);
				} else if ($layout == 2) {
					echo date("Y-m-d", strtotime($date.' +1 month'));
				}
				?>&roomid=<?=$roomid?>&layout=<?=$_GET["layout"]?>" class="btn btn-default btn-xs" role="button">
					＞
				</a>
			</th>
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
				<th <?php
				if ($layout == 1 && date("Y-m-d", $firstdate+86400*$d) == $date) {
					echo 'class="info"'; 
				}
				?> style="text-align: center;"><?php
					 echo "(".$week[$d].")";
					 ?></th>
			<?php
			}
			?>
			</tr>
			<?php
			$now = $firstdate;
			while (true) {
				?><tr><th></th><?php
				for ($i=0; $i < date("w", $now); $i++) { 
					?><th></th><?php
				}
				for ($i=date("w", $now); $i < 7; $i++, $now+=86400) {
					if ($now > $enddate) {
						?><th></th><?php
					} else {
						?><th <?php echo (date("Y-m-d", $now) == $date?'class="info"':''); ?> style="text-align: center;"><?php echo date("d", $now); ?></th><?php
					}
				}
				?></tr><?php
				foreach ($room[$roomid]['borrow_accept_period'] as $c) {
				?>
					<tr>
					<td><?php echo $period[$c]; ?></td>
					<?php
					for($d=0;$d<7;$d++){
						$borrowtime = $now-86400*7+86400*$d;
						$borrowdate = date("Y-m-d", $borrowtime);
					?>
						<td <?php echo ($borrowdate == $date?'class="info"':''); ?> align="center"><?php
						if ($layout == 2 && ($borrowtime < $firstdate || $borrowtime > $enddate)) {
						} else if(isset($borrow[$borrowdate][$c])){
							// 已被借用
							if(checkborrowpermission($borrow[$borrowdate][$c]["hash"],$login["id"])&&$borrowdate>=date("Y-m-d")){
								// 刪除借用
							?>
								<button name="input" type="button" class="btn btn-danger" onClick="checkdelborrow('<?php echo $borrow[$borrowdate][$c]["hash"]; ?>',<?php echo ($borrow[$borrowdate][$c]["userid"]==$login["id"]?"false":"true"); ?>);">
									<span class="glyphicon glyphicon-trash"></span>
									<?php echo $acct[$borrow[$borrowdate][$c]["userid"]]["name"]; ?> 
								</button>
							<?php
							}else {
								// 不能刪除
							?>
								<button name="input" type="button" class="btn btn-default" disabled>
									<span class="glyphicon glyphicon-user"></span>
									<?php echo $acct[$borrow[$borrowdate][$c]["userid"]]["name"]; ?> 
								</button>
							<?php
							}
						}else {
							// 未被借用
							if($login!=false&&(checkroompermission($login["id"],$roomid)||$borrowtime>=time()-86400+86400*$room[$roomid]["borrow_daylimit_min"]&&$borrowtime<=time()-86400+86400*$room[$roomid]["borrow_daylimit_max"])&&$borrowdate>=date("Y-m-d")){
							?>
								<button name="input" type="button" class="btn btn-success" value="預約" onClick="checkborrow('<?php echo $roomid; ?>','<?php echo $borrowdate; ?>','<?php echo $c; ?>');">
									<span class="glyphicon glyphicon-shopping-cart"></span>
									預約 
								</button>
							<?php
							}
						}
						?>
						</td>
						<?php
					}
					?>
					</tr>
				<?php
				}
				if ($now >= $enddate) {
					break;
				}
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