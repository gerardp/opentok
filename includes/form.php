<?php

$con = mysql_connect("localhost","root","root");
$datab = mysql_select_db("topfloor");

$insert = mysql_query('INSERT INTO user(streamId, connectionId) VALUES ("'.$_POST['streamId'].'","'.$_POST['connectionId'].'")');
?>