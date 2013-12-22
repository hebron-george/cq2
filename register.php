<?
	//retrieve data from POST
	$username = $_POST['username'];
	$pass1 = $_POST['password'];
	$pass2 = $_POST['password_again'];

	if (isset($username) && len($username) > 0)
	{
		$retVal = "";
		$successfulRegistration = true;
		if (isset($pass1) && len($pass1) > 0 && isset($pass2) && len($pass2) > 0)
		{
			if ($pass1 != $pass2)
			{
				$successfulRegistration = false;
				$retVal += "Your passwords don't match.\n";
			}
		}
		else
		{
			$successfulRegistration = false;
			$retVal += "You haven't entered your desired password.";
		}
		
		if (len($username) > 30)
		{
			$successfulRegistration = false;
			$retVal += "Your username cannot be more than 30 characters.\n";
		}

		if (!$successfulRegistration)
		{
			echo $retVal;
		}
	}
?>


<html>
	<head>
		<link rel="stylesheet" type="text/css" href="main.css">
	</head>

	<body>
		<form name="register" action="" method="POST">
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