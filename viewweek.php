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
    <title>Low End Calendar-View Week's Events</title>
	<!--<style type="text/css">
	table,tr,td { border: 1px solid #000000; }
	</style>-->
    
  </head>
  <body>
  <?php
	include("functions.php");
	function generate_day_list()
	{
		$t=array("AM","PM");
		$m=array("00",10,20,30,40,50);
		$h=array(12,1,2,3,4,5,6,7,8,9,10,11);
		$list=array();
		
		foreach($t as $x)
		{
			foreach($h as $y)
			{
				foreach($m as $z)
				{
					$key=$y . ":" . $z . " " . $x;
					$list[$key]="";
				}
			}
		}
		
		$list["allday"]="";
		
		return $list;
	}
	function insert_into_list(&$list,$event)
	{
		//FORMAT: ID,Name,Owner,Start,End,Allday,Description,Invitees,Private,Location,Created,Repeat
		if($event[5] == 1)
		{
			$list[date("n/j/Y",$event[3])]["allday"].="<a href=\"viewevent.php?id=" . $event[0] . "\">" . $event[1] . "</a><br>\r\n";
		}
		else
		{
			$time=$event[3] - ((date("i",$event[3]) % 10)*60);
			$etime=$event[4] - ((date("i",$event[4]) % 10)*60) + 600;
			
			while($time < $etime)
			{
				$key=date("n/j/Y",$time);
				$key2=date("g:i A",$time);
				if(isset($list[$key][$key2]))
				{
					$list[$key][$key2].="<a href=\"viewevent.php?id=" . $event[0] . "\">" . $event[1] . "</a><br>\r\n";
				}
				$time+=(10*60);
			}
		}
	}
	function get_week_list($sdate,$edate)
	{
		$list=array();
		$time=strtotime($sdate);
		$etime=strtotime($edate);
		while($time < $etime)
		{
			$list[date("n/j/Y",$time)]=generate_day_list();
			$time+=(24*60*60);
		}
		return $list;
	}
	
	if(isset($_SESSION['username']) && $_SESSION['username'] != "")
	{
		$db=open_db("db/calendar.sqlite",SQLITE3_OPEN_READONLY);
		$user=get_user($db,$_SESSION['username']);
		if(isset($user[2]) && $user[2] < 1)
		{
			die("<script type=\"text/javascript\">window.location = \"index.php?bad=yes\"</script>");
		}
		if(isset($_GET['day']) && isset($_GET['month']) && isset($_GET['year']))
		{
			$day=preg_replace("/[^0-9]/","",$_GET['day']);
			$month=preg_replace("/[^0-9]/","",$_GET['month']);
			$year=preg_replace("/[^0-9]/","",$_GET['year']);
			if($day != "" && $month != "" && $year != "")
			{
				$date=strtotime($month . "/" . $day . "/" . $year);
				$time=strtotime(date("Y",$date) . "W" . date("W",$date));
				$sdate=date("m/d/Y",$time);
				$edate=date("m/d/Y",$time+(7*24*60*60));
				$list=get_week_list($sdate,$edate);
				$dates=array();
				for($i=0;$i<7;$i++)
				{
					$dates[]=date("n/j/Y",$time+($i*24*60*60));
				}
				$events=event_display_prepare($user[0],$user[2],get_all_events($db));
				foreach($events as $event)
				{
					if($event !== false && (in_array(date("n/j/Y",$event[3]),$dates) || ($event[5] == 0 && in_array(date("n/j/Y",$event[4]),$dates))))
					{
						insert_into_list($list,$event);
					}
					elseif($event[11] != "")
					{
						$repeat=explode(",",$event[11]);
						$rtime=$event[3];
						if($repeat[1] > $time)
						{
							$etime=$repeat[1];
							$rtime += ($repeat[0]*7*24*60*60);
							$mult=1;
							while($rtime < $etime)
							{
								if($event[5] == 0)
								{
									$retime=$event[4] + ($repeat[0]*$mult*7*24*60*60);
								}
								else
								{
									$retime=0;
								}
								$nevent=array($event[0],$event[1],$event[2],$rtime,$retime,$event[5],$event[6],$event[7],$event[8],$event[9],$event[10],$event[11]);
								if(in_array(date("n/j/Y",$nevent[3]),$dates) || ($nevent[5] == 0 && in_array(date("n/j/Y",$nevent[4]),$dates)))
								{
									insert_into_list($list,$nevent);
									break;
								}
								$rtime += ($repeat[0]*7*24*60*60);
								$mult++;
							}
						}
					}
				}
			}
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
		$open=get_setting($db,"openviewing");
		if($open == "yes")
		{
			if(isset($_GET['day']) && isset($_GET['month']) && isset($_GET['year']))
			{
				$day=preg_replace("/[^0-9]/","",$_GET['day']);
				$month=preg_replace("/[^0-9]/","",$_GET['month']);
				$year=preg_replace("/[^0-9]/","",$_GET['year']);
				if($day != "" && $month != "" && $year != "")
				{
					$date=strtotime($month . "/" . $day . "/" . $year);
					$time=strtotime(date("Y",$date) . "W" . date("W",$date));
					$sdate=date("m/d/Y",$time);
					$edate=date("m/d/Y",$time+(7*24*60*60));
					$list=get_week_list($sdate,$edate);
					$dates=array();
					for($i=0;$i<7;$i++)
					{
						$dates[]=date("n/j/Y",$time+($i*24*60*60));
					}
					$events=event_display_prepare("",0,get_all_events($db));
					foreach($events as $event)
					{
						if($event !== false && (in_array(date("n/j/Y",$event[3]),$dates) || ($event[5] == 0 && in_array(date("n/j/Y",$event[4]),$dates))))
						{
							insert_into_list($list,$event);
						}
						elseif($event[11] != "")
						{
							$repeat=explode(",",$event[11]);
							$rtime=$event[3];
							if($repeat[1] > $time)
							{
								$etime=$repeat[1];
								$rtime += ($repeat[0]*7*24*60*60);
								$mult=1;
								while($rtime < $etime)
								{
									if($event[5] == 0)
									{
										$retime=$event[4] + ($repeat[0]*$mult*7*24*60*60);
									}
									else
									{
										$retime=0;
									}
									$nevent=array($event[0],$event[1],$event[2],$rtime,$retime,$event[5],$event[6],$event[7],$event[8],$event[9],$event[10],$event[11]);
									if(in_array(date("n/j/Y",$nevent[3]),$dates) || ($nevent[5] == 0 && in_array(date("n/j/Y",$nevent[4]),$dates)))
									{
										insert_into_list($list,$nevent);
										break;
									}
									$rtime += ($repeat[0]*7*24*60*60);
									$mult++;
								}
							}
						}
					}
				}
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
  ?>
  </p>
  <h1>Low End Calendar-View A Week's Events</h1>
  <p>Use the form below to select a day. The system will select and display events from all days of the week that contains the specified day. Note that the month MUST be numerical! You may also run into issues with a two-digit year.</p>
  <form method="get" action="viewweek.php">
  Month: <input type="text" name="month" value="<?php if(isset($month)) { echo $month; } else { echo date("n"); } ?>"><br>
  Day: <input type="text" name="day" value="<?php if(isset($day)) { echo $day; } else { echo date("j"); } ?>"><br>
  Year: <input type="text" name="year" value="<?php if(isset($year)) { echo $year; } else { echo date("Y"); } ?>"><br>
  <input type="submit" value="View events">
  </form>
  <hr>
  <h3>Events for week of: <?php if(isset($date)) { echo date("n/j/Y",$date); } ?></h3>
  <table width="100%">
  <?php
	if(isset($date))
	{
		$t=array("AM","PM");
		$m=array("00",10,20,30,40,50);
		$h=array(12,1,2,3,4,5,6,7,8,9,10,11);
		
		echo("<tr>\r\n<th></th>\r\n");
		foreach($list as $key=>$value)
		{
			echo("<th>");
			$text=date("D n/j/Y",strtotime($key));
			if($key == date("n/j/Y"))
			{
				$text="<i>$text</i>";
			}
			if($key == date("n/j/Y",$date))
			{
				$text="<u>$text</u>";
			}
			echo("$text</th>\r\n");
		}
		echo("</tr>\r\n");
		echo("<tr>\r\n<td>All-day</td>\r\n");
		foreach($list as $dlist)
		{
			if($dlist["allday"] != "")
			{
				//echo ("<td style=\"background-color:#AAAAAA;\">");
				echo ("<td class=\"event-day\">");
			}
			else
			{
				echo ("<td>");
			}
			echo (substr($dlist["allday"],0,-8) . "</td>\r\n");
		}
		echo("</tr>\r\n");
		foreach($t as $x)
		{
			foreach($h as $y)
			{
				foreach($m as $z)
				{
					$k=$y . ":" . $z . " " . $x;
					echo ("<tr>\r\n");
					echo ("<td width=\"80px\">$k</td>");
					foreach($list as $dlist)
					{
						if($dlist[$k] != "")
						{
							//echo ("<td style=\"background-color:#AAAAAA;\">");
							echo ("<td class=\"event-day\">");
						}
						else
						{
							echo ("<td>");
						}
						echo (substr($dlist[$k],0,-8) . "</td>\r\n");
					}
					echo ("</tr>\r\n");
				}
			}
		}
	}
  ?>
  </table>
  <p><a href="index.php">Go back to main page</a></p>
  </body>
</html>