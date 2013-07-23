<?
	require('../config.php');
	if (!isset($_POST['list']) || !isset($_POST['user']) || $_POST['list'] == "" || $_POST['user'] == "")
	{
		die("Make sure both the user and list are valid.");
	}

	$user = htmlentities($_POST['user'], ENT_QUOTES);
	$list = htmlentities(nl2br($_POST['list']), ENT_QUOTES);

	//Connect to database
	try 
	{
		$dbh = new PDO("mysql:host=".Config::db_host.";dbname=".Config::db_name, Config::db_user, Config::db_pass);
	}
	catch(PDOException $e)
	{
		die($e->getMessage());
	}

	$stmt = $dbh->prepare("INSERT INTO ".Config::reveals_table." (user, list, submissionDate) VALUES (?, ?, NOW())");

	$stmt->bindParam(1, $user);
	$stmt->bindParam(2, $list);

	$stmt->execute();

	die("Successfully submitted reveal for: ". $user);
?>