<?php

$con = mysql_connect("localhost","root","root");
$datab = mysql_select_db("topfloor");

//$o = mysql_query('UPDATE user SET topfloor = "yes" WHERE streamId = "'.$_POST['streamId'].'" and connectionId="'.$_POST['connectionId'].'"') or die(mysql_error());  

$getuser = mysql_query("SELECT * FROM user WHERE streamId='".$_POST['streamId']."'");
if(mysql_num_rows($getuser) != 0)
{
	$o = mysql_query('UPDATE user SET topfloor = "yes" WHERE streamId = "'.$_POST['streamId'].'" and connectionId="'.$_POST['connectionId'].'"') or die(mysql_error());  
	//$user = "UPDATE user SET FirstName = '".$_POST['FirstName']."' WHERE FacebookID = '".$_POST['FacebookID']."'";
	mysql_query($o, $mysql_access);
}



