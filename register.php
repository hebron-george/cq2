<?
	session_start();
	require("session_functions.php");
	if(!isLoggedIn() || $_SESSION['username'] != "Vashy")
	{
		header('Location: login.php');
		die();
	}

?>


<html>
	<head>
		<link rel="stylesheet" type="text/css" href="main.css">
	</head>

	<body>
		<form name="register" action="register_processor.php" method="POST">
			<div id="loginFormLabel">Username:</div>
			<input type="text" name="username" />
			<br>
			<div id="loginFormLabel">Password:</div>
			<input type="password" name="password" />
			<br>
			<div id="loginFormLabel">Password again:</div>
			<input type="password" name="password_again" />
			<br>
			<input type="submit" value="Register" />	
		</form>
	</body>
</html>