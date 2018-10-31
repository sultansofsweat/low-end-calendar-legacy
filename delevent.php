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
    <title>Low End Calendar-Delete Event</title>
    
  </head>
  <body>
  <p>
  <?php
	
	if(isset($_POST['s']) && $_POST['s'] == "y" && isset($_POST['select']))
	{
		$id=preg_replace("/[^0-9]/","",$_POST['select']);
		if($id == "" || $id < 1)
		{
			trigger_error("You must select an event first, you goat!",E_USER_WARNING);
			$events=event_display_prepare($user[0],$user[2],get_all_events($db));
			if(isset($_GET['id']))
			{
				$id=preg_replace("/[^0-9]/","",$_POST['select']);
			}
		}
		//Make sure user is an administrator
		elseif(isset($_SESSION['username']) && $_SESSION['username'] != "")
		{
			$db=open_db("db/calendar.sqlite",SQLITE3_OPEN_READWRITE);
			$event=get_event($db,$id);
			$user=get_user($db,$_SESSION['username']);
			if(isset($user[2]) && $user[2] >= 2 && $event !== false)
			{
				$partlist=explode(",",$event[7]);
				if($user[2] >= 3 || $user[0] == $event[1] || in_array($user[0],$partlist))
				{
					//die("Can delete event now.");
					//Trash event
					$debug=delete_event($db,$id);
					if($debug === true)
					{
						echo ("<script type=\"text/javascript\">window.location = \"index.php?dle=yes\"</script>");
					}
					else
					{
						echo ("<script type=\"text/javascript\">window.location = \"index.php?dle=no\"</script>");
					}
				}
				else
				{
					die ("<script type=\"text/javascript\">window.location = \"index.php?bad=yes\"</script>");
				}
				/*else
				{
					if($user[2] < 3)
					{
						echo("User is not administrator.<br>");
					}
					if($user[0] != $event[1])
					{
						echo("User is not event owner.<br>");
					}
					if(!in_array($user[0],$partlist))
					{
						echo("User is not in participants list.<br>");
					}
					die("And the system is a piece of shit");
				}*/
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
			if(isset($user[2]) && $user[2] < 2)
			{
				die("<script type=\"text/javascript\">window.location = \"index.php?bad=yes\"</script>");
			}
			$events=event_display_prepare_removal($user[0],$user[2],get_all_events($db));
			if(isset($_GET['id']))
			{
				$id=preg_replace("/[^0-9]/","",$_GET['id']);
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
  <h1>Low End Calendar-Delete Event</h1>
  <form method="post" action="delevent.php">
  <input type="hidden" name="s" value="y">
  Select an event: <select name="select">
  <?php
	if(count($events) > 0)
	{
		foreach($events as $event)
		{
			echo ("<option value=" . $event[0] . "\" ");
			if(isset($id) && $id == $event[0])
			{
				echo ("selected=\"selected\"");
			}
			echo (">" . date("l F j, o, g:i A",$event[3]));
			if($event[4] > 0)
			{
				echo (" (ends " . date("l F j, o, g:i A",$event[4]) . ")");
			}
			echo (": " . $event[1] . "</option>\r\n");
		}
	}
  ?>
  </select><br>
  Are you sure you want to delete this event?<br>
  <input type="submit" value="Yes, dump it"> or <input type="button" value="Cancel" onclick="window.location.href='index.php'">
  </form>
  </body>
</html>