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
    <title>Low End Calendar-View Event List</title>
    
  </head>
  <body>
  <?php
	function sort_events($a,$b)
	{
		if(!isset($a[1]) || !isset($b[1]) || $a[1] == $b[1])
		{
			return 0;
		}
		elseif($a[1] < $b[1])
		{
			return -1;
		}
		return 1;
	}
	
	if(isset($_SESSION['username']) && $_SESSION['username'] != "")
	{
		$db=open_db("db/calendar.sqlite",SQLITE3_OPEN_READONLY);
		$user=get_user($db,$_SESSION['username']);
		if(isset($user[2]) && $user[2] < 1)
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
		$db=open_db("db/calendar.sqlite",SQLITE3_OPEN_READONLY);
		$open=get_setting($db,"openviewing");
		if($open == "yes")
		{
			$events=event_display_prepare("",0,get_all_events($db));
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
  ?>
  </p>
  <h1>Low End Calendar-View All Events</h1>
  <?php
	if(count($events) > 0)
	{
		$today=array();
		$active=array();
		$past=array();
		foreach($events as $event)
		{
			//FORMAT: ID,Name,Owner,Start,End,Allday,Description,Invitees,Private,Location,Created,Repeat
			$edetails=array("",$event[3]);
			$edetails[0]="<p>";
			if($event[8] == 1)
			{
				$edetails[0].="[PRIVATE";
			}
			else
			{
				$edetails[0].="[PUBLIC";
			}
			if($event[11] != "")
			{
				$edetails[0].=",REPEATING";
			}
			$edetails[0].="] ";
			if($event[5] == 1)
			{
				$edetails[0].="<a href=\"viewevent.php?id=" . $event[0] . "\">" . date("l F j, o",$event[3]);
			}
			else
			{
				$edetails[0].="<a href=\"viewevent.php?id=" . $event[0] . "\">" . date("l F j, o, g:i A",$event[3]) . " (ends " . date("l F j, o, g:i A",$event[4]) . ")";
			}
			$edetails[0].=": " . $event[1] . "</a></p>\r\n";
			$time=time();
			if((($event[5] == 1 && $time < ($event[3]+(24*60*60))) || $time < $event[3] || ($time >= $event[3] && $event[4] > 0 && $time < $event[4])))
			{
				if(date("l F j, o",$event[3]) == date("l F j, o"))
				{
					$today[]=$edetails;
				}
				else
				{
					$active[]=$edetails;
				}
			}
			elseif($event[11] != "")
			{
				$repeat=explode(",",$event[11]);
				if($repeat[1] > time())
				{
					$etime=$repeat[1];
					$rtime=$event[3];
					$mult=$repeat[0];
					while($rtime < $etime)
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
						if(date("I") == 0 && date("I",$rtime) == 1)
						{
							$disprtime=$rtime-(60*60);
						}
						elseif(date("I") == 1 && date("I",$rtime) == 0)
						{
							$disprtime=$rtime+(60*60);
						}
						else
						{
							$disprtime=$rtime;
						}
						if($retime > 0 && date("I") == 0 && date("I",$retime) == 1)
						{
							$dispretime=$retime-(60*60);
						}
						elseif($retime > 0 && date("I") == 1 && date("I",$retime) == 0)
						{
							$dispretime=$retime+(60*60);
						}
						else
						{
							$dispretime=$retime;
						}
						$nevent=array($event[0],$event[1],$event[2],$disprtime,$dispretime,$event[5],$event[6],$event[7],$event[8],$event[9],$event[10],$event[11]);
						if(($nevent[5] == 1 && $time < ($nevent[3]+(24*60*60))) || $time < $nevent[3] || ($time >= $nevent[3] && $nevent[4] > 0 && $time < $nevent[4]))
						{
							$nedetails=array("",$rtime);
							$nedetails[0]="<p>";
							if($nevent[8] == 1)
							{
								$nedetails[0].="[PRIVATE";
							}
							else
							{
								$nedetails[0].="[PUBLIC";
							}
							if($nevent[11] != "")
							{
								$nedetails[0].=",REPEATING";
							}
							$nedetails[0].="] ";
							if($nevent[5] == 1)
							{
								$nedetails[0].="<a href=\"viewevent.php?id=" . $nevent[0] . "\">" . date("l F j, o",$nevent[3]);
							}
							else
							{
								$nedetails[0].="<a href=\"viewevent.php?id=" . $nevent[0] . "\">" . date("l F j, o, g:i A",$nevent[3]) . " (ends " . date("l F j, o, g:i A",$nevent[4]) . ")";
							}
							$nedetails[0].=": " . $nevent[1] . "</a></p>\r\n";
							if(date("l F j, o",$nevent[3]) == date("l F j, o"))
							{
								$today[]=$nedetails;
							}
							else
							{
								$active[]=$nedetails;
							}
							break;
						}
						$mult+=$repeat[0];
					}
				}
				else
				{
					$past[]=$edetails;
				}
			}
			else
			{
				$past[]=$edetails;
			}
		}
		
		usort($today,"sort_events");
		usort($active,"sort_events");
		usort($past,"sort_events");
		$past=array_reverse($past);
		
		echo ("<p><b><u>Today's Events</u></b></p>\r\n");
		foreach($today as $edetails)
		{
			echo $edetails[0];
		}
		echo ("<p><b><u>Active Events</u></b></p>\r\n");
		foreach($active as $edetails)
		{
			echo $edetails[0];
		}
		echo ("<p><b><u>Past Events</u></b></p>\r\n");
		foreach($past as $edetails)
		{
			echo $edetails[0];
		}
	}
	else
	{
		echo ("<p>There are no events for the system to display.</p>\r\n<hr>\r\n");
	}
  ?>
  <p><a href="viewday.php">Switch to daily view</a><br>
  <a href="viewweek.php">Switch to weekly view</a><br>
  <a href="viewmonth.php">Switch to monthly view</a><br><br>
  <a href="index.php">Go back to main page</a></p>
  </body>
</html>