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
    <title>Low End Calendar-View Day's Events</title>
	<!--<style type="text/css">
	table,tr,td { border: 1px solid #000000; }
	</style>-->
    
  </head>
  <body>
  <?php
	
	function generate_list()
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
	function insert_into_list(&$list,$event,$date)
	{
		//FORMAT: ID,Name,Owner,Start,End,Allday,Description,Invitees,Private,Location,Created,Repeat
		if($event[5] == 1 && date("n/j/Y",$event[3]) == $date)
		{
			$list["allday"].="<a href=\"viewevent.php?id=" . $event[0] . "\">" . $event[1] . "</a><br>\r\n";
		}
		else
		{
			$time=$event[3] - ((date("i",$event[3]) % 10)*60);
			$etime=($event[4]-(10*60)) - ((date("i",$event[4]) % 10)*60) + 600;
			while($time < $etime)
			{
				if(date("n/j/Y",$time) == $date && isset($list[date("g:i A",$time)]))
				{
					$list[date("g:i A",$time)].="<a href=\"viewevent.php?id=" . $event[0] . "\">" . $event[1] . "</a><br>\r\n";
				}
				$time+=(10*60);
			}
		}
	}
	
	$list=array();
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
				if($date !== false)
				{
					$time=$date;
					$events=event_display_prepare($user[0],$user[2],get_all_events($db));
					$list=generate_list();
					foreach($events as $event)
					{
						if($event !== false && $event[11] == "")
						{
							insert_into_list($list,$event,date("n/j/Y",$date));
						}
						elseif($event !== false)
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
									if(date("n/j/Y",$nevent[3]) == date("n/j/Y",$date) || ($nevent[5] == 0 && date("n/j/Y",$nevent[4]) == date("n/j/Y",$date)))
									{
										insert_into_list($list,$nevent,date("n/j/Y",$date));
										break;
									}
									$rtime += ($repeat[0]*7*24*60*60);
									$mult++;
								}
							}
						}
					}
					if($list["allday"] == "")
					{
						$list["allday"]="None at this time.";
					}
				}
				else
				{
					echo("<span style=\"color:#FF0000;\"><b>The date \"$month/$day/$year\" is invalid. Enjoy your nasal demons.</b></span>");
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
                    if($date !== false)
					{
						$time=$date;
						$events=event_display_prepare($user[0],$user[2],get_all_events($db));
						$list=generate_list();
						foreach($events as $event)
						{
							if($event !== false && $event[11] == "")
							{
								insert_into_list($list,$event,date("n/j/Y",$date));
							}
							elseif($event !== false)
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
										if(date("n/j/Y",$nevent[3]) == date("n/j/Y",$date) || ($nevent[5] == 0 && date("n/j/Y",$nevent[4]) == date("n/j/Y",$date)))
										{
											insert_into_list($list,$nevent,date("n/j/Y",$date));
											break;
										}
										$rtime += ($repeat[0]*7*24*60*60);
										$mult++;
									}
								}
							}
						}
						if($list["allday"] == "")
						{
							$list["allday"]="None at this time.";
						}
					}
					else
					{
						echo("<span style=\"color:#FF0000;\"><b>The date \"$month/$day/$year\" is invalid. Enjoy your nasal demons.</b></span>");
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
  <h1>Low End Calendar-View A Day's Events</h1>
  <p>Use the form below to select a day. Selecting an invalid date (like February 31st for example) will probably summon nasal demons. Note also that you may run into issues with a two-digit year.</p>
  <form method="get" action="viewday.php">
  Month: <select name="month" required="required">
  <option value="">-Select-</option>
  <option value="1"<?php if(isset($month) && $month == 1) { echo " selected=\"selected\""; } elseif(!isset($month) && date("n") == 1) { echo " selected=\"selected\""; } ?>>January</option>
  <option value="2"<?php if(isset($month) && $month == 2) { echo " selected=\"selected\""; } elseif(!isset($month) && date("n") == 2) { echo " selected=\"selected\""; } ?>>February</option>
  <option value="3"<?php if(isset($month) && $month == 3) { echo " selected=\"selected\""; } elseif(!isset($month) && date("n") == 3) { echo " selected=\"selected\""; } ?>>March</option>
  <option value="4"<?php if(isset($month) && $month == 4) { echo " selected=\"selected\""; } elseif(!isset($month) && date("n") == 4) { echo " selected=\"selected\""; } ?>>April</option>
  <option value="5"<?php if(isset($month) && $month == 5) { echo " selected=\"selected\""; } elseif(!isset($month) && date("n") == 5) { echo " selected=\"selected\""; } ?>>May</option>
  <option value="6"<?php if(isset($month) && $month == 6) { echo " selected=\"selected\""; } elseif(!isset($month) && date("n") == 6) { echo " selected=\"selected\""; } ?>>June</option>
  <option value="7"<?php if(isset($month) && $month == 7) { echo " selected=\"selected\""; } elseif(!isset($month) && date("n") == 7) { echo " selected=\"selected\""; } ?>>July</option>
  <option value="8"<?php if(isset($month) && $month == 8) { echo " selected=\"selected\""; } elseif(!isset($month) && date("n") == 8) { echo " selected=\"selected\""; } ?>>August</option>
  <option value="9"<?php if(isset($month) && $month == 9) { echo " selected=\"selected\""; } elseif(!isset($month) && date("n") == 9) { echo " selected=\"selected\""; } ?>>September</option>
  <option value="10"<?php if(isset($month) && $month == 10) { echo " selected=\"selected\""; } elseif(!isset($month) && date("n") == 10) { echo " selected=\"selected\""; } ?>>October</option>
  <option value="11"<?php if(isset($month) && $month == 11) { echo " selected=\"selected\""; } elseif(!isset($month) && date("n") == 11) { echo " selected=\"selected\""; } ?>>November</option>
  <option value="12"<?php if(isset($month) && $month == 12) { echo " selected=\"selected\""; } elseif(!isset($month) && date("n") == 12) { echo " selected=\"selected\""; } ?>>December</option>
  </select><br>
  Day: <select name="day" required="required">
  <option value="">-Select-</option>
  <option value="1"<?php if(isset($day) && $day == 1) { echo " selected=\"selected\""; } elseif(!isset($day) && date("j") == 1) { echo " selected=\"selected\""; } ?>>1</option>
  <option value="2"<?php if(isset($day) && $day == 2) { echo " selected=\"selected\""; } elseif(!isset($day) && date("j") == 2) { echo " selected=\"selected\""; } ?>>2</option>
  <option value="3"<?php if(isset($day) && $day == 3) { echo " selected=\"selected\""; } elseif(!isset($day) && date("j") == 3) { echo " selected=\"selected\""; } ?>>3</option>
  <option value="4"<?php if(isset($day) && $day == 4) { echo " selected=\"selected\""; } elseif(!isset($day) && date("j") == 4) { echo " selected=\"selected\""; } ?>>4</option>
  <option value="5"<?php if(isset($day) && $day == 5) { echo " selected=\"selected\""; } elseif(!isset($day) && date("j") == 5) { echo " selected=\"selected\""; } ?>>5</option>
  <option value="6"<?php if(isset($day) && $day == 6) { echo " selected=\"selected\""; } elseif(!isset($day) && date("j") == 6) { echo " selected=\"selected\""; } ?>>6</option>
  <option value="7"<?php if(isset($day) && $day == 7) { echo " selected=\"selected\""; } elseif(!isset($day) && date("j") == 7) { echo " selected=\"selected\""; } ?>>7</option>
  <option value="8"<?php if(isset($day) && $day == 8) { echo " selected=\"selected\""; } elseif(!isset($day) && date("j") == 8) { echo " selected=\"selected\""; } ?>>8</option>
  <option value="9"<?php if(isset($day) && $day == 9) { echo " selected=\"selected\""; } elseif(!isset($day) && date("j") == 9) { echo " selected=\"selected\""; } ?>>9</option>
  <option value="10"<?php if(isset($day) && $day == 10) { echo " selected=\"selected\""; } elseif(!isset($day) && date("j") == 10) { echo " selected=\"selected\""; } ?>>10</option>
  <option value="11"<?php if(isset($day) && $day == 11) { echo " selected=\"selected\""; } elseif(!isset($day) && date("j") == 11) { echo " selected=\"selected\""; } ?>>11</option>
  <option value="12"<?php if(isset($day) && $day == 12) { echo " selected=\"selected\""; } elseif(!isset($day) && date("j") == 12) { echo " selected=\"selected\""; } ?>>12</option>
  <option value="13"<?php if(isset($day) && $day == 13) { echo " selected=\"selected\""; } elseif(!isset($day) && date("j") == 13) { echo " selected=\"selected\""; } ?>>13</option>
  <option value="14"<?php if(isset($day) && $day == 14) { echo " selected=\"selected\""; } elseif(!isset($day) && date("j") == 14) { echo " selected=\"selected\""; } ?>>14</option>
  <option value="15"<?php if(isset($day) && $day == 15) { echo " selected=\"selected\""; } elseif(!isset($day) && date("j") == 15) { echo " selected=\"selected\""; } ?>>15</option>
  <option value="16"<?php if(isset($day) && $day == 16) { echo " selected=\"selected\""; } elseif(!isset($day) && date("j") == 16) { echo " selected=\"selected\""; } ?>>16</option>
  <option value="17"<?php if(isset($day) && $day == 17) { echo " selected=\"selected\""; } elseif(!isset($day) && date("j") == 17) { echo " selected=\"selected\""; } ?>>17</option>
  <option value="18"<?php if(isset($day) && $day == 18) { echo " selected=\"selected\""; } elseif(!isset($day) && date("j") == 18) { echo " selected=\"selected\""; } ?>>18</option>
  <option value="19"<?php if(isset($day) && $day == 19) { echo " selected=\"selected\""; } elseif(!isset($day) && date("j") == 19) { echo " selected=\"selected\""; } ?>>19</option>
  <option value="20"<?php if(isset($day) && $day == 20) { echo " selected=\"selected\""; } elseif(!isset($day) && date("j") == 20) { echo " selected=\"selected\""; } ?>>20</option>
  <option value="21"<?php if(isset($day) && $day == 21) { echo " selected=\"selected\""; } elseif(!isset($day) && date("j") == 21) { echo " selected=\"selected\""; } ?>>21</option>
  <option value="22"<?php if(isset($day) && $day == 22) { echo " selected=\"selected\""; } elseif(!isset($day) && date("j") == 22) { echo " selected=\"selected\""; } ?>>22</option>
  <option value="23"<?php if(isset($day) && $day == 23) { echo " selected=\"selected\""; } elseif(!isset($day) && date("j") == 23) { echo " selected=\"selected\""; } ?>>23</option>
  <option value="24"<?php if(isset($day) && $day == 24) { echo " selected=\"selected\""; } elseif(!isset($day) && date("j") == 24) { echo " selected=\"selected\""; } ?>>24</option>
  <option value="25"<?php if(isset($day) && $day == 25) { echo " selected=\"selected\""; } elseif(!isset($day) && date("j") == 25) { echo " selected=\"selected\""; } ?>>25</option>
  <option value="26"<?php if(isset($day) && $day == 26) { echo " selected=\"selected\""; } elseif(!isset($day) && date("j") == 26) { echo " selected=\"selected\""; } ?>>26</option>
  <option value="27"<?php if(isset($day) && $day == 27) { echo " selected=\"selected\""; } elseif(!isset($day) && date("j") == 27) { echo " selected=\"selected\""; } ?>>27</option>
  <option value="28"<?php if(isset($day) && $day == 28) { echo " selected=\"selected\""; } elseif(!isset($day) && date("j") == 28) { echo " selected=\"selected\""; } ?>>28</option>
  <option value="29"<?php if(isset($day) && $day == 29) { echo " selected=\"selected\""; } elseif(!isset($day) && date("j") == 29) { echo " selected=\"selected\""; } ?>>29</option>
  <option value="30"<?php if(isset($day) && $day == 30) { echo " selected=\"selected\""; } elseif(!isset($day) && date("j") == 30) { echo " selected=\"selected\""; } ?>>30</option>
  <option value="31"<?php if(isset($day) && $day == 31) { echo " selected=\"selected\""; } elseif(!isset($day) && date("j") == 31) { echo " selected=\"selected\""; } ?>>31</option>
  </select><br>
  Year: <input type="text" name="year" value="<?php if(isset($year)) { echo $year; } else { echo date("Y"); } ?>" required="required"><br>
  <input type="submit" value="View events">
  </form>
  <hr>
  <h3>Events for date: <?php if(isset($date)) { echo date("l F j, Y",$date); } ?></h3>
  <?php
	if(isset($list["allday"]))
	{
		echo("<p>All-day events:<br>" . $list["allday"] . "</p>");
	}
  ?>
  <table width="90%">
  <?php
	if(isset($list) && count($list) > 0)
	{
		$t=array("AM","PM");
		$m=array("00",10,20,30,40,50);
		$h=array(12,1,2,3,4,5,6,7,8,9,10,11);
		
		foreach($t as $x)
		{
			foreach($h as $y)
			{
				foreach($m as $z)
				{
					$key=$y . ":" . $z . " " . $x;
					echo ("<tr>\r\n");
					echo ("<td width=\"10%\">$key</td>");
					if($list[$key] != "")
					{
						//echo ("<td style=\"background-color:#AAAAAA;\">");
						echo ("<td class=\"event-day\">");
					}
					else
					{
						echo ("<td>");
					}
					echo (substr($list[$key],0,-8) . "</td>\r\n");
					echo ("</tr>\r\n");
				}
			}
		}
	}
  ?>
  </table>
  <p><a href="viewweek.php">Switch to weekly view</a><br>
  <a href="viewmonth.php">Switch to monthly view</a><br>
  <a href="viewlist.php">Switch to list view</a><br><br>
  <a href="index.php">Go back to main page</a></p>
  </body>
</html>