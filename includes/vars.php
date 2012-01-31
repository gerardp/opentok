<?php

$con = mysql_connect("localhost","root","root");
$datab = mysql_select_db("topfloor");


 $objid = $_POST['objid'];
 $flashvars = mysql_real_escape_string($_POST['flashvars']);
 $streamid = $_POST['streamid'];


mysql_query('INSERT INTO flashv(streamId, objid, flashvars) VALUES ("'.$_POST['streamid'].'","'.$_POST['objid'].'","'.$_POST['flashvars'].'")');

?>