<?php

// Begin session
session_start();
$con = mysql_connect("localhost","root","root");
$datab = mysql_select_db("topfloor");

// Include database connection settings
$username = $_POST['username'];
$password = $_POST['password'];

// Retrieve username and password from database according to user's input
$query = mysql_query("SELECT * FROM admin WHERE username = '$username' AND password = '$password'");

// Check username and password match
if (mysql_num_rows($query) == 1) {
	// Set username session variable
	$_SESSION['username'] = $_POST['username'];
	// Jump to secured page
	header('Location: ../user.php');
}
else {
	// Jump to index page
	header('Location: ../index.php');
}

?>