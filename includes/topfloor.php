<?php

$con = mysql_connect("localhost","root","root");
$datab = mysql_select_db("topfloor");

$o = mysql_query('UPDATE user SET topfloor = "yes" WHERE streamId = "'.$_POST['streamId'].'" and connectionId="'.$_POST['connectionId'].'"') or die(mysql_error());  




