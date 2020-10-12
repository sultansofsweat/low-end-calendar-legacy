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
    $version=get_version();
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
    <title>Low End Calendar</title>
    
  </head>
  <body>
  <?php
	
	function sort_events($a,$b)
	{
		if(!isset($a[3]) || !isset($b[3]) || $a[3] == $b[3])
		{
			return 0;
		}
		elseif($a[3] < $b[3])
		{
			return -1;
		}
		return 1;
	}
	function get_active_events($eventlist)
	{
		$count=0;
		if(count($eventlist) > 0)
		{
			foreach($eventlist as $event)
			{
				//FORMAT: ID,Name,Owner,Start,End,Allday,Description,Invitees,Private,Location,Created,Repeat
				$time=time();
				if(($event[5] == 1 && $time < ($event[3]+(24*60*60))) || $time < $event[3] || ($time >= $event[3] && $event[4] > 0 && $time < $event[4]))
				{
					$count++;
				}
				elseif($event[11] != "")
				{
					$repeat=explode(",",$event[11]);
					$rtime=$event[3];
					if($repeat[1] > time())
					{
						$etime=$repeat[1];
						$time=$event[3];
						$stopper=10;
						$mult=$repeat[0];
						while($stopper > 0 && $rtime < $etime)
						{
							$rtime += ($repeat[0]*7*24*60*60);
							if($event[5] == 0)
							{
								$retime=$event[4] + ($mult*7*24*60*60);
							}
							else
							{
								$retime=0;
							}
							$nevent=array($event[0],$event[1],$event[2],$rtime,$retime,$event[5],$event[6],$event[7],$event[8],$event[9],$event[10],$event[11]);
							if(($nevent[5] == 1 && $time < ($nevent[3]+(24*60*60))) || $time < $nevent[3] || ($time >= $nevent[3] && $nevent[4] > 0 && $time < $nevent[4]))
							{
								$count++;
								break;
							}
							$stopper--;
							$mult+=$repeat[0];
						}
					}
				}
			}
		}
		return $count;
	}
	function get_next_five($eventlist)
	{
		$events=array();
		$nevents=array();
		if(count($eventlist) > 0)
		{
			foreach($eventlist as $event)
			{
				//FORMAT: ID,Name,Owner,Start,End,Allday,Description,Invitees,Private,Location,Created,Repeat
				$time=time();
				if(($event[5] == 1 && $time < ($event[3]+(24*60*60))) || $time < $event[3] || ($time >= $event[3] && $event[4] > 0 && $time < $event[4]))
				{
					$events[]=$event;
				}
				if($event[11] != "")
				{
					$repeat=explode(",",$event[11]);
					$rtime=$event[3];
					if($repeat[1] > $time)
					{
						$etime=$repeat[1];
						$rtime += ($repeat[0]*7*24*60*60);
						$stopper=PHP_INT_MAX;
						$mult=$repeat[0];
						while($stopper > 0 && $rtime < $etime)
						{
							if($event[5] == 0)
							{
								$retime=$event[4] + ($mult*7*24*60*60);
							}
							else
							{
								$retime=0;
							}
							$nevent=array($event[0],$event[1],$event[2],$rtime,$retime,$event[5],$event[6],$event[7],$event[8],$event[9],$event[10],$event[11]);
							if(($nevent[5] == 1 && $time < ($nevent[3]+(24*60*60))) || $time < $nevent[3] || ($time >= $nevent[3] && $nevent[4] > 0 && $time < $nevent[4]))
							{
								$events[]=$nevent;
							}
							$stopper--;
							$rtime += ($repeat[0]*7*24*60*60);
							$mult+=$repeat[0];
						}
					}
				}
			}
		}
		usort($events,"sort_events");
		return array_slice($events,0,5);
	}
	if(isset($_GET['in']) && $_GET['in'] == "yes")
	{
		echo ("<p style=\"color:#00FF00\"><b>Successfully logged in!</b></p>\r\n");
	}
	if(isset($_GET['out']) && $_GET['out'] == "yes")
	{
		echo ("<p style=\"color:#00FF00\"><b>Successfully logged out! Thanks for using this calendar.</b></p>\r\n");
	}
	elseif(isset($_GET['out']) && $_GET['out'] == "no")
	{
		echo ("<p style=\"color:#FF0000\"><b>Failed to log out. The server is in desperate need of some quality frying pan time.</b></p>\r\n");
	}
	if(isset($_GET['reg']) && $_GET['reg'] == "yes")
	{
		echo ("<p style=\"color:#00FF00\"><b>Successfully registered! In order to make events, your account must be enabled by the BOFH.</b></p>\r\n");
	}
	elseif(isset($_GET['reg']) && $_GET['reg'] == "no")
	{
		echo ("<p style=\"color:#FF0000\"><b>Failed to register. Either that username was taken already or the server is in desperate need of some microwave radiation treatment.</b></p>\r\n");
	}
	if(isset($_GET['adu']) && $_GET['adu'] == "yes")
	{
		echo ("<p style=\"color:#00FF00\"><b>Successfully registered user!</b></p>\r\n");
	}
	elseif(isset($_GET['adu']) && $_GET['adu'] == "no")
	{
		echo ("<p style=\"color:#FF0000\"><b>Failed to register user. Either that username was taken already or the server is in desperate need of some microwave radiation treatment.</b></p>\r\n");
	}
	if(isset($_GET['edt']) && $_GET['edt'] == "yes")
	{
		echo ("<p style=\"color:#00FF00\"><b>Successfully edited user!</b></p>\r\n");
	}
	elseif(isset($_GET['edt']) && $_GET['edt'] == "no")
	{
		echo ("<p style=\"color:#FF0000\"><b>Failed to edit user. The server would appreciate a goose to get intimate with.</b></p>\r\n");
	}
	if(isset($_GET['deu']) && $_GET['deu'] == "yes")
	{
		echo ("<p style=\"color:#00FF00\"><b>Successfully deleted user!</b></p>\r\n");
	}
	elseif(isset($_GET['deu']) && $_GET['deu'] == "no")
	{
		echo ("<p style=\"color:#FF0000\"><b>Failed to delete user. The server has defenestrated your modem.</b></p>\r\n");
	}
	if(isset($_GET['set']) && $_GET['set'] == "yes")
	{
		echo ("<p style=\"color:#00FF00\"><b>Successfully changed settings!</b></p>\r\n");
	}
	elseif(isset($_GET['set']) && $_GET['set'] == "no")
	{
		echo ("<p style=\"color:#FF0000\"><b>Failed to change settings. The server would appreciate a goose to get intimate with. Changes may have been applied but are likely not to be permanent.</b></p>\r\n");
	}
	if(isset($_GET['odb']) && $_GET['odb'] == "yes")
	{
		echo ("<p style=\"color:#00FF00\"><b>Successfully optimized database!</b></p>\r\n");
	}
	elseif(isset($_GET['odb']) && $_GET['odb'] == "no")
	{
		echo ("<p style=\"color:#FF0000\"><b>Failed to optimize database. A criticality accident may likely have irradiated it.</b></p>\r\n");
	}
	if(isset($_GET['ade']) && $_GET['ade'] == "yes")
	{
		echo ("<p style=\"color:#00FF00\"><b>Successfully added event!</b></p>\r\n");
	}
	elseif(isset($_GET['ade']) && $_GET['ade'] == "no")
	{
		echo ("<p style=\"color:#FF0000\"><b>Failed to add event. Smack the server with a red courtesy club.</b></p>\r\n");
	}
	if(isset($_GET['ede']) && $_GET['ede'] == "yes")
	{
		echo ("<p style=\"color:#00FF00\"><b>Successfully edited event!</b></p>\r\n");
	}
	elseif(isset($_GET['ede']) && $_GET['ede'] == "no")
	{
		echo ("<p style=\"color:#FF0000\"><b>Failed to edit event. Mark's dog somehow urinated on the server. Fix your &lt;censored&gt;, Mark!</b></p>\r\n");
	}
	elseif(isset($_GET['ede']) && $_GET['ede'] == "nid")
	{
		echo ("<p style=\"color:#FF0000\"><b>Failed to edit event. The server has no idea what event you were trying to edit.</b></p>\r\n");
	}
	elseif(isset($_GET['ede']) && $_GET['ede'] == "nbe")
	{
		echo ("<p style=\"color:#FF0000\"><b>Failed to edit event. Either you are a peon and can't edit this event, or the event was abducted by Russian intelligence.</b></p>\r\n");
	}
	if(isset($_GET['dle']) && $_GET['dle'] == "yes")
	{
		echo ("<p style=\"color:#00FF00\"><b>Successfully delete event!</b></p>\r\n");
	}
	elseif(isset($_GET['dle']) && $_GET['dle'] == "no")
	{
		echo ("<p style=\"color:#FF0000\"><b>Failed to delete event. The server probably microwaved the entire database.</b></p>\r\n");
	}
	if(isset($_GET['csd']) && $_GET['csd'] == "yes")
	{
		echo ("<p style=\"color:#00FF00\"><b>Successfully cleared session data. You will now need to log in again.</b></p>\r\n");
	}
	elseif(isset($_GET['csd']) && $_GET['csd'] == "no")
	{
		echo ("<p style=\"color:#FF0000\"><b>Failed to clear session data. Server fall down and go boom.</b></p>\r\n");
	}
	if(isset($_GET['cdb']) && $_GET['cdb'] == "yes")
	{
		echo ("<p style=\"color:#00FF00\"><b>Successfully cleared all events in the database!</b></p>\r\n");
	}
	elseif(isset($_GET['cdb']) && $_GET['cdb'] == "no")
	{
		echo ("<p style=\"color:#FF0000\"><b>Failed to clear database events. A criticality accident may likely have irradiated it.</b></p>\r\n");
	}
	if(isset($_GET['atk']) && $_GET['atk'] == "yes")
	{
		echo ("<p style=\"color:#00FF00\"><b>Successfully submitted support ticket!</b></p>\r\n");
	}
	elseif(isset($_GET['atk']) && $_GET['atk'] == "no")
	{
		echo ("<p style=\"color:#FF0000\"><b>Failed to submit ticket. Obtain a VISCA camera and spy on the server.</b></p>\r\n");
	}
	if(isset($_GET['etk']) && $_GET['etk'] == "yes")
	{
		echo ("<p style=\"color:#00FF00\"><b>Successfully edited support ticket!</b></p>\r\n");
	}
	elseif(isset($_GET['etk']) && $_GET['etk'] == "no")
	{
		echo ("<p style=\"color:#FF0000\"><b>Failed to edit ticket. The server has determined that selling a bridge is more important than everything else.</b></p>\r\n");
	}
	if(isset($_GET['dtk']) && $_GET['dtk'] == "yes")
	{
		echo ("<p style=\"color:#00FF00\"><b>Successfully deleted support ticket!</b></p>\r\n");
	}
	elseif(isset($_GET['dtk']) && $_GET['dtk'] == "no")
	{
		echo ("<p style=\"color:#FF0000\"><b>Failed to delete ticket. Microwave the software vendor.</b></p>\r\n");
	}
	if(isset($_GET['bad']) && $_GET['bad'] == "yes")
	{
		echo ("<p style=\"color:#FF0000\"><b>You have been caught red-handed trying to circumvent system security. The BOFH will be by shortly with your punishment. In the meantime, simply log in and try again.</b></p>\r\n");
	}
	if(isset($_GET['snp']) && $_GET['snp'] == "yes")
	{
		echo ("<p style=\"color:#FF0000\"><b>Didn't anyone ever tell you no one likes a snoop? The BOFH will be by shortly with your punishment.</b></p>\r\n");
	}
	if(isset($_GET['ved']) && $_GET['ved'] == "bad")
	{
		echo ("<p style=\"color:#FF0000\"><b>The event you are trying to view was microwaved, dunked in a pool, abducted by Russians, or some other ridiculous thing happened to it.</b></p>\r\n");
	}
	echo ("<p>\r\n");
	$db=open_db("db/calendar.sqlite",SQLITE3_OPEN_READONLY);
	$name=get_setting($db,"calendarname");
	$open=get_setting($db,"openviewing");
	$reg=get_setting($db,"allowregistration");
	$usercount=get_user_count($db);
	$events=get_all_events($db);
	$eventcount=count($events);
	$active=get_active_events($events);
	if(isset($_SESSION['username']) && $_SESSION['username'] != "")
	{
		$user=get_user($db,$_SESSION['username']);
		$nextfive=get_next_five(event_display_prepare($user[0],$user[2],$events));
		echo ("Hello, " . $user[1] . " (" . $user[0] . ")! <a href=\"logout.php\">Log out</a> | <a href=\"settings.php\">Preferences</a>");
	}
	else
	{
		echo("Hello, guest! <a href=\"login.php\">Log in</a>");
		$nextfive=get_next_five(event_display_prepare("",0,$events));
		if($reg == "yes")
		{
			echo(" | <a href=\"register.php\">Register</a>");
		}
	}
	$debug=close_db($db);
	if($debug === false)
	{
		trigger_error("The server has caused a criticality accident and the database became irradiated.",E_USER_WARNING);
	}
  ?>
  </p>
  <h1>Low End Calendar-<?php echo stripcslashes($name); ?></h1>
  <p>We presently have <?php echo $usercount; ?> users and <?php echo $eventcount; ?> calendar events. <?php echo $active; ?> calendar events are still active.</p>
  <hr>
  <p>
  <?php
	if(isset($user) && isset($user[2]) && $user[2] == 0)
	{
		echo ("You have been banned from using this calendar.");
	}
	elseif((isset($user) && isset($user[2]) && $user[2] > 0) || $open == "yes")
	{
		if(isset($_SESSION["timezone"]) && $_SESSION["timezone"] != "")
		{
			date_default_timezone_set($_SESSION["timezone"]);
		}
		echo ("<a href=\"viewday.php\">View a day's events</a><br>\r\n<a href=\"viewweek.php\">View a week's events</a><br>\r\n<a href=\"viewmonth.php\">View a month's events</a><br>\r\n<a href=\"viewlist.php\">View an event list</a></p>\r\n<p>Next five events:<br>\r\n");
		if(count($nextfive) > 0)
		{
			foreach($nextfive as $event)
			{
				if(date("l F j, o",$event[3]) == date("l F j, o"))
				{
					$dateflag=true;
					echo("<b>");
				}
				if($event[5] == 1)
				{
					echo("<a href=\"viewevent.php?id=" . $event[0] . "\">" . date("l F j, o",$event[3]));
				}
				else
				{
					echo("<a href=\"viewevent.php?id=" . $event[0] . "\">" . date("l F j, o, g:i A",$event[3]));
					echo(" (ends " . date("l F j, o, g:i A",$event[4]) . ")");
				}
				echo(": " . $event[1] . "</a>");
				if(!empty($dateflag))
				{
					echo("</b>");
				}
				echo("<br>\r\n");
			}
		}
		else
		{
			echo ("No events exist in the system yet.<br>\r\n");
		}
		echo("Note: events in <b>bold</b> occur today.<br>\r\n");
	}
	else
	{
		echo ("This system does not allow non-registered users to view events. Please log in, register, or request an account.");
	}
  ?>
  </p>
  <p>
  <?php
	if(isset($user) && isset($user[2]) && $user[2] >= 2)
	{
		echo ("<a href=\"addevent.php\">Add an event</a><br>\r\n<a href=\"dupevent.php\">Duplicate an event</a><br>\r\n<a href=\"delevent.php\">Delete an event</a>");
	}
  ?>
  </p>
  <p>
  <?php
	if(isset($user) && isset($user[2]) && $user[2] >= 3)
	{
		echo ("<a href=\"system.php\">Change system settings</a><br>\r\n<a href=\"viewlogins.php\">View user login information</a><br>\r\n<a href=\"edituser.php\">Modify a user's details</a><br>\r\n<a href=\"deleteuser.php\">Delete a user</a>");
	}
	elseif(isset($user) && isset($user[2]) && $user[2] >= 1)
	{
		echo ("<a href=\"viewlogins.php\">View user login information</a>");
	}
	if(isset($user) && isset($user[2]) && $user[2] >= 4)
	{
		echo ("<br>\r\n<a href=\"viewerrors.php\">View error log</a><br>\r\n<a href=\"adduser.php\">Create a user</a><br>\r\n<a href=\"optdb.php\">Optimize database</a><br>\r\n<a href=\"delcal.php\">Delete all calendar entries</a><br>\r\n<a href=\"delsess.php\">Delete all session save files</a>");
	}
  ?>
  </p>
  <p>
  <?php
	if(isset($user[2]))
	{
		switch($user[2])
		{
			case 0:
			echo ("<a href=\"unban.php\">Submit an unban request</a><br>\r\n<a href=\"viewtickets.php\">View support tickets</a>\r\n");
			break;
			case 1:
			echo ("<a href=\"reqedit.php\">Request editing permissions</a><br>\r\n<a href=\"viewtickets.php\">View support tickets</a>\r\n");
			break;
			case 2:
			echo ("<a href=\"reqnoedit.php\">Request no editing permissions</a><br>\r\n<a href=\"viewtickets.php\">View support tickets</a>\r\n");
			break;
			case 3:
			case 4:
			default:
			echo ("<a href=\"viewtickets.php\">View support tickets</a>\r\n");
			break;
		}
	}
  ?>
  </p>
  <hr>
  <h6>Low End Calendar is copyright &copy; 2017-2019 Brad Hunter/CarnelProd666. All rights are reserved. Built on pure PHP and CSS, no JavaScript here (except for enabling redirecting). Direct all bug reports, compliments, and hatemail <a href="http://firealarms.redbat.ca/contact.php">here</a>.<br>
  LECal software release <?php echo $version[0] ?> revision <?php echo $version[1] ?>, revision <?php echo $version[2] ?> overall.</h6>
  </body>
</html>