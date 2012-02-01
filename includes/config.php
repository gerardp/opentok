<?php
// Connection to the database
	$hostname = 'localhost';        
	$dbname   = 'topfloor';
	$username = 'root';             
	$password = 'root';       
	mysql_connect($hostname, $username, $password) or die('Connection to host is failed, perhaps the service is down!');
	mysql_select_db($dbname) or die('Database name is not available!');
?>