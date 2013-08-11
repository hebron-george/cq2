<?
	require('../config.php');
?>

<html>
	<head>
		<title>Find a Reveal</title>
		<link rel="stylesheet" type="text/css" href="../main.css">
		<?
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
			$page = Config::REVEAL_FIND;

			$stmt = $dbh->prepare("INSERT INTO ".Config::visitor_stats." (client_IP, user_agent, visited_time, visited_page) VALUES (?, ?, NOW(), ?)");
			$stmt->bindParam(1, $ip);
			$stmt->bindParam(2, $user_agent);
			$stmt->bindParam(3, $page);

			$stmt->execute();
		?>
	</head>

	<body> 
		<?
			include("../menu.php");
		?>
		<form action="findReveal.php" method="GET">
			<h2>Filters</h2>
			User:
			<input type="text" id="user" name="user"></input> <br>
			Level:
			<input type="text" id="level" name="level"></input> <br>
			List Search: 
			<input type="text" id="crit" name="crit"></input> (Ex: 'Death', 'Dragon', 'Rancid Imling', etc)
			<br>
			<input type="submit"></input>
		</form>
		<div id="list">
				<?
				    $currentDate = date('Y-m-d H:i:s');
					if (isset($_GET['user']) && $_GET['user'] !== "" && isset($_GET['level']) && $_GET['level'] !== "" && isset($_GET['crit']) && $_GET['crit'] !== "")
					{
						$user = htmlentities($_GET['user'], ENT_QUOTES);
						$level = htmlentities($_GET['level'], ENT_QUOTES);
						$crit = htmlentities($_GET['crit'], ENT_QUOTES);

						$stmt = $dbh->prepare("SELECT list, submissionDate, userLevel FROM ".Config::reveals_table." WHERE user = ? AND userLevel = ? AND list LIKE ? ORDER BY submissionDate DESC");

						$stmt->bindParam(1, $user);
						$stmt->bindParam(2, $level);
						$crit = '%'.$crit.'%';
						$stmt->bindParam(3, $crit);

						$stmt->execute();

						$result = $stmt->fetchAll();
						if (!isset($result[0]['list']) || $result[0]['list'] == "")
							echo "<h2>There is no result for those search parameters.</h2>";
						else
						{
							$list = $result[0]['list'];
						    $submissionDate = $result[0]['submissionDate'];
						    $userLevel = $result[0]['userLevel'];
					    	$days = floor((strtotime($currentDate) - strtotime($submissionDate))/(60*60*24));
							echo "<h2>".html_entity_decode($user)." - ".$userLevel."</h2>This reveal is $days days old.<br>";
							echo nl2br(htmlspecialchars($list, ENT_QUOTES, 'UTF-8'));

						}
					}
					else if (isset($_GET['user']) && $_GET['user'] !== "" && isset($_GET['level']) && $_GET['level'] !== "")
					{
						$user = htmlentities($_GET['user'], ENT_QUOTES);
						$level = htmlentities($_GET['level'], ENT_QUOTES);

						$stmt = $dbh->prepare("SELECT list, submissionDate, userLevel FROM ".Config::reveals_table." WHERE user = ? AND userLevel = ? ORDER BY submissionDate DESC");

						$stmt->bindParam(1, $user);
						$stmt->bindParam(2, $level);

						$stmt->execute();
						$result = $stmt->fetchAll();
					    $submissionDate = $result[0]['submissionDate'];
					    $userLevel = $result[0]['userLevel'];

					    if (isset($result[0][0]))
					    {
					    	$days = floor((strtotime($currentDate) - strtotime($submissionDate))/(60*60*24));
							echo "<h2>".html_entity_decode($user)." - ".$userLevel."</h2>This reveal is $days days old.<br>";
							echo nl2br(htmlspecialchars($result[0]['list'], ENT_QUOTES, 'UTF-8'));
						}
						else
						{
							echo "<h2>There is no result for user: ".$user." at level ".$level."</h2>";
						}

					}
					else if (isset($_GET['user']) && $_GET['user'] !== "" && isset($_GET['crit']) && $_GET['crit'] !== "")
					{
						$user = htmlentities($_GET['user'], ENT_QUOTES);
						$crit = htmlentities($_GET['crit'], ENT_QUOTES);

						$stmt = $dbh->prepare("SELECT list, submissionDate, userLevel FROM ".Config::reveals_table." WHERE user = ? AND list LIKE ? ORDER BY submissionDate DESC");

						$stmt->bindParam(1, $user);
						$crit = '%'.$crit.'%';
						$stmt->bindParam(2, $crit);

						$stmt->execute();
						$result = $stmt->fetchAll();
					    $submissionDate = $result[0]['submissionDate'];
					    $userLevel = $result[0]['userLevel'];

					    if (isset($submissionDate) && isset($result[0][0]))
					    {
					    	$days = floor((strtotime($currentDate) - strtotime($submissionDate))/(60*60*24));
							echo "<h2>".html_entity_decode($user)." - ".$userLevel."</h2>This reveal is $days days old.<br>";
							echo nl2br(htmlspecialchars($result[0]['list'], ENT_QUOTES, 'UTF-8'));
						}
						else
						{
							echo "<h2>There is no result for user: ".htmlspecialchars($user)." and search pattern: ".htmlspecialchars($crit)."</h2>";
						}
					}
					else if (isset($_GET['level']) && $_GET['level'] !== "" && isset($_GET['crit']) && $_GET['crit'] !== "")
					{
						$level = htmlentities($_GET['level'], ENT_QUOTES);
						$crit = htmlentities($_GET['crit'], ENT_QUOTES);

						$stmt = $dbh->prepare("SELECT user, submissionDate FROM ".Config::reveals_table." WHERE userLevel = ? AND list LIKE ? ORDER BY submissionDate DESC");

						$stmt->bindParam(1, $level);
						$crit = '%'.$crit.'%';
						$stmt->bindParam(2, $crit);

						$stmt->execute();
						$result = $stmt->fetchAll();

					    if (count($result) > 0)
					    {
					    	echo '<h2>Users</h2>';
					    	for ($i = 0; $i < count($result); $i++)
						    {
							    if (count($result) > 0)
							    {
					    			$submissionDate = $result[$i]['submissionDate'];
							    	$days = floor((strtotime($currentDate) - strtotime($submissionDate))/(60*60*24));
							    	$user = $result[$i]['user'];
									echo '<a href="findReveal.php?user='.htmlspecialchars($user).'">'.$user.'</a> - '.$days.' days old<br>';
								}
						    }
						}
						else
						{
							echo '<h2>There were no users found with level: '.htmlspecialchars($level).' and search pattern: '.htmlspecialchars($crit).'</h2>';
						}

					}
					else if (isset($_GET['user']) && $_GET['user'] !== "")
					{
						$user = htmlentities($_GET['user'], ENT_QUOTES);


						$stmt = $dbh->prepare("SELECT list, submissionDate, userLevel FROM ".Config::reveals_table." WHERE user = ? ORDER BY submissionDate DESC");
						$stmt->bindParam(1, $user);

						$stmt->execute();

						$result = $stmt->fetchAll();
					    $submissionDate = $result[0]['submissionDate'];
					    $userLevel = $result[0]['userLevel'];

					    if (isset($submissionDate) && isset($result[0][0]))
					    {
					    	$days = floor((strtotime($currentDate) - strtotime($submissionDate))/(60*60*24));
							echo "<h2>".html_entity_decode($user)." - ".$userLevel."</h2>This reveal is $days days old.<br>";
							echo nl2br(htmlspecialchars($result[0][0], ENT_QUOTES, 'UTF-8'));
						}
						else
						{
							echo "<h2>There is no result for user: ".html_entity_decode($user)."</h2>";
						}
					}
					else if (isset($_GET['level']) && $_GET['level'] !== "")
					{
						$level = htmlentities($_GET['level'], ENT_QUOTES);

						$stmt = $dbh->prepare("SELECT user, submissionDate, userLevel FROM ".Config::reveals_table." WHERE userLevel = ? ORDER BY submissionDate DESC");

						$stmt->bindParam(1, $level);

						$stmt->execute();
						$result = $stmt->fetchAll();

						if (count($result) > 0)
						{
							echo '<h2>Potential Targets</h2>';
							for ($i = 0; $i < count($result); $i++)
							{
					   			$submissionDate = $result[$i]['submissionDate'];
					    		$days = floor((strtotime($currentDate) - strtotime($submissionDate))/(60*60*24));
								echo '<a href="findReveal.php?user='.$result[$i]['user'].'">'.$result[$i]['user'].' - Level '.$result[$i]['userLevel'].' - '.$days.' days old<br>';								
							}
						}
						else
						{
							echo '<h2>There were no users found for level: '.$level.'</h2>';
						}
					}
					else if (isset($_GET['crit']) && $_GET['crit'] !== "")
					{
						$crit = htmlentities($_GET['crit'], ENT_QUOTES);

						$stmt = $dbh->prepare("SELECT user, submissionDate, userLevel FROM ".Config::reveals_table." WHERE list LIKE ? ORDER BY submissionDate DESC");

						$crit = '%'.$crit.'%';
						$stmt->bindParam(1, $crit);

						$stmt->execute();
						$result = $stmt->fetchAll();

						if (count($result) > 0)
						{
							echo '<h2>Potential Targets</h2>';
							for ($i = 0; $i < count($result); $i++)
							{
					   			$submissionDate = $result[$i]['submissionDate'];
					    		$days = floor((strtotime($currentDate) - strtotime($submissionDate))/(60*60*24));
								echo '<a href="findReveal.php?user='.$result[$i]['user'].'">'.$result[$i]['user'].' - Level '.$result[$i]['userLevel'].' - '.$days.' days old<br>';
							}
						}
						else
						{
							echo '<h2>No results were return with your search critera</h2>';
						}
					}

					if ($_GET['user'] !== "" || $_GET['level'] !== "" || $_GET['crit'] !== "")
					{
						$search = "user=".$_GET['user'].'&level='.$_GET['level'].'&crit='.$_GET['crit'];
						$stmt = $dbh->prepare("INSERT INTO ".Config::searches_table." (client_IP, search, submissionDate, visited_page) VALUES (?, ?, ?, ?)");
						$stmt->bindParam(1, $ip);
						$stmt->bindParam(2, $search);
						$stmt->bindParam(3, $currentDate);
						$page = Config::REVEAL_FIND;
						$stmt->bindParam(4, $page);

						$stmt->execute();
					}

				?>
		</div>


	</body>
</html>