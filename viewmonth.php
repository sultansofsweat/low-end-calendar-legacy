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
    <title>Low End Calendar-View Month's Events</title>
	<style type="text/css">
	th { text-decoration:underline; }
	td { height:150px; }
	</style>
    
  </head>
  <body>
  <?php
	include("functions.php");
	function insert_into_list(&$list,$event)
	{
		//FORMAT: ID,Name,Owner,Start,End,Allday,Description,Invitees,Private,Location,Created,Repeat
		$time=strtotime(date("n/j/Y",$event[3]));
		if($event[5] == 1)
		{
			$etime=$time+(23*60*60);
		}
		else
		{
			$etime=strtotime(date("n/j/Y",$event[4]));
		}
		while($time <= $etime)
		{
			$key=date("n/j/Y",$time);
			if(isset($list[$key]))
			{
				$details="<a href=\"viewevent.php?id=" . $event[0] . "\">";
				if($event[5] == 0 && date("n/j/Y",$time) == date("n/j/Y",$event[3]))
				{
					$details.="[" . date("H:i",$event[3]) . "] ";
					$eventstart=$event[3];
				}
				elseif($event[5] == 0)
				{
					$details.="[" . date("0:00",$event[3]) . "] ";
					$eventstart=$event[3];
				}
				else
				{
					$details.="[24h] ";
					$eventstart=0;
				}
				$details.=$event[1] . "</a><br>\r\n";
				$list[$key][]=array($details,$eventstart);
			}
			$time+=(24*60*60);
		}
	}
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
	function generate_list($month,$year)
	{
		$list=array();
		$btime=strtotime($month . "/1/" . $year);
		$maxtime=strtotime($month . "/" . date("t",$btime) . "/" . $year);
		//$sweek=date("W",$btime);
		//$stime=strtotime($year . "W" . $sweek);
		$dtr=date("N",$btime)-1;
		$stime=$btime-($dtr*24*60*60);
		$eweek=date("W",$maxtime);
		$etime=strtotime($year . "W" . $eweek)+(6*24*60*60);
		$time=$stime;
		while($time <= $etime)
		{
			$key=date("n/j/Y",$time);
			$list[$key]=array();
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
		if(isset($_GET['month']) && isset($_GET['year']))
		{
			$month=preg_replace("/[^0-9]/","",$_GET['month']);
			$year=preg_replace("/[^0-9]/","",$_GET['year']);
			if($month != "" && $year != "")
			{
				$list=generate_list($month,$year);
				$x=strtotime($month . "/1/" . $year);
				$time=strtotime($year . "W" . date("W",$x));
				$date=date("F",$x);
				$ddate=date("F Y",$x);
				$max=strtotime($date . " " . date("t",$x) . " " . $year);
				$eweek=date("W",$max);
				$etime=strtotime($year . "W" . $eweek)+(6*24*60*60);
				$dates=array();
				$ctime=$time;
				while($ctime <= $etime)
				{
					$dates[]=date("n/j/Y",$ctime);
					$ctime+=(24*60*60);
				}
				$events=event_display_prepare($user[0],$user[2],get_all_events($db));
				foreach($events as $event)
				{
					if($event !== false && (in_array(date("n/j/Y",$event[3]),$dates) || ($event[5] == 0 && in_array(date("n/j/Y",$event[4]),$dates))))
					{
						insert_into_list($list,$event);
					}
					if($event[11] != "")
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
			if(isset($_GET['month']) && isset($_GET['year']))
			{
				$month=preg_replace("/[^0-9]/","",$_GET['month']);
				$year=preg_replace("/[^0-9]/","",$_GET['year']);
				if($month != "" && $year != "")
				{
					$list=generate_list($month,$year);
					$x=strtotime($month . "/1/" . $year);
					$time=strtotime($year . "W" . date("W",$x));
					$date=date("F",$x);
					$ddate=date("F Y",$x);
					$max=strtotime($date . " " . date("t",$x) . " " . $year);
					$eweek=date("W",$max);
					$etime=strtotime($year . "W" . $eweek)+(6*24*60*60);
					$dates=array();
					$ctime=$time;
					while($ctime <= $etime)
					{
						$dates[]=date("n/j/Y",$ctime);
						$ctime+=(24*60*60);
					}
					$events=event_display_prepare("",0,get_all_events($db));
					foreach($events as $event)
					{
						if($event !== false && (in_array(date("n/j/Y",$event[3]),$dates) || ($event[5] == 0 && in_array(date("n/j/Y",$event[4]),$dates))))
						{
							insert_into_list($list,$event);
						}
						if($event[11] != "")
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
  <h1>Low End Calendar-View A Month's Events</h1>
  <p>Use the form below to select a month. Note that the month MUST be numerical! You may also run into issues with a two-digit year.</p>
  <form method="get" action="viewmonth.php">
  Month: <input type="text" name="month" value="<?php if(isset($month)) { echo $month; } else { echo date("n"); } ?>"><br>
  Year: <input type="text" name="year" value="<?php if(isset($year)) { echo $year; } else { echo date("Y"); } ?>"><br>
  <input type="submit" value="View events">
  </form>
  <hr>
  <h3>Events for month of: <?php if(isset($ddate)) { echo $ddate; } ?></h3>
  <table width="100%">
  <tr>
  <th>Monday</th>
  <th>Tuesday</th>
  <th>Wednesday</th>
  <th>Thursday</th>
  <th>Friday</th>
  <th>Saturday</th>
  <th>Sunday</th>
  </tr>
  <?php
	if(isset($list) && count($list) > 0)
	{
		$i=0;
		foreach($list as $key=>$value)
		{
			if($i == 0)
			{
				echo("<tr>\r\n");
			}
			$display="";
			$itemcount=0;
			$ndiscount=0;
			if(count($value) > 0)
			{
				usort($value,"sort_events");
				foreach($value as $entry)
				{
					if($itemcount >= 5)
					{
						$ndiscount++;
					}
					else
					{
						$display.=$entry[0];
					}
					$itemcount++;
				}
			}
			$display=substr($display,0,-6);
			if($ndiscount > 0)
			{
				$dayinfo=explode("/",$key);
				$display.="<br>\r\n<a href=\"viewday.php?day=" . $dayinfo[1] . "&month=" . $dayinfo[0] . "&year=" . $dayinfo[2] . "\">+$ndiscount more</a>\r\n";
			}
			if($itemcount > 0)
			{
				//echo ("<td style=\"background-color:#AAAAAA;\">");
				echo ("<td class=\"event-day\">");
			}
			else
			{
				echo ("<td>");
			}
			echo ("<b><u>");
			if(date("n",strtotime($key)) != $month)
			{
				echo date("F j",strtotime($key));
			}
			else
			{
				echo date("j",strtotime($key));
			}
			echo ("</u></b><br>\r\n$display</td>\r\n");
			$i++;
			if($i > 6)
			{
				echo ("</tr>\r\n");
				$i=0;
			}
		}
	}
  ?>
  </table>
  <p><a href="index.php">Go back to main page</a></p>
  </body>
</html>