<?

	require('../session_functions.php');
	if(!isLoggedIn())
	{
		header('Location: ../login.php');
		die();
	}
?>
<html>
	<head>
		<title>Curses</title>
		<link rel="stylesheet" type="text/css" href="../main.css">		
		<?
			require('../config.php');
			include("../menu.php");
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
			$user_agent = $_SERVER['HTTP_USER_AGENT'];
			$page = Config::CURSE_ADD;

			$stmt = $dbh->prepare("INSERT INTO ".Config::visitor_stats." (client_IP, user_agent, visited_time, visited_page) VALUES (?, ?, NOW(), ?)");
			$stmt->bindParam(1, $ip);
			$stmt->bindParam(2, $user_agent);
			$stmt->bindParam(3, $page);

			$stmt->execute();
		?>
	</head>

	<body>
		<center>
		<?
			if (isset($_POST['curses']))
			{
				//Read Input
				$input = trim(htmlspecialchars($_POST['curses']));
				$curses = explode("\n", $input);

				//Sample Curse Format:
				//Surathli's Anger on Looney: Targeted player will lose 200% more resources, power balance, 
				//workers and arcane chamber energy when attacked. (1 active shards left, 1 days, 21 hours, 21 minutes left, try to remove curse)
				$pattern = '/(.+) on(.+):.+\((.+) active shards left, (.+) days, (.+) hours, (.+) minutes left.+\)/';
				$count = 0;	

				foreach ($curses as $i) {
					preg_match($pattern, $i, $m);

					//Relevant information
					$curseType = trim($m[1]);
					$victim = trim($m[2]);
					$numOfShardsLeft = trim($m[3]);
					$days = $m[4];
					$hours = $m[5];
					$minutes = $m[6];
					$myNull = '0';

					if ($victim === "" || $numOfShardsLeft === "" || $days === "" || $hours === "" || $minutes === "")
					{
						die("There was an error parsing your data :( <br><strong>Here: </strong>".$i);
					}

					//Expiration Date (relative to the server's time)
					$date = date("Y-m-d H:i:s");
					$date = date('Y-m-d H:i:s', strtotime($date. " + $days days $hours hours $minutes minutes"));

					$insertStmt = $dbh->prepare("INSERT INTO ".Config::victims_table." (curseName, expireDate, user, numShards, crit) VALUES( ?, ?, ?, ?, ?)");
					
					if (strpos($curseType, "Surathli's") !== false)
					{
						$crit = '0';
						$curseName = Config::SA;
					}
					else if (strpos($curseType, "Metamorphosis") !== false)
					{
						$crit = implode(" ", explode(" ", $m[1], -1));
						$curseName = Config::META;
					}
					else if (strpos($curseType, "Doppelganger") !== false)
					{
						$crit = implode(" ", explode(" ", $m[1], -1));
						$curseName = Config::DOPPLE;
					}
					else if (strpos($curseType, "Jinx") !== false)
					{
						$crit = implode(" ", explode(" ", $m[1], -1));
						$curseName = Config::JINX;
					}
					else if (strpos($curseType, "Suffocation") != false)
					{
						$crit = implode(" ", explode(" ", $m[1], -1));	
						$curseName = Config::SUFFO;
					}
					else
					{
						die("Either the curses you entered weren't real or there was an issue parsing. :(");
					}

					//Check to see if this user is already in the database
					if ($curseName == "SA")
					{
						//Check if already exists, else add
						$stmt = $dbh->prepare("SELECT id FROM ". Config::victims_table ." WHERE user = ? AND curseName = ?");
						$stmt->bindParam(1, $victim);
						$stmt->bindParam(2, $curseName);

						$stmt->execute();

						if ($data = $stmt->fetch())
						{
							die("This curse exists already. Contact an admin if you want to update or remove this curse: <strong>".$i."</strong>");
						}
					}
					else
					{
						if ($crit === "")
						{
							die("There was an error parsing your data :( <br>Here: <strong>".$i."</strong><br>");
						}
					}

					$insertStmt->bindParam(1, $curseName);
					$insertStmt->bindParam(2, $date);
					$insertStmt->bindParam(3, $victim);
					$insertStmt->bindParam(4, $numOfShardsLeft);
					$insertStmt->bindParam(5, $crit);

					$insertStmt->execute();
					$count = $count + 1;
				}
				echo "Added ".$count." curse(s) successfully!";
				
				}

			?>
			<br>
			<h2>Active Curses:</h2><br>
			Active Curses can be found <a href="image.php">HERE</a> 
			<form name="curseForm" action="" method="POST">
				<h2>Paste Your Curses:</h2> <br><textarea name="curses" id="curses" class="textbox" cols="150" rows="12"></textarea>
				<br>
				<input type="submit" name="submit" value="Submit" id="submit"/>
			</form>
		</center>
	</body>
</html>