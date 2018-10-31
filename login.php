<?php
	session_save_path("sessions");
	session_set_cookie_params(0,"/",$_SERVER['HTTP_HOST'],false,true);
	session_start();
	include("functions.php");
	if(isset($_SESSION['style']) && file_exists("styles/" . $_SESSION['style'] . ".css"))
	{
		$style="styles/" . $_SESSION['style'] . ".css";
	}
	else
	{
		$style=get_default_style();
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <meta name="generator" content="CoffeeCup HTML Editor (www.coffeecup.com)">
    <meta name="created" content="Sat, 25 Nov 2017 05:38:17 GMT">
    <meta name="description" content="">
    <meta name="keywords" content="">
	<link rel="stylesheet" type="text/css" href="<?php echo $style; ?>">
    <title>Low End Calendar-Log In</title>
    
  </head>
  <body>
  <p>
  <?php
	
	if(isset($_POST['s']) && $_POST['s'] == "y")
	{
		//Begin submission
		$db=open_db("db/calendar.sqlite",SQLITE3_OPEN_READONLY);
		if(isset($_POST['name']) && $_POST['name'] != "" && isset($_POST['password']) && $_POST['password'] != "")
		{
			$login=preg_replace("/[^A-Za-z0-9]/","",$_POST['name']);
			if(password_verify($_POST['password'],get_password($db,$login)) === true)
			{
				$_SESSION['username']=$login;
				//setcookie("username",$login,0,getcwd(),"",false,true);
				echo ("<script type=\"text/javascript\">document.cookie = \"username=$login;httponly\"</script>");
				$userinfo=get_user($db,$login);
				if(isset($userinfo[1]) && $userinfo[1] != "")
				{
					$_SESSION["name"]=$userinfo[1];
				}
				if(isset($userinfo[3]) && $userinfo[3] != "")
				{
					$_SESSION["timezone"]=$userinfo[3];
				}
				if(isset($userinfo[4]) && $userinfo[4] != "")
				{
					$_SESSION["style"]=$userinfo[4];
				}
				echo ("<script type=\"text/javascript\">window.location = \"index.php?in=yes\"</script>");
			}
			else
			{
				trigger_error("You only had ONE JOB! The password you supplied for user $login was INCORRECT!",E_USER_WARNING);
			}
		}
		else
		{
			trigger_error("Username and password cannot be blank, you goat!",E_USER_WARNING);
		}
		$debug=close_db($db);
		if($debug === false)
		{
			trigger_error("The server has caused a criticality accident and the database became irradiated.",E_USER_WARNING);
		}
	}
	else
	{
		//Check if already logged in
		if(isset($_SESSION['username']) && $_SESSION['username'] != "")
		{
			trigger_error("What are you doing here? You are already logged in! Get out before a GPX clock radio hits you!");
			$disable=true;
		}
		else
		{
			$disable=false;
		}
	}
  ?>
  </p>
  <h1>Low End Calendar-Log In</h1>
  <form method="post" action="login.php">
  <input type="hidden" name="s" value="y">
  Username: <input type="text" name="name"><br>
  Password: <input type="password" name="password"><br>
  <input type="submit" value="Log me in" <?php if($disable === true) { echo ("disabled=\"disabled\""); } ?>> or <input type="button" value="Cancel" onclick="window.location.href='index.php'">
  </form>
  </body>
</html>