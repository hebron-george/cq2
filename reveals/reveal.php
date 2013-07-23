<?
	require('../config.php');
	if (!isset($_POST['list']) || !isset($_POST['user']) || $_POST['list'] == "" || $_POST['user'] == "")
	{
		die("Make sure both the user and list are valid.");
	}

	$user = htmlentities($_POST['user'], ENT_QUOTES);
	$list = htmlentities(nl2br($_POST['list']), ENT_QUOTES);

	

?>