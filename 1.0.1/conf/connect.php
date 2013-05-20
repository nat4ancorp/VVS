<?php
$con = mysql_connect($properties->DB_HOST,$properties->DB_USER,$properties->DB_PASS);
if(!$con){
	die('Could not connect: '.mysql_error());
}
mysql_select_db($properties->DB_NAME,$con);
?>