<?php
session_start();

$con = mysql_connect("localhost","root","root");
$datab = mysql_select_db("topfloor");

//$insert = mysql_query('INSERT INTO user(username, password) VALUES ("'.$_POST['username'].'","'.$_POST['password'].'")');
$user = mysql_query('SELECT username, password FROM user WHERE username="'.$_POST['username'].'" and password="'.$_POST['password'].'"');

// Check username and password match
if (mysql_num_rows($user) == 1) {
	// Set username session variable
	$_SESSION['username'] = $_POST['username'];
	// Jump to secured page
	header('Location: ../logged/index.php');
}
else {
	// Jump to index page
	header('Location: ../user.php');
}