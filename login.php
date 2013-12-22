<?
	session_start();
	require("config.php");
	require("session_functions.php");
	collectVisitorStats();
	
	$username = $_POST['username'];
	$pass = $_POST['password'];

	if(isset($username) && strlen($username) > 0)
	{
		$stmt = $dbh->prepare("SELECT password, salt FROM ".Config::users_table." WHERE username = ?");
		$stmt->bindParam(1, $username);

		$stmt->execute();
		$result = $stmt->fetchAll();

		if (sizeof($result) < 1)
		{
			echo "Could not find user: $username";
		}
		else
		{
			$hash = hash('sha256', $result[0]['salt'].hash('sha256', $pass));
			if( $hash != $result[0]['password'] )
			{
				echo "Invalid username/password combination. <br>";
			}
			else
			{
				/* Successful user log in*/
				validateUser($username);
				header('Location: index.php');
			}
		}

	}
	function collectVisitorStats()
	{
			//Connect to database
			try 
			{
				global $dbh;
				$dbh = new PDO("mysql:host=".Config::db_host.";dbname=".Config::db_name, Config::db_user, Config::db_pass);
			}
			catch(PDOException $e)
			{
				die($e->getMessage());
			}



			//Test if it is a shared client
			if (!empty($_SERVER['HTTP_CLIENT_IP']))
			{
				$ip=$_SERVER['HTTP_CLIENT_IP'];
			}
			elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
			{
				//Is it a proxy address
				$ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
			} 
			else
			{
				$ip=$_SERVER['REMOTE_ADDR'];
			}

			//The value of $ip at this point would look something like: "192.0.34.166"
			$ip = ip2long($ip);
			//The $ip would now look something like: 1073732954
			$user_agent = $_SERVER['HTTP_USER_AGENT'];
			$page = Config::LOGIN;

			$stmt = $dbh->prepare("INSERT INTO ".Config::visitor_stats." (client_IP, user_agent, visited_time, visited_page) VALUES (?, ?, NOW(), ?)");
			$stmt->bindParam(1, $ip);
			$stmt->bindParam(2, $user_agent);
			$stmt->bindParam(3, $page);

			$stmt->execute();		
	}
?>

<html>
	<head>
		<link rel="stylesheet" type="text/css" href="main.css">
	</head>
	<body>
		<form name="login" action="" method="POST">
			<div id="loginFormLabel">Username:</div>
			<input type="text" name="username" />
			<br>
			<div id="loginFormLabel">Password:</div>
			<input type="password" name="password" />
			<br>
			<input type="submit" value="Login" />
		</form>
	</body>
</html>