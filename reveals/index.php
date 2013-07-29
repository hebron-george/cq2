<html>
	<head>
		<title>Reveals Submission</title>
		<link rel="stylesheet" type="text/css" href="reveals.css">
	</head>
<?
	require('../config.php');

	//Connect to database
	try 
	{
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
?>

	<body>
		<?

			if (isset($_POST['sublist']) && isset($_POST['user']) && strlen($_POST['sublist']) > 0 && strlen($_POST['user']) > 0)
			{
				
				$user = htmlentities($_POST['user'], ENT_QUOTES);
				$list = htmlentities($_POST['sublist'], ENT_QUOTES);


				$stmt = $dbh->prepare("SELECT COUNT(id) FROM ".Config::reveals_table." WHERE user=?");
				$stmt->bindParam(1, $user);
				$stmt->execute();
				$result = $stmt->fetchAll();
				if ($result[0][0] > 0)
				{
					$stmt = $dbh->prepare("UPDATE ".Config::reveals_table." SET submissionDate=NOW(), list=?, client_IP=? WHERE user=?");
					$stmt->bindParam(1, $list);
					$stmt->bindParam(2, $ip);
					$stmt->bindParam(3, $user);
					$stmt->execute();
				}
				else
				{
					$stmt = $dbh->prepare("INSERT INTO ".Config::reveals_table." (user, list, submissionDate, client_IP) VALUES (?, ?, NOW(), ?)");
					$stmt->bindParam(1, $user);
					$stmt->bindParam(2, $list);
					$stmt->bindParam(3, $ip);

					$stmt->execute();

				}

				echo ("Successfully submitted reveal for: ". $user);
			}
		?>
		<center>		
		<div id="subUsers">
		<br>
		<h2> Submitted Users </h2>
		<br>
		<?
			$stmt = $dbh->prepare("SELECT user FROM reveals");
			$stmt->execute();

			$result = $stmt->fetchAll();

			for($i = 0; $i < count($result); $i++)
			{
				echo '<a href="http://hebrongeorge.com/cq2/reveals/findReveal.php?user='.$result[$i]['user'].'">'.$result[$i]['user'].'</a><br>';
			}

			//foreach($result[0]['user'] as $i)
			//	echo $i."<br>";
		?>
		</div>

		<h2>Find a Reveal</h2>
		<a href="findReveal.php">Looking for a reveal?</a>

		<h2>Submit a Reveal</h2>
		<br>
		<form action="reveal.php" method="POST">
			Mage: 
			<br>
			<input id="user" class="textbox" name="user" type="text"></input>
			<br>
			List: 
			<br>
			<textarea class="textbox" rows="25" cols="90" id="sublist" name="sublist"></textarea>
			<br>
			<input type="submit"></input>
		</form>
		
		</center>
	</body>

</html>