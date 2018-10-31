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
    <title>Low End Calendar-Change System Settings</title>
    
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
				if(isset($_POST['name']) && $_POST['name'] != "" && isset($_POST['timezone']) && $_POST['timezone'] != "" && isset($_POST['open']) && $_POST['open'] != "" && isset($_POST['reg']) && $_POST['reg'] != "")
				{
					$name=filter_var($_POST['name'],FILTER_SANITIZE_STRING);
					$timezone=filter_var($_POST['timezone'],FILTER_SANITIZE_STRING);
					switch($_POST['open'])
					{
						case "yes":
						$open="yes";
						break;
						case "no":
						default:
						$open="no";
						break;
					}
					switch($_POST['reg'])
					{
						case "yes":
						$reg="yes";
						break;
						case "no":
						default:
						$reg="no";
						break;
					}
					$debug=set_setting($db,"calendarname",$name);
					if($debug === true)
					{
						$debug=set_setting($db,"allowregistration",$reg);
						if($debug === true)
						{
							$debug=set_setting($db,"openviewing",$open);
							if($debug === true)
							{
								$debug=set_setting($db,"timezone",$timezone);
								if($debug === true)
								{
									echo ("<script type=\"text/javascript\">window.location = \"index.php?set=yes\"</script>");
								}
								else
								{
									echo ("<script type=\"text/javascript\">window.location = \"index.php?set=no\"</script>");
								}
							}
							else
							{
								echo ("<script type=\"text/javascript\">window.location = \"index.php?set=no\"</script>");
							}
						}
						else
						{
							echo ("<script type=\"text/javascript\">window.location = \"index.php?set=no\"</script>");
						}
					}
					else
					{
						echo ("<script type=\"text/javascript\">window.location = \"index.php?set=no\"</script>");
					}
				}
				else
				{
					trigger_error("You left a field blank, you goat!",E_USER_WARNING);
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
			if(isset($user[2]) && $user[2] < 3)
			{
				die("<script type=\"text/javascript\">window.location = \"index.php?bad=yes\"</script>");
			}
			$reg=get_setting($db,"allowregistration");
			$open=get_setting($db,"openviewing");
			$timezone=get_setting($db,"timezone");
			$name=get_setting($db,"calendarname");
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
  <h1>Low End Calendar-Change System Settings</h1>
  <form method="post" action="system.php">
  <input type="hidden" name="s" value="y">
  Calendar Name: <input type="text" name="name" value="<?php if(isset($_POST['name'])) { echo stripcslashes($_POST['name']); } elseif(isset($name)) { echo stripcslashes($name); } ?>"><br>
  Enable registration: <input type="radio" name="reg" value="yes" <?php if(isset($_POST['reg']) && $_POST['reg'] == "yes") { echo "checked=\"checked\""; } elseif(isset($reg) && $reg == "yes") { echo "checked=\"checked\""; } ?>>Yes | <input type="radio" name="reg" value="no" <?php if(isset($_POST['reg']) && $_POST['reg'] == "no") { echo "checked=\"checked\""; } elseif(isset($reg) && $reg == "no") { echo "checked=\"checked\""; } ?>>No<br>
  Allow logged out users to see events: <input type="radio" name="open" value="yes" <?php if(isset($_POST['open']) && $_POST['open'] == "yes") { echo "checked=\"checked\""; } elseif(isset($open) && $open == "yes") { echo "checked=\"checked\""; } ?>>Yes | <input type="radio" name="open" value="no" <?php if(isset($_POST['open']) && $_POST['open'] == "no") { echo "checked=\"checked\""; } elseif(isset($open) && $open == "no") { echo "checked=\"checked\""; } ?>>No<br>
  Default <a href="http://firealarms.redbat.ca/timezone/index.php">timezone</a>: <input type="text" name="timezone" value="<?php if(isset($_POST['timezone'])) { echo $_POST['timezone']; } elseif(isset($timezone)) { echo $timezone; }?>"><br>
  <input type="submit" value="Change settings"> or <input type="button" value="Cancel" onclick="window.location.href='index.php'">
  </form>
  </body>
</html>