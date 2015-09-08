<?php
date_default_timezone_set("Asia/Taipei");
include_once("../func/common.php");
include_once("../func/checkpermission.php");
include_once("../func/sql.php");
include_once("../func/msgbox.php");
$login=checklogin();
?>
<div id="wrap" sytle="min-height: 100%;">
	<header class="navbar navbar-static-top bs-docs-nav" id="top" role="banner" style="background-color: #FECB2C; margin: 0;">
		<div class="container">
			<div class="navbar-header" style="background-color: #FECB2C;">
				<button class="navbar-toggle collapsed" type="button" data-toggle="collapse" data-target=".bs-navbar-collapse" style="background-color:#802915;">
					<span class="sr-only" style="background-color:#FECB2C;">巡覽切換</span>
					<span class="icon-bar" style="background-color:#FECB2C;"></span>
					<span class="icon-bar" style="background-color:#FECB2C;"></span>
					<span class="icon-bar" style="background-color:#FECB2C;"></span>
				</button>
				<a href="../home" class="navbar-brand">
					<div style="float: left;">
						<img src="http://www.tnfsh.tn.edu.tw/ezfiles/0/1000/sys_1000_5047883_27719.png" alt="臺南第一高級中學" height="30px">
					</div>
					<div style="float: left;">
						&nbsp;&nbsp;<span style="font-weight: bold; font-size: px; font-family: '標楷體';">場地預約管理系統</span><br>
					</div>
				</a>
			</div>
			<nav class="collapse navbar-collapse bs-navbar-collapse" role="navigation">
				<ul class="nav navbar-nav">
					<li>
						<a href="../home">最近預約</a>
					</li>
					<li>
						<a href="../search">所有預約查詢</a>
					</li>
					<li>
						<a href="../user">個人預約查詢</a>
					</li>
					<?php 
					if(@checkroompermission($login["id"])){ ?>
					<li>
						<a href="../validborrow">預約審核
						<?php
						$query=new query;
						$query->table = "borrow";
						$query->column = array("*");
						$query->where = array(
							array("valid","0"),
							array("date",date("Y-m-d"),">=")
						);
						$row = SELECT($query);
						$isborrow=false;
						foreach ($row as $borrow) {
							if(!checkroompermission(@$login["id"],$borrow["roomid"]))continue;
							$isborrow=true;
							break;
						}
						if($isborrow)echo "<font color='#802915'>(新申請)</font>"; ?>
						</a>
					</li>
					<?php }if(@$login["power"]>=2){ ?>
					<li>
						<a href="../manageroom">場地管理</a>
					</li>
					<li>
						<a href="../manageuser">使用者管理</a>
					</li>
					<?php } ?>
				</ul>
				<ul class="nav navbar-nav navbar-right">
					<?php 
					if($login){
					?>
					<li>
						<a href="../logout"><?php echo @$login["user"]."(".het(@$login["name"]).")"; ?> 登出</a></li>
					<?php
					}else{
					?>
					<li><a href="../login">登入</a></li>
					<?php
					}
					?>
				</ul>
			</nav>
		</div>
	</header>
<?php
	showmsgbox();
?>
	<div class="container-fluid">