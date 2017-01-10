<?php
function url(){
	$url=$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
	return substr($url,0,strrpos($url,"/")+1);
}
function het($text){
	return htmlentities($text,ENT_QUOTES);
}
function getrandommd5(){
	return md5(uniqid(rand(),true));
}
function timelen($sec){
	if ($sec<60) {
		return $sec."秒";
	} else if ($sec<60*60) {
		return floor($sec/60)."分";
	} else if ($sec<60*60*24) {
		return floor($sec/(60*60))."時";
	} else if ($sec<60*60*24*31) {
		return floor($sec/(60*60*24))."日";
	} else if ($sec<60*60*24*365) {
		return floor($sec/(60*60*24*30))."月";
	} else {
		return floor($sec/(60*60*24*365))."年";
	}
}
?>