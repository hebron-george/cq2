<?

	session_start();
	
	function isLoggedIn()
	{
	    if(isset($_SESSION['valid']) && $_SESSION['valid'] == 1)
	        return true;
	    return false;
	}

	function logout()
	{
	    $_SESSION = array(); //destroy all of the session variables
	    session_destroy();
	}

	function validateUser($username)
	{
		$_SESSION['valid'] = 1;
		$_SESSION['username'] = $username;
	}
?>	