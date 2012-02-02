<?php
// Begin session
session_start();
// If username is set, then go to the index page
if (isset($_SESSION['username'])) {
	header('Location:user.php');
}else{
}
?>
<!DOCTYPE html>
<html xmlns:fb="http://www.facebook.com/2008/fbml">
<head>
	<title>The Top Floor Project</title>
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>
	<link rel="stylesheet" type="text/css" href="styl.css" />
	<link rel="stylesheet" type="text/css" href="css/login.css" />
	<link rel="shortcut icon" type="image/x-icon" href="images/favi.ico">
</head>
<body class="login">

	<div id="login">

		<div id="index_response"></div>
		<img src="images/topfloor.png" width="140" style="margin:10px 90px" />
		<form name="loginform" id="loginform" action="includes/authentication.php" method="post">
			<div id="index">
				<p>
					<label>username</label>
					<input name="username" id="username" class="input" size="20" type="text"></label>
				</p>
				<p>
					<label>username</label>
					<input name="password" id="password" class="input" size="20" type="text"></label>
				</p>

				<p class="submit">
					<input name="login" id="submit" class="crtbtn_bl" value="Login" type="submit">
				</p>
			</div>		
		</div>
		<!--<a href="#"><div style="position: absolute;left: 571px;top: 410px;"><img src="images/facebookc.png" width="200" /></div></a>-->
	</form>
</body>
</html>