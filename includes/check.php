<?php

$con = mysql_connect("localhost","root","root");
$datab = mysql_select_db("topfloor");


$check = mysql_query('SELECT distinct flashv.objid, flashv.flashvars, flashv.streamId FROM flashv LEFT JOIN user ON flashv.streamId=user.streamId where user.topfloor="yes"');

$rows = array();
while($myquery = mysql_fetch_array($check)){
	$rows[] = $myquery;
}
	
$fp = fopen('../json/obj.json', 'w');
fwrite($fp, json_encode($rows));
fclose($fp);

?>