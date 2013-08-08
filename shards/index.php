<htm>
	<head>
		<?
			require('../config.php');

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
			$page = Config::SHARDS_INDEX;

			//Connect to database
			try 
			{
				$dbh = new PDO("mysql:host=".Config::db_host.";dbname=".Config::db_name, Config::db_user, Config::db_pass);
			}
			catch(PDOException $e)
			{
				die($e->getMessage());
			}

			$stmt = $dbh->prepare("INSERT INTO ".Config::visitor_stats." (client_IP, user_agent, visited_time, visited_page) VALUES (?, ?, NOW(), ?)");
			$stmt->bindParam(1, $ip);
			$stmt->bindParam(2, $user_agent);
			$stmt->bindParam(3, $page);

			$stmt->execute();
		?>
		<title>Shards</title>
		<link rel="stylesheet" type="text/css" href="../main.css">
	</head>
	<body>
		<?
			include("../menu.php");

			if (isset($_POST['user']) && strlen($_POST['user']) > 0 && isset($_POST['shards']) && strlen($_POST['shards']) > 0)
			{
				$user = trim(htmlentities($_POST['user'], ENT_QUOTES));
				$shards = trim(htmlentities($_POST['shards']), ENT_QUOTES);

				$stmt = $dbh->prepare("DELETE FROM ".Config::shards_table." WHERE user=?");
				$stmt->bindParam(1, $user);
				$stmt->execute();

				$lines = explode(PHP_EOL, $shards);

				$pattern = "/(.+) Shard,.+\((.+)\)/";
				foreach($lines as $i)
				{
					preg_match($pattern, $i, $m);

					$shard = trim($m[1]);
					$amount = trim($m[2]);

					$stmt = $dbh->prepare("INSERT INTO ".Config::shards_table." (user, shard, amount, submissionDate) VALUES (?, ?, ?, NOW())");
					$stmt->bindParam(1, $user);
					$stmt->bindParam(2, $shard);
					$stmt->bindParam(3, $amount);

					$stmt->execute();
				}
			}
		?>
		<center>
			<form action="" method="POST">
				<h2>Submit Your Shards</h2>
				<br>
				Mage:
				<br>
				<input id="user" class="textbox" name="user" type="text"></input>
				<br>
				Shards:
				<br>
				<textarea id="shards" class="textbox" rows="25" cols="90" name="shards" type="text"></textarea>
				<br>
				<input type="submit"></input>
			</form>
		</center>
	</body>
</htm>