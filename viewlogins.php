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
    <title>Low End Calendar-View User Login History</title>
    
  </head>
  <body>
  <p>
  <?php
	
	//Make sure user is signed in
	if(isset($_SESSION['username']) && $_SESSION['username'] != "")
	{
		$db=open_db("db/calendar.sqlite",SQLITE3_OPEN_READONLY);
		$user=get_user($db,$_SESSION['username']);
		if(!isset($user[2]) || $user[2] < 1)
		{
			die("<script type=\"text/javascript\">window.location = \"index.php?bad=yes\"</script>");
		}
		$logins=get_last_login($db);
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
  ?>
  </p>
  <h1>Low End Calendar-View User Login History</h1>
  <table width="100%">
  <?php
	if(isset($user[2]) && $user[2] > 2)
	{
		echo("<tr>\r\n<th>Login Name</th>\r\n<th>User Name</th>\r\n<th>Login Count</th>\r\n<th>Last Logged In</th>\r\n</tr>");
		foreach($logins as $login)
		{
			echo("<tr>\r\n");
			echo("<td>" . $login[0] . "</td>\r\n");
			echo("<td>" . $login[1] . "</td>\r\n");
			echo("<td>" . $login[2] . "</td>\r\n");
			echo("<td>");
			if($login[3] > 0)
			{
				echo date("l F jS, Y",$login[3]) . " at " . date("g:i A",$login[3]);
			}
			else
			{
				echo "Never";
			}
			echo("</td>\r\n");
			echo("</tr>\r\n");
		}
	}
	else
	{
		echo("<tr>\r\n<th>User Name</th>\r\n<th>Login Count</th>\r\n<th>Last Logged In</th>\r\n</tr>");
		foreach($logins as $login)
		{
			echo("<tr>\r\n");
			echo("<td>" . $login[1] . "</td>\r\n");
			echo("<td>" . $login[2] . "</td>\r\n");
			echo("<td>");
			if($login[3] > 0)
			{
				echo date("l F jS, Y",$login[3]) . " at " . date("g:i A",$login[3]);
			}
			else
			{
				echo "Never";
			}
			echo("</td>\r\n");
			echo("</tr>\r\n");
		}
	}
  ?>
  </table>
  <p><a href="index.php">Go back</a></p>
  </body>
</html>