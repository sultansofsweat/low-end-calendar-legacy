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
    <title>Low End Calendar-Submit Request For Editing Permissions</title>
    
  </head>
  <body>
  <p>
  <?php
	include("functions.php");
	
	if(isset($_POST['s']) && $_POST['s'] == "y")
	{
		//Make sure user is logged in
		if(isset($_SESSION['username']) && $_SESSION['username'] != "")
		{
			$db=open_db("db/calendar.sqlite",SQLITE3_OPEN_READWRITE);
			$user=get_user($db,$_SESSION['username']);
			if(isset($user[2]) && $user[2] == 1)
			{
				//Begin submission
				$user=preg_replace("/[^A-Za-z0-9]/","",$_POST['user']);
				$details=filter_var($_POST['description'],FILTER_SANITIZE_STRING);
				
				$debug=insert_ticket($db,$user,"Supply edit rights",$details);
				if($debug === true)
				{
					echo ("<script type=\"text/javascript\">window.location = \"index.php?atk=yes\"</script>");
				}
				else
				{
					echo ("<script type=\"text/javascript\">window.location = \"index.php?atk=no\"</script>");
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
		//Make sure user is banned
		if(isset($_SESSION['username']) && $_SESSION['username'] != "")
		{
			$db=open_db("db/calendar.sqlite",SQLITE3_OPEN_READONLY);
			$user=get_user($db,$_SESSION['username']);
			if(isset($user[2]) && $user[2] != 1)
			{
				die("<script type=\"text/javascript\">window.location = \"index.php?bad=yes\"</script>");
			}
			$user=$user[0];
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
  <h1>Low End Calendar-Submit Request For Editing Permissions</h1>
  <form method="post" action="reqedit.php">
  <input type="hidden" name="s" value="y">
  <input type="hidden" name="user" value="<?php echo $user; ?>">
  Describe why you should be allowed to edit this calendar:<br>
  <textarea rows="10" cols="50" name="description" required="required"><?php if(isset($_POST['description'])) { echo stripcslashes($_POST['description']); } ?></textarea><br>
  <input type="submit" value="Submit ticket"> or <input type="button" value="Cancel" onclick="window.location.href='index.php'">
  </form>
  </body>
</html>