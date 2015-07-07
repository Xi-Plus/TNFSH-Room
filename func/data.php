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
	return getall("account");
}

function getoneroom($id){
	return getone("roomlist",$id);
}
function getallroom(){
	return getall("roomlist");
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
?>