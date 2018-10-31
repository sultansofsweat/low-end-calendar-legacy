<?php
	session_save_path("sessions");
	session_set_cookie_params(0,"/",$_SERVER['HTTP_HOST'],false,true);
	session_start();
	if(isset($_SESSION['style']) && file_exists("styles/" . $_SESSION['style'] . ".css"))
	{
		$style="styles/" . $_SESSION['style'] . ".css";
	}
	else
	{
		$style="styles/default.css";
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
    <title>Low End Calendar-Register</title>
    
  </head>
  <body>
  <p>
  <?php
	include("functions.php");
	$disable=false;
	if(isset($_POST['s']) && $_POST['s'] == "y")
	{
		//Begin submission
		if(isset($_POST['login']) && $_POST['login'] != "" && isset($_POST['name']) && $_POST['name'] != "" && isset($_POST['password']) && $_POST['password'] != "" && isset($_POST['cpassword']) && $_POST['cpassword'] != "")
		{
			$login=preg_replace("/[^A-Za-z0-9]/","",$_POST['login']);
			$name=preg_replace("/[^A-Za-z0-9 ]/","",$_POST['name']);
			if($_POST['password'] == $_POST['cpassword'])
			{
				$db=open_db("db/calendar.sqlite",SQLITE3_OPEN_READWRITE);
				$debug=insert_user($db,$name,$login,password_hash($_POST['password'],PASSWORD_DEFAULT),1);
				if($debug === true)
				{
					echo ("<script type=\"text/javascript\">window.location = \"index.php?reg=yes\"</script>");
				}
				else
				{
					echo ("<script type=\"text/javascript\">window.location = \"index.php?reg=no\"</script>");
				}
				$debug=close_db($db);
				if($debug === false)
				{
					trigger_error("The server has caused a criticality accident and the database became irradiated.",E_USER_WARNING);
				}
			}
			else
			{
				trigger_error("You only had ONE JOB! Passwords did not match!",E_USER_WARNING);
			}
		}
		else
		{
			trigger_error("Name, username and password cannot be blank, you goat!",E_USER_WARNING);
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
		//Make sure registration isn't disabled
		$db=open_db("db/calendar.sqlite",SQLITE3_OPEN_READONLY);
		if(get_setting($db,"allowregistration") == "no")
		{
			die ("<script type=\"text/javascript\">window.location = \"index.php?bad=yes\"</script>");
		}
		$debug=close_db($db);
		if($debug === false)
		{
			trigger_error("The server has caused a criticality accident and the database became irradiated.",E_USER_WARNING);
		}
	}
  ?>
  </p>
  <h1>Low End Calendar-Register</h1>
  <form method="post" action="register.php">
  <input type="hidden" name="s" value="y">
  <span title="Can contain spaces, numbers and letters. Use something descriptive."><u>Name</u></span>: <input type="text" name="name" value="<?php if(isset($_POST['name'])) { echo $_POST['name']; } ?>"><br>
  <span title="Can contain numbers and letters. Use something short and memorable."><u>Username</u></span>: <input type="text" name="login" value="<?php if(isset($_POST['login'])) { echo $_POST['login']; } ?>"><br>
  Password: <input type="password" name="password"><br>
  Confirm password: <input type="password" name="cpassword"><br>
  <input type="submit" value="Sign me up" <?php if($disable === true) { echo ("disabled=\"disabled\""); } ?>> or <input type="button" value="Cancel" onclick="window.location.href='index.php'">
  </form>
  </body>
</html>