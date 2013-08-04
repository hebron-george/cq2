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
			$page = Config::REVEAL_ALL;

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


			include('../menu.php');
		?>
		<title>Reveals Submission</title>
		<link rel="stylesheet" type="text/css" href="../main.css">
	</head>

	<body>
		<div id="list">
		<?
			$stmt = $dbh->prepare('SELECT list, submissionDate, userLevel FROM '.Config::reveals_table.' ORDER BY submissionDate DESC');
			$stmt->execute();

			$result = $stmt->fetchAll();

			print_r($result);
		?>
		</div>
	</body>
</html>