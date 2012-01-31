<?php
$con = mysql_connect("localhost","root","root");
$datab = mysql_select_db("topfloor");

$o = mysql_query('UPDATE user SET topfloor = "no" WHERE streamId = "'.$_POST['streamId'].'"') or die(mysql_error());  




