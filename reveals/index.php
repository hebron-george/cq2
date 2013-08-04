<html>
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
			$page = Config::REVEAL_INDEX;

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
		<title>Reveals Submission</title>
		<link rel="stylesheet" type="text/css" href="../main.css">
	</head>

	<body>
		<?
			include("../menu.php");
			if (isset($_POST['sublist']) && isset($_POST['user']) && isset($_POST['userLevel']) && strlen($_POST['sublist']) > 0 && strlen($_POST['user']) > 0 && strlen($_POST['userLevel']) > 0)
			{
				
				$user = trim(htmlentities($_POST['user'], ENT_QUOTES));
				$list = htmlentities($_POST['sublist'], ENT_QUOTES);
				$userLevel = trim(htmlentities($_POST['userLevel'], ENT_QUOTES));

				if (!is_int(intval($userLevel)))
					$userLevel = 0;

				$stmt = $dbh->prepare("SELECT COUNT(id) FROM ".Config::reveals_table." WHERE user=?");
				$stmt->bindParam(1, $user);
				$stmt->execute();
				$result = $stmt->fetchAll();
				if ($result[0][0] > 0)
				{
					echo "user level: $userLevel";
					$stmt = $dbh->prepare("UPDATE ".Config::reveals_table." SET submissionDate=NOW(), list=?, client_IP=?, userLevel=? WHERE user=?");
					$stmt->bindParam(1, $list);
					$stmt->bindParam(2, $ip);
					$stmt->bindParam(3, $userLevel);
					$stmt->bindParam(4, $user);
					$stmt->execute();
				}
				else
				{
					$stmt = $dbh->prepare("INSERT INTO ".Config::reveals_table." (user, list, submissionDate, client_IP, userLevel) VALUES (?, ?, NOW(), ?, ?)");
					$stmt->bindParam(1, $user);
					$stmt->bindParam(2, $list);
					$stmt->bindParam(3, $ip);
					$stmt->bindParam(4, $userLevel);

					$stmt->execute();

				}

				echo ("Successfully submitted reveal for: ". $user);
			}
		?>
		<center>		
		<div id="subUsers">
		<h2>Recently Submitted Users </h2>
		<br>
		<?
			$stmt = $dbh->prepare("SELECT user, userLevel, submissionDate FROM reveals ORDER BY id DESC LIMIT 5");
			$stmt->execute();

			$result = $stmt->fetchAll();
		    $currentDate = date('Y-m-d H:i:s');

			for($i = 0; $i < count($result); $i++)
			{
				$submissionDate = $result[$i]['submissionDate'];
		    	$days = floor((strtotime($currentDate) - strtotime($submissionDate))/(60*60*24));
				echo '<a href="./findReveal.php?user='.$result[$i]['user'].'">('.$result[$i]['userLevel'].') '.$result[$i]['user']." - $days days old".'</a><br>';
			}
		?>
		</div>
		<br>
		<h2>Find a Reveal</h2>
		<a href="findReveal.php">Looking for a specific reveal?</a>
		<br>
		<a href="allReveals.php">Looking for all reveals?</a>

		<h2>Submit a Reveal</h2>
		<br>
		<form action="" method="POST">
			Mage: 
			<br>
			<input id="user" class="textbox" name="user" type="text"></input>
			<br>
			Level:
			<br>
			<input id="userLevel" class="textbox" name="userLevel" type="text" maxlength="2"></input>
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