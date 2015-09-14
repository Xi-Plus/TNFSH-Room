<?php
include_once("sql.php");
include_once("data.php");
function checklogin(){
	if(!isset($_COOKIE["TNFSH_Classroom"]))return false;
	$query=new query;
	$query->table="session";
	$query->column = array("id");
	$query->where = array(
		array("cookie",$_COOKIE["TNFSH_Classroom"])
	);
	$query->limit = array(0,1);
	$row = fetchone(SELECT($query));
	if($row===null)return false;
	$query=new query;
	$query->table="account";
	$query->column = array("*");
	$query->where = array(
		array("id",$row["id"]),
	);
	$query->limit = array(0,1);
	return fetchone(SELECT($query));
}
function checkroompermission($uid,$roomid=0){
	$query=new query;
	$query->table="account";
	$query->column = array("power");
	$query->where = array(
		array("id",$uid)
	);
	$row = fetchone(SELECT($query));
	if($row["power"]>=2)return true;
	$query=new query;
	$query->table="roomlist";
	$query->column = array("*");
	$query->where = array(
		array("admin",$uid)
	);
	if($roomid!=0)$query->where[]=array("id",$roomid);
	$query->limit = array(0,1);
	$row = fetchone(SELECT($query));
	if($row===null)return false;
	return true;
}
function checkborrowpermission($hash,$uid){
	$query=new query;
	$query->table = "borrow";
	$query->column = array("*");
	$query->where = array(
		array("hash",$hash)
	);
	$borrow=fetchone(SELECT($query));
	if($borrow===false)return false;
	else if($uid==$borrow["userid"])return true;
	else if(checkroompermission($uid,$borrow["roomid"]))return true;
	else return false;
}
?>