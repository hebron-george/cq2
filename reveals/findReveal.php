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
			User:
			<input type="text" id="user" name="user"></input> [OR]
			Level:
			<input type="text" id="level" name="level"></input>
			<br>
			<input type="submit"></input>
		</form>
		<div id="list">
				<?
					if (isset($_GET['user']) && $_GET['user'] !== "")
					{
						$user = htmlentities($_GET['user'], ENT_QUOTES);


						$stmt = $dbh->prepare("SELECT list, submissionDate, userLevel FROM ".Config::reveals_table." WHERE user = ?");
						$stmt->bindParam(1, $user);

						$stmt->execute();

						$result = $stmt->fetchAll();
					    $currentDate = date('Y-m-d H:i:s');
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

					}

				?>
		</div>


	</body>
</html>