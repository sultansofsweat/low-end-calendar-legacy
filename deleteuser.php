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
    <title>Low End Calendar-Delete User</title>
    
  </head>
  <body>
  <p>
  <?php
	
	if(isset($_POST['s']) && $_POST['s'] == "y")
	{
		//Make sure user is an administrator
		if(isset($_SESSION['username']) && $_SESSION['username'] != "")
		{
			$db=open_db("db/calendar.sqlite",SQLITE3_OPEN_READWRITE);
			$user=get_user($db,$_SESSION['username']);
			if(isset($user[2]) && $user[2] >= 3)
			{
				//Begin submission
				if(isset($_POST['select']) && $_POST['select'] != "")
				{
					$login=preg_replace("/[^A-Za-z0-9]/","",$_POST['select']);
					if($login != "root" && $login != $user[0])
					{
						$debug=delete_user($db,$login);
					}
					else
					{
						$debug=false;
					}
					if($debug === true)
					{
						echo ("<script type=\"text/javascript\">window.location = \"index.php?deu=yes\"</script>");
					}
					else
					{
						echo ("<script type=\"text/javascript\">window.location = \"index.php?deu=no\"</script>");
					}
				}
				else
				{
					trigger_error("You must select a user first, you goat!",E_USER_WARNING);
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
			die("<script type=\"text/javascript\">window.location = \"index.php?bad=yes\"</script>");
		}
	}
	else
	{
		//Make sure user is an administrator
		if(isset($_SESSION['username']) && $_SESSION['username'] != "")
		{
			$db=open_db("db/calendar.sqlite",SQLITE3_OPEN_READONLY);
			$user=get_user($db,$_SESSION['username']);
			if(isset($user[2]) && $user[2] < 3)
			{
				die("<script type=\"text/javascript\">window.location = \"index.php?bad=yes\"</script>");
			}
			$users=get_all_users($db);
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
  <h1>Low End Calendar-Dump User</h1>
  <form method="post" action="deleteuser.php">
  <input type="hidden" name="s" value="y">
  Select a user: <select name="select">
  <?php
	if(count($users) > 0)
	{
		foreach($users as $userinfo)
		{
			if($userinfo[0] != "root" && $user[0] != $userinfo[0])
			{
				echo("<option value=\"" . $userinfo[0] . "\">" . $userinfo[0] . " (" . $userinfo[1] . ")</option>\r\n");
			}
		}
	}
  ?>
  </select><br>
  Are you sure you want to delete this user?<br>
  <input type="submit" value="Yes, dump them"> or <input type="button" value="Cancel" onclick="window.location.href='index.php'">
  </form>
  </body>
</html>