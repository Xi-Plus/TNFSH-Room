<!DOCTYPE html>
<html lang="zh-Hant-TW">
<?php
date_default_timezone_set("Asia/Taipei");
include_once("../func/sql.php");
include_once("../func/common.php");
include_once("../func/msgbox.php");
$query=new query;
$query->table = "session";
$query->where = array(
	array("cookie",@$_COOKIE["TNFSH_Classroom"])
);
DELETE($query);
setcookie("TNFSH_Classroom", "", time(), "/");
?>
<head>
<meta charset="UTF-8">
<?php
include_once("../res/comhead.php");
?>
<title>登出-<?php echo $cfg['site']['name']; ?></title>
</head>
<body Marginwidth="-1" Marginheight="-1" Topmargin="0" Leftmargin="0">
<?php
	addmsgbox("success","已登出",false);
	include_once("../res/header.php");
	include("../res/footer.php");
?>
<script>setTimeout(function(){location="../home";},1000)</script>
</body>
</html>