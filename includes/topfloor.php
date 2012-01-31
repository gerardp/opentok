<?php

$con = mysql_connect("localhost","root","root");
$datab = mysql_select_db("topfloor");

mysql_query('UPDATE user SET topfloor = "yes" WHERE streamId = "'.$_POST['streamId'].'" and connectionId="'.$_POST['connectionId'].'"') or die(mysql_error());  
//mysql_query("UPDATE user SET approved='no' WHERE id=$key");      

?>