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
    <title>Low End Calendar-Change User Settings</title>
    
  </head>
  <body>
  <p>
  <?php
	include("functions.php");
	if(isset($_POST['s2']) && $_POST['s2'] != "")
	{
		//Make sure user is an administrator
		if(isset($_SESSION['username']) && $_SESSION['username'] != "")
		{
			$db=open_db("db/calendar.sqlite",SQLITE3_OPEN_READWRITE);
			$user=get_user($db,$_SESSION['username']);
			if(isset($user[2]) && $user[2] >= 3)
			{
				//Begin submission
				if(isset($_POST['login']) && $_POST['login'] != "" && isset($_POST['name']) && $_POST['name'] != "" && isset($_POST['timezone']) && $_POST['timezone'] != "" && isset($_POST['style']) && $_POST['style'] != "" && isset($_POST['privileges']) && $_POST['privileges'] != "")
				{
					$origlogin=preg_replace("/[^A-Za-z0-9]/","",$_POST['s2']);
					$login=preg_replace("/[^A-Za-z0-9]/","",$_POST['login']);
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
						$style="default";
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
					if($origlogin == $login)
					{
						if(isset($_POST['password']) && isset($_POST['cpassword']) && $_POST['password'] != "" && $_POST['cpassword'] != "" && $_POST['password'] == $_POST['cpassword'])
						{
							$debug=update_user($db,$name,$login,password_hash($_POST['password'],PASSWORD_DEFAULT),$level,$timezone,$style);
							if($debug === true)
							{
								echo ("<script type=\"text/javascript\">window.location = \"index.php?edt=yes\"</script>");
							}
							else
							{
								echo ("<script type=\"text/javascript\">window.location = \"index.php?edt=no\"</script>");
							}
						}
						elseif((!isset($_POST['password']) || $_POST['password'] == "") && (!isset($_POST['cpassword']) || $_POST['cpassword'] == ""))
						{
							$password=get_password($db,$login);
							if(isset($password) && $password != "")
							{
								$debug=update_user($db,$name,$login,$password,$level,$timezone,$style);
								if($debug === true)
								{
									echo ("<script type=\"text/javascript\">window.location = \"index.php?edt=yes\"</script>");
								}
								else
								{
									echo ("<script type=\"text/javascript\">window.location = \"index.php?edt=no\"</script>");
								}
							}
							else
							{
								echo ("<script type=\"text/javascript\">window.location = \"index.php?edt=no\"</script>");
							}
						}
						else
						{
							trigger_error("You only had ONE JOB! Passwords did not match or were blank!",E_USER_WARNING);
						}
					}
					else
					{
						if(isset($_POST['password']) && isset($_POST['cpassword']) && $_POST['password'] != "" && $_POST['cpassword'] != "" && $_POST['password'] == $_POST['cpassword'])
						{
							$debug=delete_user($db,$origlogin);
							if($debug === true)
							{
								$debug=insert_user($db,$name,$login,password_hash($_POST['password']),$level,$timezone,$style);
								if($debug === true)
								{
									echo ("<script type=\"text/javascript\">window.location = \"index.php?edt=yes\"</script>");
								}
								else
								{
									echo ("<script type=\"text/javascript\">window.location = \"index.php?edt=no\"</script>");
								}
							}
							else
							{
								echo ("<script type=\"text/javascript\">window.location = \"index.php?edt=no\"</script>");
							}
						}
						elseif((!isset($_POST['password']) || $_POST['password'] == "") && (!isset($_POST['cpassword']) || $_POST['cpassword'] == ""))
						{
							$password=get_password($db,$_SESSION['username']);
							if(isset($password) && $password != "")
							{
								$debug=delete_user($db,$origlogin);
								if($debug === true)
								{
									$debug=insert_user($db,$name,$login,$password,$level,$timezone,$style);
									if($debug === true)
									{
										echo ("<script type=\"text/javascript\">window.location = \"index.php?edt=yes\"</script>");
									}
									else
									{
										echo ("<script type=\"text/javascript\">window.location = \"index.php?edt=no\"</script>");
									}
								}
								else
								{
									echo ("<script type=\"text/javascript\">window.location = \"index.php?edt=no\"</script>");
								}
							}
							else
							{
								echo ("<script type=\"text/javascript\">window.location = \"index.php?set=no\"</script>");
							}
						}
						else
						{
							trigger_error("You only had ONE JOB! Passwords did not match or were blank!",E_USER_WARNING);
						}
					}
				}
				else
				{
					trigger_error("No details can be blank, you goat!",E_USER_WARNING);
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
	elseif(isset($_POST['s1']) && $_POST['s1'] == "y")
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
			if(isset($_POST['select']) && $_POST['select'] != "")
			{
				$select=preg_replace("/[^A-Za-z0-9]/","",$_POST['select']);
				$userinfo=get_user($db,$select);
				$login=$userinfo[0];
				$name=$userinfo[1];
				$level=$userinfo[2];
				$timezone=$userinfo[3];
				$style=$userinfo[4];
			}
			else
			{
				trigger_error("You must choose a user to edit, you goat!",E_USER_WARNING);
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
  <h1>Low End Calendar-Change User Settings</h1>
  <form method="post" action="edituser.php">
  <input type="hidden" name="s1" value="y">
  <input type="hidden" name="s2" <?php if(isset($login) && $login != "") { echo "value=\"$login\""; } else { echo "value=\"\" disabled=\"disabled\""; } ?>>
  Select a user: <select name="select" <?php if(isset($_POST['s1']) && $_POST['s1'] == "y") { echo "disabled=\"disabled\""; } ?>>
  <?php
	if(count($users) > 0)
	{
		foreach($users as $user)
		{
			echo("<option value=\"" . $user[0] . "\" ");
			if(isset($login) && $login == $user[0])
			{
				echo ("selected=\"selected\"");
			}
			echo (">" . $user[0] . " (" . $user[1] . ")</option>\r\n");
		}
	}
  ?>
  </select><br>
  Name: <input type="text" name="name" value="<?php if(isset($_POST['name'])) { echo $_POST['name']; } elseif(isset($name)) { echo $name; } ?>" <?php if(!isset($login) || $login == "") { echo "disabled=\"disabled\""; } ?>><br>
  Login name: <input type="text" name="login" value="<?php if(isset($_POST['login'])) { echo $_POST['login']; } elseif(isset($login)) { echo $login; } ?>" <?php if(!isset($login) || $login == "") { echo "disabled=\"disabled\""; } ?>><br>
  User level: <select name="privileges" <?php if(!isset($login) || $login == "") { echo "disabled=\"disabled\""; } ?>>
  <option value="0" <?php if(isset($_POST['privileges']) && $_POST['privileges'] == "0") { echo "selected=\"selected\""; } elseif(isset($level) && $level == "0") { echo "selected=\"selected\""; }?>>Banned</option>
  <option value="1" <?php if(isset($_POST['privileges']) && $_POST['privileges'] == "1") { echo "selected=\"selected\""; } elseif(isset($level) && $level == "1") { echo "selected=\"selected\""; }?>>Viewer only</option>
  <option value="2" <?php if(isset($_POST['privileges']) && $_POST['privileges'] == "2") { echo "selected=\"selected\""; } elseif(isset($level) && $level == "2") { echo "selected=\"selected\""; }?>>Regular user</option>
  <option value="3" <?php if(isset($_POST['privileges']) && $_POST['privileges'] == "3") { echo "selected=\"selected\""; } elseif(isset($level) && $level == "3") { echo "selected=\"selected\""; }?>>Administrator</option>
  <option value="4" <?php if(isset($_POST['privileges']) && $_POST['privileges'] == "4") { echo "selected=\"selected\""; } elseif(isset($level) && $level == "4") { echo "selected=\"selected\""; }?>>Microwave Master</option>
  </select><br>
  New password: <input type="password" name="password" <?php if(!isset($login) || $login == "") { echo "disabled=\"disabled\""; } ?>><br>
  Confirm new password: <input type="password" name="cpassword" <?php if(!isset($login) || $login == "") { echo "disabled=\"disabled\""; } ?>><br>
  <a href="http://php.net/manual/en/timezones.php">Timezone</a>: <input type="text" name="timezone" value="<?php if(isset($_POST['timezone'])) { echo $_POST['timezone']; } elseif(isset($timezone)) { echo $timezone; }?>" <?php if(!isset($login) || $login == "") { echo "disabled=\"disabled\""; } ?>><br>
  Display style: <select name="style" <?php if(!isset($login) || $login == "") { echo "disabled=\"disabled\""; } ?>>
  <option value="default" <?php if(isset($_POST['style']) && $_POST['style'] == "default") { echo "selected=\"selected\""; } elseif(isset($style) && $style == "default") { echo "selected=\"selected\""; }?>>Default</option>
  <option value="black" <?php if(isset($_POST['style']) && $_POST['style'] == "black") { echo "selected=\"selected\""; } elseif(isset($style) && $style == "black") { echo "selected=\"selected\""; }?>>Black</option>
  <option value="blue" <?php if(isset($_POST['style']) && $_POST['style'] == "blue") { echo "selected=\"selected\""; } elseif(isset($style) && $style == "blue") { echo "selected=\"selected\""; }?>>Blue</option>
  <option value="green" <?php if(isset($_POST['style']) && $_POST['style'] == "green") { echo "selected=\"selected\""; } elseif(isset($style) && $style == "green") { echo "selected=\"selected\""; }?>>Green</option>
  <option value="yellow" <?php if(isset($_POST['style']) && $_POST['style'] == "yellow") { echo "selected=\"selected\""; } elseif(isset($style) && $style == "yellow") { echo "selected=\"selected\""; }?>>Yellow</option>
  <option value="rusty" <?php if(isset($_POST['style']) && $_POST['style'] == "rusty") { echo "selected=\"selected\""; } elseif(isset($style) && $style == "rusty") { echo "selected=\"selected\""; }?>>Rusty</option>
  <option value="purple" <?php if(isset($_POST['style']) && $_POST['style'] == "purple") { echo "selected=\"selected\""; } elseif(isset($style) && $style == "purple") { echo "selected=\"selected\""; }?>>Purple</option>
  <option value="pink" <?php if(isset($_POST['style']) && $_POST['style'] == "pink") { echo "selected=\"selected\""; } elseif(isset($style) && $style == "pink") { echo "selected=\"selected\""; }?>>Pink</option>
  <option value="appliance" <?php if(isset($_POST['style']) && $_POST['style'] == "appliance") { echo "selected=\"selected\""; } elseif(isset($style) && $style == "appliance") { echo "selected=\"selected\""; }?>>Appliance</option>
  <option value="white" <?php if(isset($_POST['style']) && $_POST['style'] == "white") { echo "selected=\"selected\""; } elseif(isset($style) && $style == "white") { echo "selected=\"selected\""; }?>>White</option>
  <option value="default-large" <?php if(isset($_POST['style']) && $_POST['style'] == "default-large") { echo "selected=\"selected\""; } elseif(isset($style) && $style == "default-large") { echo "selected=\"selected\""; }?>>Default (Large Text)</option>
  <option value="black-large" <?php if(isset($_POST['style']) && $_POST['style'] == "black-large") { echo "selected=\"selected\""; } elseif(isset($style) && $style == "black-large") { echo "selected=\"selected\""; }?>>Black (Large Text)</option>
  <option value="blue-large" <?php if(isset($_POST['style']) && $_POST['style'] == "blue-large") { echo "selected=\"selected\""; } elseif(isset($style) && $style == "blue-large") { echo "selected=\"selected\""; }?>>Blue (Large Text)</option>
  <option value="green-large" <?php if(isset($_POST['style']) && $_POST['style'] == "green-large") { echo "selected=\"selected\""; } elseif(isset($style) && $style == "green-large") { echo "selected=\"selected\""; }?>>Green (Large Text)</option>
  <option value="yellow-large" <?php if(isset($_POST['style']) && $_POST['style'] == "yellow-large") { echo "selected=\"selected\""; } elseif(isset($style) && $style == "yellow-large") { echo "selected=\"selected\""; }?>>Yellow (Large Text)</option>
  <option value="rusty-large" <?php if(isset($_POST['style']) && $_POST['style'] == "rusty-large") { echo "selected=\"selected\""; } elseif(isset($style) && $style == "rusty-large") { echo "selected=\"selected\""; }?>>Rusty (Large Text)</option>
  <option value="purple-large" <?php if(isset($_POST['style']) && $_POST['style'] == "purple-large") { echo "selected=\"selected\""; } elseif(isset($style) && $style == "purple-large") { echo "selected=\"selected\""; }?>>Purple (Large Text)</option>
  <option value="pink-large" <?php if(isset($_POST['style']) && $_POST['style'] == "pink-large") { echo "selected=\"selected\""; } elseif(isset($style) && $style == "pink-large") { echo "selected=\"selected\""; }?>>Pink (Large Text)</option>
  <option value="appliance-large" <?php if(isset($_POST['style']) && $_POST['style'] == "appliance-large") { echo "selected=\"selected\""; } elseif(isset($style) && $style == "appliance-large") { echo "selected=\"selected\""; }?>>Appliance (Large Text)</option>
  <option value="white-large" <?php if(isset($_POST['style']) && $_POST['style'] == "white-large") { echo "selected=\"selected\""; } elseif(isset($style) && $style == "white-large") { echo "selected=\"selected\""; }?>>White (Large Text)</option>
  </select><br>
  <input type="submit" value="Change settings"> or <input type="button" value="Cancel" onclick="window.location.href='index.php'">
  </form>
  </body>
</html>