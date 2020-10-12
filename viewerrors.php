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
    <title>Low End Calendar-View Error Log</title>
    
  </head>
  <body>
  <?php
	//Make sure user is signed in
	if(isset($_SESSION['username']) && $_SESSION['username'] != "")
	{
		$db=open_db("db/calendar.sqlite",SQLITE3_OPEN_READONLY);
		$user=get_user($db,$_SESSION['username']);
		if(!isset($user[2]) || $user[2] < 4)
		{
			die("<script type=\"text/javascript\">window.location = \"index.php?bad=yes\"</script>");
		}
		$debug=close_db($db);
		if($debug === false)
		{
			trigger_error("The server has caused a criticality accident and the database became irradiated.",E_USER_WARNING);
		}
		if(!empty($_POST['mark']))
		{
			$db=open_db("db/calendar.sqlite",SQLITE3_OPEN_READWRITE);
			switch($_POST['mark'])
			{
				case "r":
				$debug=mark_error_log_as_read($db);
				if($debug === true)
				{
					echo ("<p style=\"color:#00FF00\"><b>Successfully marked error log as read!</b></p>\r\n");
				}
				else
				{
					echo ("<p style=\"color:#FF0000\"><b>Failed to mark error log as read! Must have said something about a GPX.</b></p>\r\n");
				}
				break;
				
				case "u":
				$debug=mark_error_log_as_unread($db);
				if($debug === true)
				{
					echo ("<p style=\"color:#00FF00\"><b>Successfully marked error log as unread!</b></p>\r\n");
				}
				else
				{
					echo ("<p style=\"color:#FF0000\"><b>Failed to mark error log as unread! Must have said something about a GPX.</b></p>\r\n");
				}
				break;
				
				case "d":
				$debug=clear_error_log($db);
				if($debug === true)
				{
					echo ("<p style=\"color:#00FF00\"><b>Successfully cleared error log!</b></p>\r\n");
				}
				else
				{
					echo ("<p style=\"color:#FF0000\"><b>Failed to clear error log! Must have said something about a GPX.</b></p>\r\n");
				}
				break;
				
				default:
				echo ("<p style=\"color:#FF0000\"><b>Invalid command! Is this a GPX?</b></p>\r\n");
				break;
			}
			if(!empty($_POST['all']))
			{
				$errors=get_all_errors($db);
			}
			else
			{
				$errors=get_unread_errors($db);
			}
			$debug=close_db($db);
			if($debug === false)
			{
				trigger_error("The server has caused a criticality accident and the database became irradiated.",E_USER_WARNING);
			}
		}
		else
		{
			$db=open_db("db/calendar.sqlite",SQLITE3_OPEN_READONLY);
			if(!empty($_POST['all']))
			{
				$errors=get_all_errors($db);
			}
			else
			{
				$errors=get_unread_errors($db);
			}
			$debug=close_db($db);
			if($debug === false)
			{
				trigger_error("The server has caused a criticality accident and the database became irradiated.",E_USER_WARNING);
			}
		}
	}
	else
	{
		//Automatically deny access
		die ("<script type=\"text/javascript\">window.location = \"index.php?bad=yes\"</script>");
	}
  ?>
  <h1>Low End Calendar-View Error Logs</h1>
  <form method="post" action="viewerrors.php">
  <input type="checkbox" name="all" value="y"<?php if(!empty($_POST['all'])) { echo " checked=\"checked\""; } ?>>View all messages<br>
  <input type="radio" name="mark" value="r">Mark log as read | <input type="radio" name="mark" value="u">Mark log as unread | <input type="radio" name="mark" value="d">Clear log<br>
  <input type="submit" value="Update">
  </form>
  <table width="100%">
  <tr>
  <th>Page:line</th>
  <th>Type</th>
  <th>Details</th>
  <th>Time</th>
  <th></th>
  </tr>
  <?php
	if(count($errors) > 0)
	{
		foreach($errors as $error)
		{
			//FORMAT: page:line,type,details,time,status
			echo("<tr>\r\n");
			echo("<td>" . stripcslashes($error[0]) . "</td>\r\n
			<td>");
			switch($error[1])
			{
				case E_USER_ERROR:
				case E_ERROR:
				echo "Critical";
				break;
				
				case E_USER_WARNING:
				case E_WARNING:
				echo "Error";
				break;
				
				case E_USER_NOTICE:
				case E_NOTICE:
				echo "Information";
				break;
				
				case E_USER_DEPRECATED:
				case E_DEPRECATED:
				echo "Deprecation";
				break;
				
				default:
				echo "Unknown";
				break;
			}
			echo(" [" . $error[1] . "]</td>\r\n
			<td>" . stripcslashes($error[2]) . "</td>\r\n
			<td>" . date("l F j, o, g:i A",$error[3]) . "</td>\r\n
			<td>");
			if($error[4] === true)
			{
				echo("*****");
			}
			echo("</td>\r\n</tr>\r\n");
		}
	}
	else
	{
		echo("<tr>\r\n<td colspan=\"5\">There are no error messages currently logged.</td>\r\n</tr>\r\n");
	}
  ?>
  </table>
  <p><a href="index.php">Go back</a></p>
  </body>
</html>