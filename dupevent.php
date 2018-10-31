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
    <title>Low End Calendar-Duplicate Event</title>
    
  </head>
  <body>
  <p>
  <?php
	include("functions.php");
	function check_if_valid($id,$event,$month,$day,$year)
	{
		if($id === false || $event === false || $month === false || $day === false || $year === false)
		{
			var_dump($event);
			return false;
		}
		$time=strtotime($month . " " . $day . " " . $year);
		if($time === false || $time < 0)
		{
			echo("Invalid date.");
			return false;
		}
		return true;
	}
	if(isset($_POST['s']) && $_POST['s'] == "y")
	{
		//Make sure user is logged in
		if(isset($_SESSION['username']) && $_SESSION['username'] != "")
		{
			$db=open_db("db/calendar.sqlite",SQLITE3_OPEN_READWRITE);
			$user=get_user($db,$_SESSION['username']);
			if(isset($user[2]) && $user[2] >= 2)
			{
				//FORMAT: id,details,month,day,year
				$infos=array(false,false,false,false,false);
				//Begin submission
				if(isset($_POST['event']))
				{
					$infos[0]=preg_replace("/[^0-9]/","",$_POST['event']);
					$infos[1]=false;
					if($infos[0] != "")
					{
						/*$infos[1]=get_event($db,$infos[0]);
						array_unshift($infos[1],intval($infos[0]));*/
						$valid=event_display_prepare($user[0],$user[2],get_all_events($db));
						foreach($valid as $checkevent)
						{
							if($checkevent[0] == $infos[0])
							{
								$infos[1]=get_event($db,$infos[0]);
								array_unshift($infos[1],intval($infos[0]));
							}
						}
						/*echo("<pre>");
						var_dump($valid);
						echo("</pre>\r\n");
						if(!in_array($infos[1],$valid))
						{
							$infos[1]=false;
						}*/
						unset($valid,$checkevent);
					}
					else
					{
						$infos[0]=false;
					}
				}
				if(isset($_POST['month']))
				{
					$infos[2]=preg_replace("/[^A-Za-z0-9]/","",$_POST['month']);
				}
				if(isset($_POST['day']))
				{
					$infos[3]=preg_replace("/[^0-9]/","",$_POST['day']);
				}
				if(isset($_POST['year']))
				{
					$infos[4]=preg_replace("/[^0-9]/","",$_POST['year']);
				}
				
				for($i=0;$i<count($infos);$i++)
				{
					if($infos[$i] == "")
					{
						$infos[$i]=false;
					}
				}
				
				if(check_if_valid($infos[0],$infos[1],$infos[2],$infos[3],$infos[4]) === true && $infos[1] !== false)
				{
					//FORMAT: ID,Name,Owner,Start,End,Allday,Description,Invitees,Private,Location,Created,Repeat
					$edetails=$infos[1];
					$month=$infos[2];
					$day=$infos[3];
					$year=$infos[4];
					unset($infos);
					$ostime=date("g:i A",$edetails[3]);
					$stime=strtotime($month . " " . $day . ", " . $year . " " . $ostime);
					if($edetails[5] == 0)
					{
						$oduration=$edetails[4]-$edetails[3];
						$etime=$stime+$oduration;
					}
					else
					{
						$etime=0;
					}
					if($edetails[8] == 1)
					{
						$invitees=explode(",",$edetails[7]);
						$invitees[]=$edetails[2];
						$invitees=array_diff(array_unique($invitees),array($_SESSION['username']));
						$invitees=implode(",",$invitees);
					}
					else
					{
						$invitees=$edetails[7];
					}
					$odetails=get_user($db,$edetails[2]);
					$description="[This event was duplicated from an event created by " . $odetails[1] . " on " . date("l F j, o, g:i A",$edetails[10]) . "] " . $edetails[6];
					echo("<pre>");
					var_dump(array($edetails[1],$_SESSION['username'],$edetails[9],$stime,$etime,$edetails[5],$description,$invitees,$edetails[8],time(),$edetails[11]));
					echo("</pre>\r\n");
					$debug=insert_event($db,$edetails[1],$_SESSION['username'],$edetails[9],$stime,$etime,$edetails[5],$description,$invitees,$edetails[8],time(),$edetails[11]);
					/*if($debug === true)
					{
						echo ("<script type=\"text/javascript\">window.location = \"index.php?ade=yes\"</script>");
					}
					else
					{
						echo ("<script type=\"text/javascript\">window.location = \"index.php?ade=no\"</script>");
					}*/
				}
				else
				{
					trigger_error("Check your details. You probably missed something very important.",E_USER_WARNING);
					$events=event_display_prepare($user[0],$user[2],get_all_events($db));
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
			if(isset($user[2]) && $user[2] < 2)
			{
				die("<script type=\"text/javascript\">window.location = \"index.php?bad=yes\"</script>");
			}
			$events=event_display_prepare($user[0],$user[2],get_all_events($db));
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
  <h1>Low End Calendar-Duplicate Event</h1>
  <p>Select an event below. Note that you can only select events that you can see. Events will be duplicated as-is with the month changed, with a few notable exceptions:</p>
  <ul>
  <li>The event created time will be set to the current time.</li>
  <li>You will become the event owner.</li>
  <li>If the event is private, the owner of the event you are duplicating will be added to the invitees list (if needed), and you will be removed from it (if needed).</li>
  <li>The event description will contain an addendum that this event was duplicated.</li>
  </ul>
  <p>If you want to change any details, you will have to edit the new event after duplicating it.</p>
  <form method="post" action="dupevent.php">
  <input type="hidden" name="s" value="y">
  Event: <select name="event">
  <?php
	if(count($events) > 0)
	{
		foreach($events as $event)
		{
			echo ("<option value=\"" . $event[0] . "\" ");
			$sel=false;
			if(isset($_POST['event']) && $_POST['event'] == $event[0])
			{
				echo "selected=\"selected\"";
			}
			echo ">" . date("l F j, o",$event[3]) . ": " . $event[1] . "</option>\r\n";
		}
	}
  ?>
  </select><br>
  Date: <input maxlength="9" size="9" type="text" name="month" value="<?php if(isset($_POST['month'])) { echo $_POST['month']; } else { echo date("F"); } ?>"><input maxlength="2" size="2" type="text" name="day" value="<?php if(isset($_POST['day'])) { echo $_POST['day']; } else { echo date("j"); } ?>"><input maxlength="4" size="4" type="text" name="year" value="<?php if(isset($_POST['year'])) { echo $_POST['year']; } else { echo date("Y"); } ?>"> (month MUST be in text format!)<br>
  <input type="submit" value="Add event"> or <input type="button" value="Cancel" onclick="window.location.href='index.php'">
  </form>
  </body>
</html>