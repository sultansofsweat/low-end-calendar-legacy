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
    <title>Low End Calendar-Add User</title>
    
  </head>
  <body>
  <p>
  <?php
	include("functions.php");
	if(isset($_POST['s']) && $_POST['s'] == "y")
	{
		//Make sure user is an administrator
		if(isset($_SESSION['username']) && $_SESSION['username'] != "")
		{
			$db=open_db("db/calendar.sqlite",SQLITE3_OPEN_READWRITE);
			$user=get_user($db,$_SESSION['username']);
			if(isset($user[2]) && $user[2] >= 4)
			{
				//Begin submission
				if(isset($_POST['login']) && $_POST['login'] != "" && isset($_POST['name']) && $_POST['name'] != "" && isset($_POST['password']) && $_POST['password'] != "" && isset($_POST['cpassword']) && $_POST['cpassword'] != "" && isset($_POST['privileges']) && $_POST['privileges'] != "")
				{
					$login=preg_replace("/[^A-Za-z0-9]/","",$_POST['login']);
					$name=preg_replace("/[^A-Za-z0-9 ]/","",$_POST['name']);
					$level=preg_replace("/[^0-9]/","",$_POST['privileges']);
					if($level < 1 || $level > 3)
					{
						$level=1;
					}
					if($_POST['password'] == $_POST['cpassword'])
					{
						$debug=insert_user($db,$name,$login,password_hash($_POST['password'],PASSWORD_DEFAULT),$level);
						if($debug === true)
						{
							echo ("<script type=\"text/javascript\">window.location = \"index.php?adu=yes\"</script>");
						}
						else
						{
							echo ("<script type=\"text/javascript\">window.location = \"index.php?adu=no\"</script>");
						}
					}
					else
					{
						trigger_error("You only had ONE JOB! Passwords did not match!",E_USER_WARNING);
					}
				}
				else
				{
					trigger_error("Name, username, password and privilege level cannot be blank, you goat!",E_USER_WARNING);
				}
			}
			else
			{
				die ("<script type=\"text/javascript\">window.location = \"index.php?bad=yes\"</script>");
			}
			$debug=close_db($db);
			if($debug === false)
			{
				trigger_error("The server has caused a criticality accident and the database became irradiated.",E_USER_WARNING);
			}
		}
		else
		{
			die ("<script type=\"text/javascript\">window.location = \"index.php?bad=yes\"</script>");
		}
	}
	else
	{
		//Make sure user is an administrator
		if(isset($_SESSION['username']) && $_SESSION['username'] != "")
		{
			$db=open_db("db/calendar.sqlite",SQLITE3_OPEN_READONLY);
			$user=get_user($db,$_SESSION['username']);
			if(isset($user[2]) && $user[2] < 4)
			{
				die ("<script type=\"text/javascript\">window.location = \"index.php?bad=yes\"</script>");
			}
			$debug=close_db($db);
			if($debug === false)
			{
				trigger_error("The server has caused a criticality accident and the database became irradiated.",E_USER_WARNING);
			}
		}
		else
		{
			//Automatically deny access
			die ("<script type=\"text/javascript\">window.location = \"index.php?bad=yes\"</script>");
		}
	}
  ?>
  </p>
  <h1>Low End Calendar-Add User</h1>
  <form method="post" action="adduser.php">
  <input type="hidden" name="s" value="y">
  Name: <input type="text" name="name" value="<?php if(isset($_POST['name'])) { echo $_POST['name']; } ?>"><br>
  Username: <input type="text" name="login" value="<?php if(isset($_POST['login'])) { echo $_POST['login']; } ?>"><br>
  Password: <input type="password" name="password"><br>
  Confirm password: <input type="password" name="cpassword"><br>
  User level: <select name="privileges">
  <option value="1">Viewer only</option>
  <option value="2">Regular user</option>
  <option value="3">Administrator</option>
  </select><br>
  <input type="submit" value="Sign user up"> or <input type="button" value="Cancel" onclick="window.location.href='index.php'">
  </form>
  </body>
</html>