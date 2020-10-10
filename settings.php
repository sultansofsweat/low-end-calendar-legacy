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
    <title>Low End Calendar-Preferences</title>
    
  </head>
  <body>
  <p>
  <?php
	
	if(isset($_POST['s']) && $_POST['s'] == "y")
	{
		//Make sure user is logged in
		if(isset($_SESSION['username']) && $_SESSION['username'] != "")
		{
			//Begin submission
			if(isset($_POST['name']) && $_POST['name'] != "" && isset($_POST['timezone']) && $_POST['timezone'] != "" && isset($_POST['style']) && $_POST['style'] != "" && isset($_POST['privileges']) && $_POST['privileges'] != "")
			{
				$db=open_db("db/calendar.sqlite",SQLITE3_OPEN_READWRITE);
				$name=preg_replace("/[^A-Za-z0-9 ]/","",$_POST['name']);
				$timezone=filter_var($_POST['timezone'],FILTER_SANITIZE_STRING);
				$level=preg_replace("/[^0-9]/","",$_POST['privileges']);
				if($level < 0 || $level > 4)
				{
					$level=0;
				}
				$style=preg_replace("/[^A-Za-z0-9-]/","",$_POST['style']);
				if(!file_exists("styles/$style.css"))
				{
					$style="white";
				}
				/*switch($_POST['style'])
				{
					case "black":
					case "blue":
					case "green":
					case "yellow":
					case "rusty":
					case "purple":
					case "pink":
					case "appliance":
					case "sdp":
					$style=$_POST['style'];
					break;
					case "default":
					default:
					$style="default";
					break;
				}*/
				if(isset($_POST['password']) && isset($_POST['cpassword']) && $_POST['password'] != "" && $_POST['cpassword'] != "" && $_POST['password'] == $_POST['cpassword'])
				{
					$debug=update_user($db,$name,$_SESSION['username'],password_hash($_POST['password'],PASSWORD_DEFAULT),$level,$timezone,$style);
					if($debug === true)
					{
						$_SESSION['timezone']=$timezone;
						$_SESSION['style']=$style;
						echo ("<script type=\"text/javascript\">window.location = \"index.php?set=yes\"</script>");
					}
					else
					{
						$_SESSION['timezone']=$timezone;
						$_SESSION['style']=$style;
						echo ("<script type=\"text/javascript\">window.location = \"index.php?set=no\"</script>");
					}
				}
				elseif((!isset($_POST['password']) || $_POST['password'] == "") && (!isset($_POST['cpassword']) || $_POST['cpassword'] == ""))
				{
					$password=get_password($db,$_SESSION['username']);
					if(isset($password) && $password != "")
					{
						$debug=update_user($db,$name,$_SESSION['username'],$password,$level,$timezone,$style);
						if($debug === true)
						{
							$_SESSION['timezone']=$timezone;
							$_SESSION['style']=$style;
							echo ("<script type=\"text/javascript\">window.location = \"index.php?set=yes\"</script>");
						}
						else
						{
							$_SESSION['timezone']=$timezone;
							$_SESSION['style']=$style;
							echo ("<script type=\"text/javascript\">window.location = \"index.php?set=no\"</script>");
						}
					}
					else
					{
						$_SESSION['timezone']=$timezone;
						$_SESSION['style']=$style;
						echo ("<script type=\"text/javascript\">window.location = \"index.php?set=no\"</script>");
					}
				}
				else
				{
					trigger_error("You only had ONE JOB! Passwords did not match or were blank!",E_USER_WARNING);
				}
				$debug=close_db($db);
				if($debug === false)
				{
					trigger_error("The server has caused a criticality accident and the database became irradiated.",E_USER_WARNING);
				}
			}
			else
			{
				trigger_error("Privileges, name, timezone and style cannot be blank, you goat!",E_USER_WARNING);
			}
		}
		else
		{
			die("<script type=\"text/javascript\">window.location = \"index.php?bad=yes\"</script>");
		}
	}
	else
	{
		//Make sure user is logged in
		if(!isset($_SESSION['username']) || $_SESSION['username'] == "")
		{
			die ("<script type=\"text/javascript\">window.location = \"index.php?bad=yes\"</script>");
		}
		$db=open_db("db/calendar.sqlite",SQLITE3_OPEN_READONLY);
		$userinfo=get_user($db,$_SESSION['username']);
		$debug=close_db($db);
		$styles=get_all_styles();
		if($debug === false)
		{
			trigger_error("The server has caused a criticality accident and the database became irradiated.",E_USER_WARNING);
		}
	}
  ?>
  </p>
  <h1>Low End Calendar-Preferences</h1>
  <form method="post" action="settings.php">
  <input type="hidden" name="s" value="y">
  <input type="hidden" name="privileges" value="<?php if(isset($_POST['privileges'])) { echo $_POST['privileges']; } elseif(isset($userinfo[2])) { echo $userinfo[2]; } else { echo 0; } ?>">
  Name: <input type="text" name="name" value="<?php if(isset($_POST['name'])) { echo $_POST['name']; } elseif(isset($_SESSION['name'])) { echo $_SESSION['name']; } ?>"><br>
  New password: <input type="password" name="password"><br>
  Confirm new password: <input type="password" name="cpassword"><br>
  <a href="http://firealarms.redbat.ca/timezone/index.php">Timezone</a>: <input type="text" name="timezone" value="<?php if(isset($_POST['timezone'])) { echo $_POST['timezone']; } elseif(isset($_SESSION['timezone'])) { echo $_SESSION['timezone']; }?>"><br>
  Display style: <select name="style">
  <!--<option value="white" <?php if(isset($_POST['style']) && $_POST['style'] == "white") { echo "selected=\"selected\""; } elseif(isset($style) && $style == "white") { echo "selected=\"selected\""; }?>>White</option>
  <option value="black" <?php if(isset($_POST['style']) && $_POST['style'] == "black") { echo "selected=\"selected\""; } elseif(isset($style) && $style == "black") { echo "selected=\"selected\""; }?>>Black</option>
  <option value="blue" <?php if(isset($_POST['style']) && $_POST['style'] == "blue") { echo "selected=\"selected\""; } elseif(isset($style) && $style == "blue") { echo "selected=\"selected\""; }?>>Blue</option>
  <option value="green" <?php if(isset($_POST['style']) && $_POST['style'] == "green") { echo "selected=\"selected\""; } elseif(isset($style) && $style == "green") { echo "selected=\"selected\""; }?>>Green</option>
  <option value="yellow" <?php if(isset($_POST['style']) && $_POST['style'] == "yellow") { echo "selected=\"selected\""; } elseif(isset($style) && $style == "yellow") { echo "selected=\"selected\""; }?>>Yellow</option>
  <option value="rusty" <?php if(isset($_POST['style']) && $_POST['style'] == "rusty") { echo "selected=\"selected\""; } elseif(isset($style) && $style == "rusty") { echo "selected=\"selected\""; }?>>Rusty</option>
  <option value="purple" <?php if(isset($_POST['style']) && $_POST['style'] == "purple") { echo "selected=\"selected\""; } elseif(isset($style) && $style == "purple") { echo "selected=\"selected\""; }?>>Purple</option>
  <option value="pink" <?php if(isset($_POST['style']) && $_POST['style'] == "pink") { echo "selected=\"selected\""; } elseif(isset($style) && $style == "pink") { echo "selected=\"selected\""; }?>>Pink</option>
  <option value="appliance" <?php if(isset($_POST['style']) && $_POST['style'] == "appliance") { echo "selected=\"selected\""; } elseif(isset($style) && $style == "appliance") { echo "selected=\"selected\""; }?>>Appliance</option>
  <option value="sdp" <?php if(isset($_POST['style']) && $_POST['style'] == "sdp") { echo "selected=\"selected\""; } elseif(isset($style) && $style == "sdp") { echo "selected=\"selected\""; }?>>Stereo Dust Particles</option>
  <option value="white-large" <?php if(isset($_POST['style']) && $_POST['style'] == "white-large") { echo "selected=\"selected\""; } elseif(isset($style) && $style == "white-large") { echo "selected=\"selected\""; }?>>White (Large Text)</option>
  <option value="black-large" <?php if(isset($_POST['style']) && $_POST['style'] == "black-large") { echo "selected=\"selected\""; } elseif(isset($style) && $style == "black-large") { echo "selected=\"selected\""; }?>>Black (Large Text)</option>
  <option value="blue-large" <?php if(isset($_POST['style']) && $_POST['style'] == "blue-large") { echo "selected=\"selected\""; } elseif(isset($style) && $style == "blue-large") { echo "selected=\"selected\""; }?>>Blue (Large Text)</option>
  <option value="green-large" <?php if(isset($_POST['style']) && $_POST['style'] == "green-large") { echo "selected=\"selected\""; } elseif(isset($style) && $style == "green-large") { echo "selected=\"selected\""; }?>>Green (Large Text)</option>
  <option value="yellow-large" <?php if(isset($_POST['style']) && $_POST['style'] == "yellow-large") { echo "selected=\"selected\""; } elseif(isset($style) && $style == "yellow-large") { echo "selected=\"selected\""; }?>>Yellow (Large Text)</option>
  <option value="rusty-large" <?php if(isset($_POST['style']) && $_POST['style'] == "rusty-large") { echo "selected=\"selected\""; } elseif(isset($style) && $style == "rusty-large") { echo "selected=\"selected\""; }?>>Rusty (Large Text)</option>
  <option value="purple-large" <?php if(isset($_POST['style']) && $_POST['style'] == "purple-large") { echo "selected=\"selected\""; } elseif(isset($style) && $style == "purple-large") { echo "selected=\"selected\""; }?>>Purple (Large Text)</option>
  <option value="pink-large" <?php if(isset($_POST['style']) && $_POST['style'] == "pink-large") { echo "selected=\"selected\""; } elseif(isset($style) && $style == "pink-large") { echo "selected=\"selected\""; }?>>Pink (Large Text)</option>
  <option value="appliance-large" <?php if(isset($_POST['style']) && $_POST['style'] == "appliance-large") { echo "selected=\"selected\""; } elseif(isset($style) && $style == "appliance-large") { echo "selected=\"selected\""; }?>>Appliance (Large Text)</option>
  <option value="sdp-large" <?php if(isset($_POST['style']) && $_POST['style'] == "sdp-large") { echo "selected=\"selected\""; } elseif(isset($style) && $style == "sdp-large") { echo "selected=\"selected\""; }?>>Stereo Dust Particles (Large Text)</option>-->
  <?php
	foreach($styles as $file=>$display)
	{
		echo("<option value=\"$file\"");
		if(isset($_POST['style']) && $_POST['style'] == $file)
		{
			echo " selected=\"selected\"";
		}
		elseif(isset($userinfo[4]) && $userinfo[4] == $file)
		{
			echo " selected=\"selected\"";
		}
		echo(">$display</option>\r\n");
	}
  ?>
  </select><br>
  <input type="submit" value="Change settings"> or <input type="button" value="Cancel" onclick="window.location.href='index.php'">
  </form>
  </body>
</html>