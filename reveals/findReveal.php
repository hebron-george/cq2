<?
	require('../config.php');
	if (!isset($_POST['user']))
	{
		die("No input given.");
	}

	$user = htmlentities($_POST['user'], ENT_QUOTES);

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

    $days = floor((strtotime($currentDate) - strtotime($submissionDate))/(60*60*24));

	echo "<h2>".html_entity_decode($user)."</h2>This reveal is $days days old.<br>";
	echo html_entity_decode($result[0][0]);
?>