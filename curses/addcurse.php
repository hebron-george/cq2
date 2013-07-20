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

	//Meta Format:
	//Priest of Light Metamorphosis on Beauregard: Every battle the creature transforms into a 
	//new version with only 1-60% of its original damage/health. (1 active shards left, 2 days, 11 hours, 04 minutes left, try to remove curse)
	$meta_pattern = 

	//Parse Input
	//SA Format:
	//Surathli's Anger on Looney: Targeted player will lose 200% more resources, power balance, 
	//workers and arcane chamber energy when attacked. (1 active shards left, 1 days, 21 hours, 21 minutes left, try to remove curse)
	$pattern = '/(.+) on(.+):.+\((.+) active shards left, (.+) days, (.+) hours, (.+) minutes left.+\)/';
	$count = 0;

	foreach ($curses as $i) {
		preg_match($pattern, $i, $m);

		$curseType = $i;
		if (strpos($curseType, "Surathli's") !== false)
		{
			//Relevant information
			$victim = trim($m[2]);
			$numOfShardsLeft = trim($m[3]);
			$days = $m[4];
			$hours = $m[5];
			$minutes = $m[6];

			if ($victim === "" || $numOfShardsLeft === "" || $days === "" || $hours === "" || $minutes === "")
			{
				die("There was an error parsing your data :( <br> <strong>Here: </strong>".$i);
			}

			//Expiration Date (relative to the server's time)
			$date = date("Y-m-d H:i:s");
			$date = date('Y-m-d H:i:s', strtotime($date. " + $days days $hours hours $minutes minutes"));

			//Submit to database
			$con=mysqli_connect(Config::db_host,Config::db_user,Config::db_pass,Config::db_name) or die("fuck");
			//Check connection
			if (mysqli_connect_errno($con))
			{
			  	echo "Failed to connect to Database.";
			  	die();
			}
			$stmt = "SELECT id FROM ".Config::db_table." WHERE user='".$victim."' AND curseName = '".Config::SA."'";
			$result = mysqli_query($con, $stmt);
			if ($result !== false && mysqli_num_rows($result) > 0)
			{
				die("This user already exists.");
			}
			$stmt = "INSERT INTO ".Config::db_table." (curseName, expireDate, user, numShards) VALUES('".Config::SA."', '".$date."', '".$victim."', '".$numOfShardsLeft."')";
			echo $stmt;
			mysqli_query($con, $stmt);
			mysqli_close($con);
			$count = $count + 1;
		}
		else if (strpos($curseType, "Metamorphosis") !== false)
		{
			$crit = implode(" ", explode(" ", $m[1], -1));
			$victim = trim($m[2]);
			$numOfShardsLeft = trim($m[3]);
			$days = $m[4];
			$hours = $m[5];
			$minutes = $m[6];

			if ($crit === "" || $victim === "" || $numOfShardsLeft === "" || $days === "" || $hours === "" || $minutes === "")
			{
				die("There was an error parsing your data :( <br> <strong>Here: </strong>".$i);
			}

			//Expiration Date (relative to the server's time)
			$date = date("Y-m-d H:i:s");
			$date = date('Y-m-d H:i:s', strtotime($date. " + $days days $hours hours $minutes minutes"));

			//Submit to database
			$con=mysqli_connect(Config::db_host,Config::db_user,Config::db_pass,Config::db_name) or die("fuck");
			//Check connection
			if (mysqli_connect_errno($con))
			{
			  	echo "Failed to connect to Database.";
			  	die();
			}

			$stmt = "INSERT INTO ".Config::db_table." (curseName, expireDate, user, numShards, crit) VALUES ('".Config::META."', '".$date."', '".$victim."', '".$numOfShardsLeft."', '".$crit."')";
			echo $stmt;
			mysqli_query($con, $stmt);
			mysqli_close($con);
			$count = $count + 1;
		}
		else
		{
			die("Either the curses you entered weren't Surathli's Anger or there was an issue parsing. :(");
		}
	}

	echo "Added ".$count." curses successfully!";

?>