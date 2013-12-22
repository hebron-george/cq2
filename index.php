<?
	require("session_functions.php");
	if(!isLoggedIn())
	{
		header('Location: login.php');
		die();
	}
?>
<html>
	<head>
		<title>Castle Quest 2 Tools</title>
		<link rel="stylesheet" type="text/css" href="main.css">
	</head>

	<body>
		<?
			include('menu.php');
		?>
	</body>
</html>