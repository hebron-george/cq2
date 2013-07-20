<?
	require('config.php');
	if (isset($_POST['curses']))
	{
		//Read Input
		$input = trim(htmlspecialchars($_POST['curses']));
		$curses = explode("\n", $input);
		
	}
	else
	{
		die("No input. Copy & Paste your SA curses.");
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

	//Meta Format:
	//Priest of Light Metamorphosis on Beauregard: Every battle the creature transforms into a 
	//new version with only 1-60% of its original damage/health. (1 active shards left, 2 days, 11 hours, 04 minutes left, try to remove curse)

	//SA Format:
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

		if ($victim === "" || $numOfShardsLeft === "" || $days === "" || $hours === "" || $minutes === "")
		{
			die("There was an error parsing your data :( <br><strong>Here: </strong>".$i);
		}

		//Expiration Date (relative to the server's time)
		$date = date("Y-m-d H:i:s");
		$date = date('Y-m-d H:i:s', strtotime($date. " + $days days $hours hours $minutes minutes"));

		$insertStmt = $dbh->prepare("INSERT INTO ".Config::db_table." (curseName, expireDate, user, numShards, crit) VALUES( ?, ?, ?, ?, ?)")
		;
		if (strpos($curseType, "Surathli's") !== false)
		{
			//Check to see if this user is already in the database with SA
			$curseName = Config::SA;
			$stmt = $dbh->prepare("SELECT id FROM ". Config::db_table ." WHERE user = ? AND curseName = ?");
			$stmt->bindParam(1, $victim);
			$stmt->bindParam(2, $curseName);

			$stmt->execute();

			if ($data = $stmt->fetch())
			{
				die("This curse exists already. Contact an admin if you want to update or remove this curse: <strong>".$i."</strong>");
			}

			//Since the user is not in the database with SA already, add them
			$myNull = null;
			$insertStmt->bindParam(5, $myNull);

			$count = $count + 1;
		}
		else if (strpos($curseType, "Metamorphosis") !== false)
		{
			$crit = implode(" ", explode(" ", $m[1], -1));

			if ($crit === "")
			{
				die("There was an error parsing your data :( <br> <strong>Here: </strong>".$i);
			}

			//Check to see if this user is already in the database with Meta
			$curseName = Config::META;
			$stmt = $dbh->prepare("SELECT id FROM ". Config::db_table ." WHERE user = ? AND curseName = ? AND crit = ?");
			$stmt->bindParam(1, $victim);
			$stmt->bindParam(2, $curseName);
			$stmt->bindParam(3, $crit);

			$stmt->execute();

			if ($data = $stmt->fetch())
			{
				die("This curse exists already. Contact an admin if you want to update or remove this curse: <strong>".$i."</strong>");
			}

			$insertStmt->bindParam(5, $crit);

			$count = $count + 1;
		}
		else
		{
			die("Either the curses you entered weren't Surathli's Anger or there was an issue parsing. :(");
		}


		$insertStmt->bindParam(1, $curseName);
		$insertStmt->bindParam(2, $date);
		$insertStmt->bindParam(3, $victim);
		$insertStmt->bindParam(4, $numOfShardsLeft);

		$insertStmt->execute();
	}

	echo "Added ".$count." curse(s) successfully!";

?>