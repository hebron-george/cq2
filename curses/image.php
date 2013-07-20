<?php
	require('config.php');

	//Connect to database
	$con=mysqli_connect(Config::db_host,Config::db_user,Config::db_pass,Config::db_name) or die("fuck");
	//Check connection
	if (mysqli_connect_errno($con))
	{
	  	echo "Failed to connect to MySQL: " . mysqli_connect_error();
	  	die();
	}
	
	/* TODO: Make a loop that updates database here (Robert) */
	$stmt = "DELETE FORM sa_victims WHERE expireDate < NOW()";
	mysqli_query($con, $stmt);

	$stmt = "SELECT COUNT(id) FROM sa_victims;";
	$result = mysqli_query($con, $stmt);

	while($row = mysqli_fetch_array($result))
	{
		$rows = $row['COUNT(id)'];
	}


	$width = 600;
	$height = 50 * $rows;
	$my_img = imagecreate( $width, $height );
	$background = imagecolorallocate( $my_img, 0, 0, 0 );
	$text_colour = imagecolorallocate( $my_img, 255,100,000 );
	$line_colour = imagecolorallocate( $my_img, 075,075,075 );
	$Surathli_color = imagecolorallocate( $my_img, 255,100,000);
	imagestring ($my_img, 5, 10, 0, "Siralim - Surathli's Anger Victims", $Surathli_color);
	imagesetthickness ( $my_img, 3 );
	imageline( $my_img, 10, 18, 380, 18, $line_colour);
	$desc_color = imagecolorallocate( $my_img, 150,150,150);
	imagestring( $my_img, 4, 10, 20, "User: Time Left", $desc_color );

	$stmt = "SELECT id, expireDate, user, numShards FROM  sa_victims ORDER BY expireDate DESC";
	$result = mysqli_query($con, $stmt);

	$victim_buffer = 15;
	$previousY = 20;
	while ($row = mysqli_fetch_array($result))
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

		imagestring( $my_img, 4, 10, $previousY + $victim_buffer, $user.", ".$numShards." Shard(s): ".$days." days, ".$hours." hours, ".$minutes." minutes.", $text_colour );
		$previousY = $previousY + $victim_buffer;
	}



	header( "Content-type: image/png" );
	imagepng( $my_img );
	imagecolordeallocate( $line_color );
	imagecolordeallocate( $text_color );
	imagecolordeallocate( $background );
	imagedestroy( $my_img );
?>