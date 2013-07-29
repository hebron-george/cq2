<?
	require('../config.php');
?>

<html>
	<head>
		<title>Find a Reveal</title>
		<link rel="stylesheet" type="text/css" href="reveals.css">
	</head>

	<body> 
		<form action="findReveal.php" method="GET">
			User:
			<input type="text" id="user" name="user"></input>
			<br>
			<input type="submit"></input>
		</form>
		<div id="list">
				<?
					if (isset($_GET['user']) && $_GET['user'] !== "")
					{
						$user = htmlentities($_GET['user'], ENT_QUOTES);

						//Connect to database
						try 
						{
							$dbh = new PDO("mysql:host=".Config::db_host.";dbname=".Config::db_name, Config::db_user, Config::db_pass);
						}
						catch(PDOException $e)
						{
							die($e->getMessage());
						}

						$stmt = $dbh->prepare("SELECT list, submissionDate FROM ".Config::reveals_table." WHERE user = ?");
						$stmt->bindParam(1, $user);

						$stmt->execute();

						$result = $stmt->fetchAll();
					    $currentDate = date('Y-m-d H:i:s');
					    $submissionDate = $result[0]['submissionDate'];

					    if (isset($submissionDate) && isset($result[0][0]))
					    {
					    	$days = floor((strtotime($currentDate) - strtotime($submissionDate))/(60*60*24));
							echo "<h2>".html_entity_decode($user)."</h2>This reveal is $days days old.<br>";
							echo nl2br(htmlspecialchars($result[0][0], ENT_QUOTES, 'UTF-8'));
						}
						else
						{
							echo "<h2>There is no result for user: ".html_entity_decode($user)."</h2>";
						}
					}

				?>
		</div>


	</body>
</html>