<?
	require('../config.php');
	if (isset($_POST['curses']))
	{
		//Read Input
		$input = trim(htmlspecialchars($_POST['curses']));
		$curses = explode("\n", $input);
		
	}
	else
	{
		die("No input. Copy & Paste your curses.");
	}

	//Connect to database
	try 
	{
		$dbh = new PDO("mysql:host=".Config::db_host.";dbname=".Config::db_name, Config::db_user, Config::db_pass);
	}
	catch(PDOException $e)
	{
		die($e->getMessage());
	}

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

?>