<?php

$con = mysql_connect("localhost","root","root");
$datab = mysql_select_db("topfloor");


$check = mysql_query('SELECT objid, flashvars FROM user WHERE topfloor="'.$_POST['join'].'"');

$rows = array();
while($myquery = mysql_fetch_array($check)){
	$rows[] = $myquery;
}
	
$fp = fopen('../json/obj.json', 'w');
fwrite($fp, json_encode($rows));
fclose($fp);

?>