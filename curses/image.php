<?php
	require('../config.php');


	//Connect to database
	try 
	{
		$dbh = new PDO("mysql:host=".Config::db_host.";dbname=".Config::db_name, Config::db_user, Config::db_pass);
	}
	catch(PDOException $e)
	{
		die($e->getMessage());
	}

	//Remove expired curses
	$count = $dbh->exec("DELETE FROM ".Config::victims_table." WHERE expireDate < NOW()"); 

	$stmt = $dbh->prepare("SELECT COUNT(id) FROM victims");
	$stmt->execute();

	if ($data = $stmt->fetch(PDO::FETCH_LAZY))
	{
		$rows = $data['COUNT(id)'];
		if ($rows == '0')
		{
			$rows = 5;
		}
	}

	$width = 1024;
	$height = 20 * $rows + 100;
	$my_img = imagecreate( $width, $height );
	$background = imagecolorallocate( $my_img, 0, 0, 0 );
	$text_colour = imagecolorallocate( $my_img, 255,100,000 );
	$line_colour = imagecolorallocate( $my_img, 075,075,075 );
	$Surathli_color = imagecolorallocate( $my_img, 255,100,000);
	imagestring ($my_img, 5, 10, 0, "Siralim - Curse Victims", $Surathli_color);
	imagesetthickness ( $my_img, 3 );
	imageline( $my_img, 10, 18, 380, 18, $line_colour);
	$desc_color = imagecolorallocate( $my_img, 150,150,150);
	imagestring( $my_img, 4, 10, 20, "User: Time Left", $desc_color );

	$stmt = $dbh->prepare("SELECT id, expireDate, user, numShards, crit, curseName FROM ".Config::victims_table." ORDER BY expireDate DESC");
	$stmt->execute();

	$result = $stmt->fetchAll();
	//$result = mysqli_query($con, $stmt);

	$victim_buffer = 15;
	$previousY = 20;
	foreach ($result as $row)
	{
		//$row['id']
		$user = $row['user'];
		$now = date("Y-m-d H:i:s");
		$time = $row['expireDate'];
		$time = strtotime($time) - strtotime($now);
		$days = floor($time/(24 * 60 * 60));
		$time = $time-(24*60*60*$days);
		$hours = floor($time/(60*60));
		$time = $time - (60*60*$hours);
		$minutes = floor($time/60);
		$numShards = $row['numShards'];
		$crit = $row['crit'];
		$curseName = $row['curseName'];

		if ($crit == '0')
		{
			imagestring( $my_img, 4, 10, $previousY + $victim_buffer, "(SA) ".$user.", ".$numShards." Shard(s): ".$days." days, ".$hours." hours, ".$minutes." minutes.", $text_colour );
		}
		else
		{
			imagestring( $my_img, 4, 10, $previousY + $victim_buffer, "(".$curseName.") ".$crit." - ".$user.", ".$numShards." Shard(s): ".$days." days, ".$hours." hours, ".$minutes." minutes.", $text_colour );
		} 
		$previousY = $previousY + $victim_buffer;
	}



	header( "Content-type: image/png" );
	imagepng( $my_img );
	imagecolordeallocate( $line_color );
	imagecolordeallocate( $text_color );
	imagecolordeallocate( $background );
	imagedestroy( $my_img );
?>