<?php

$con = mysql_connect("localhost","root","root");
$datab = mysql_select_db("topfloor");


$find = mysql_query("SELECT * FROM user WHERE streamId='".$_POST['streamId']."'");
if(mysql_num_rows($find) == 0)
{
	$put = mysql_query('INSERT INTO user(id,streamId, connectionId) VALUES (null,"'.$_POST['streamId'].'","'.$_POST['connectionId'].'")');
	mysql_query($put, $mysql_access);
}

?>