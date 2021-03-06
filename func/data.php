<?php
include_once("sql.php");
function getone($table,$id){
	$query=new query;
	$query->table = $table;
	$query->column = array("*");
	$query->where = array(
		array("id",$id)
	);
	$query->limit = array(0,1);
	return fetchone(SELECT($query));
}
function getall($table){
	$query=new query;
	$query->table = $table;
	$query->column = array("*");
	$query->order = array(
		array("id","ASC")
	);
	$row = SELECT($query);
	$data=array();
	foreach ($row as $temp){
		$data[$temp["id"]]=$temp;
	}
	return $data;
}

function getonecate($id){
	return getone("category",$id);
}
function getallcate(){
	return getall("category");
}

function getoneacct($id){
	return getone("account",$id);
}
function getallacct(){
	$query=new query;
	$query->table = "account";
	$query->column = array("id","user","name","email","power");
	$query->order = array(
		array("name","ASC")
	);
	$row = SELECT($query);
	$data=array();
	foreach ($row as $temp){
		$data[$temp["id"]]=$temp;
	}
	return $data;
}

function getoneroom($id){
	return getallroom()[$id] ?? null;
}
function getallroom(){
	$query=new query;
	$query->table = "roomlist";
	$query->column = array("*");
	$query->order = array(
		array("cate","ASC"),
		array("name","ASC")
	);
	$row = SELECT($query);
	$data=array();
	foreach ($row as $temp){
		$data[$temp["id"]]=$temp;
		$data[$temp["id"]]['borrow_accept_period'] = json_decode($data[$temp["id"]]['borrow_accept_period'], true);
	}
	return $data;
}

function getoneborrow($hash){
	$query=new query;
	$query->table = "borrow";
	$query->column = array("*");
	$query->where = array(
		array("hash",$hash)
	);
	$query->limit = array(0,1);
	return fetchone(SELECT($query));
}

function periodname($short = false){
	$query=new query;
	$query->table = "periodname";
	$query->order = array("no");
	$row=SELECT($query);
	$res=array();
	foreach ($row as $temp) {
		if ($short) $res[$temp["no"]]=$temp["shortname"];
		else $res[$temp["no"]]=$temp["name"];
	}
	return $res;
}
?>