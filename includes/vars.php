<?php

$con = mysql_connect("localhost","root","root");
$datab = mysql_select_db("topfloor");


$objid = $_POST['objid'];
$flashvars = $_POST['flashvars'];
$streamid2 = $_POST['streamid2'];


//$ca = 'UPDATE user SET objid="'.$objid.'" WHERE streamId ="'.$_POST['streamid2'].'"' or die(mysql_error());  

$ca = mysql_query('UPDATE user SET flashvars="'.$flashvars.'"') or die(mysql_error());  

?>