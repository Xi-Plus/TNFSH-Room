<?php
include_once("../config/config.php");
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="../res/css/bootstrap.min.css" rel="stylesheet">
<script src="../res/js/jquery.min.js"></script>
<script src="../res/js/bootstrap.min.js"></script>
<link rel="SHORTCUT ICON" href="http://www.tnfsh.tn.edu.tw/ezfiles/0/1000/sys_1000_5899370_87126.ico" type="image/x-icon">
<meta property="og:title" content="<?php echo $cfg['og']['title']; ?>"/>
<meta property="og:type" content="website"/>
<meta property="og:description" content="<?php echo $cfg['og']['description']; ?>"/>
<meta property="og:url" content="http://<?php echo url()?>"/>
<meta property="og:image" content="http://www.tnfsh.tn.edu.tw/ezfiles/0/1000/sys_1000_5899370_87126.ico">
<style type="text/css">
body {
	background-color:#FFF0C5;
}
</style>
<?php
include_once("../func/checkpermission.php");
$login=checklogin();
?>
<script>
function keyFunction(){
	if ((event.altKey) && (event.keyCode!=18)){
		switch(event.keyCode){
			case 49: location="../home";break;
			case 50: location="../search";break;
			case 51: location="../user";break;
			<?php
			if(@$login["power"]>=2){
			?>
			case 52: location="../validborrow";break;
			case 53: location="../manageroom";break;
			case 54: location="../manageuser";break;
			<?php
			}
			?>
			case 48: location="../<?php echo ($login?"logout":"login");?>";break;
		}
	}
}
window.onkeydown=keyFunction;
document.onkeydown=keyFunction;
</script>