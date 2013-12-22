<?
	require('config.php');
	collectVisitorStats();
	//retrieve data from POST
	$username = $_POST['username'];
	$pass1 = $_POST['password'];
	$pass2 = $_POST['password_again'];

	/* Check to see whether anything was typed into the username field to be submitted. */
	if (isset($username) && strlen($username) > 0)
	{
		$retVal = "";
		$successfulRegistration = true;
		if (isset($pass1) && strlen($pass1) > 0 && isset($pass2) && strlen($pass2) > 0)
		{
			if ($pass1 != $pass2)
			{
				$successfulRegistration = false;
				$retVal .= "Your passwords don't match.<br>";
			}
		}
		else
		{
			$successfulRegistration = false;
			$retVal .= "You haven't entered your desired password.";
		}
		
		/* Check to see if the username doesn't have over 30 characters */
		if (strlen($username) > 30)
		{
			$successfulRegistration = false;
			$retVal .= "Your username cannot be more than 30 characters.<br>";
		}

		if (!$successfulRegistration)
		{
			echo $retVal;
		}
		else
		{
			/* Registration checks have returned successfully */
			$hash = hash('sha256', $pass1);
			$salt = createSalt();
			$hash = hash('sha256', $salt.$hash);

			$stmt = $dbh->prepare("INSERT INTO ".Config::users_table." (username, password, salt) VALUES(?, ?, ?)");
			$stmt->bindParam(1, $username);
			$stmt->bindParam(2, $hash);
			$stmt->bindParam(3, $salt);

			$result = $stmt->execute();
			if(1 != $result)
			{
				echo "Error: User not created. Perhaps the user already exists? <br>";
			}
			else
			{
				echo "Successfully added user: $username <br>";
			}
		}
	}

	function createSalt()
	{
		$string = md5(uniqid(rand(), true));
		return $string;
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
			$page = Config::REGISTER;

			$stmt = $dbh->prepare("INSERT INTO ".Config::visitor_stats." (client_IP, user_agent, visited_time, visited_page) VALUES (?, ?, NOW(), ?)");
			$stmt->bindParam(1, $ip);
			$stmt->bindParam(2, $user_agent);
			$stmt->bindParam(3, $page);

			$stmt->execute();		
	}
?>