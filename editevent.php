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
    <title>Low End Calendar-Edit Event</title>
    
  </head>
  <body>
  <p>
  <?php
	
	function check_if_valid($name,$month,$day,$year,$shour,$sminute,$smerid,$allday,$duration,$dmod,$public)
	{
		if($name === false || $month === false || $day === false || $year === false || $shour === false || $sminute === false || $smerid === false || $public === false)
		{
			return false;
		}
		if($allday == 0 && $duration === false)
		{
			return false;
		}
		$validmonths=array("January","February","March","April","May","June","July","August","September","October","November","December");
		if(!in_array($month,$validmonths))
		{
			trigger_error("That is not a valid date.");
			return false;
		}
		if($day < 0 || $day > 31 || $shour < 0 || $shour > 12 || $sminute < 0 || $sminute > 59 || ($smerid != "AM" && $smerid != "PM") || ($duration !== false && $duration < 0))
		{
			trigger_error("That is not a valid date or duration.");
			return false;
		}
		return true;
	}
	function check_valid_repeat($frequency,$month,$day,$year)
	{
		if($frequency !== false && $month !== false && $day !== false && $year !== false)
		{
			if(strtotime($month . " " . $day . " " . $year) !== false)
			{
				return true;
			}
			else
			{
				trigger_error("$month $day $year is not a valid date.");
			}
		}
		elseif($frequency === false && $month === false && $day === false && $year === false)
		{
			return true;
		}
		return false;
	}
	//Make sure user is signed in, and perform basic setup
	if(isset($_SESSION['username']) && $_SESSION['username'] != "")
	{
		$db=open_db("db/calendar.sqlite",SQLITE3_OPEN_READONLY);
		$user=get_user($db,$_SESSION['username']);
		if(isset($user[2]) && $user[2] < 2)
		{
			die("<script type=\"text/javascript\">window.location = \"index.php?bad=yes\"</script>");
		}
		if(isset($_GET['id']))
		{
			$id=preg_replace("/[^0-9]/","",$_GET['id']);
			$event=get_event($db,$id);
			if($event !== false && ($user[2] >= 3 || $event[1] == $user[0]))
			{
				//FORMAT: Name,Owner,Start,End,Allday,Description,Invitees,Private,Location,Created,Repeat
				$month=date("F",$event[2]);
				$day=date("j",$event[2]);
				$year=date("Y",$event[2]);
				$shour=date("g",$event[2]);
				$sminute=date("i",$event[2]);
				$smerid=date("A",$event[2]);
				if($event[3] > 0)
				{
					$duration=($event[3]-$event[2])/60;
				}
				else
				{
					$duration=3;
				}
				if($event[10] != "")
				{
					$repeat="yes";
					$rinfo=explode(",",$event[10]);
					$frequency=$rinfo[0];
					$rmonth=date("F",$rinfo[1]);
					$rday=date("j",$rinfo[1]);
					$ryear=date("Y",$rinfo[1]);
				}
				else
				{
					$repeat="no";
					$frequency=1;
					$etime=time()+(30*24*60*60);
					$rmonth=date("F",$etime);
					$rday=date("j",$etime);
					$ryear=date("Y",$etime);
				}
				$users=get_all_users($db);
			}
			else
			{
				die("<script type=\"text/javascript\">window.location = \"index.php?ede=nbe\"</script>");
			}
		}
		elseif(isset($_POST['s']))
		{
			$id=preg_replace("/[^0-9]/","",$_POST['s']);
			$event=get_event($db,$id);
			if($event !== false && ($user[2] >= 3 || $event[1] == $user[0]))
			{
				//FORMAT: Name,Owner,Start,End,Allday,Description,Invitees,Private,Location,Created,Repeat
				$month=date("F",$event[2]);
				$day=date("j",$event[2]);
				$year=date("Y",$event[2]);
				$shour=date("g",$event[2]);
				$sminute=date("i",$event[2]);
				$smerid=date("A",$event[2]);
				if($event[3] > 0)
				{
					$duration=($event[3]-$event[2])/60;
				}
				else
				{
					$duration=3;
				}
				if($event[10] != "")
				{
					$repeat="yes";
					$rinfo=explode(",",$event[10]);
					$frequency=$rinfo[0];
					$rmonth=date("F",$rinfo[1]);
					$rday=date("j",$rinfo[1]);
					$ryear=date("Y",$rinfo[1]);
				}
				else
				{
					$repeat="no";
					$frequency=1;
					$etime=time()+(30*24*60*60);
					$rmonth=date("F",$etime);
					$rday=date("j",$etime);
					$ryear=date("Y",$etime);
				}
				$users=get_all_users($db);
			}
			else
			{
				die("<script type=\"text/javascript\">window.location = \"index.php?ede=nbe\"</script>");
			}
		}
		else
		{
			die("<script type=\"text/javascript\">window.location = \"index.php?ede=nid\"</script>");
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
	//Process submission, if one exists
	if(isset($_POST['s']) && $_POST['s'] != "")
	{
		//Make sure user is logged in
		if(isset($_SESSION['username']) && $_SESSION['username'] != "")
		{
			$db=open_db("db/calendar.sqlite",SQLITE3_OPEN_READWRITE);
			$user=get_user($db,$_SESSION['username']);
			if(isset($user[2]) && $user[2] >= 2)
			{
				//Begin submission
				$infos=array(false,false,false,false,false,false,false,false,false,false,false,false,false,false,false,false,false,false,false,false);
				$infos[16]=preg_replace("/[^0-9]/","",$_POST['s']);
				if(isset($_POST['name']))
				{
					$infos[0]=filter_var($_POST['name'],FILTER_SANITIZE_STRING);
				}
				if(isset($_POST['owner']) && count(get_user($db,preg_replace("/[^A-Za-z0-9]/","",$_POST['owner']))) == 5)
				{
					$infos[1]=preg_replace("/[^A-Za-z0-9]/","",$_POST['owner']);
				}
				else
				{
					$infos[1]=$_SESSION['username'];
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
				if(isset($_POST['shour']))
				{
					$infos[5]=preg_replace("/[^0-9]/","",$_POST['shour']);
				}
				if(isset($_POST['sminute']))
				{
					$infos[6]=preg_replace("/[^0-9]/","",$_POST['sminute']);
				}
				if(isset($_POST['smerid']))
				{
					switch($_POST['smerid'])
					{
						case "PM":
						$infos[7]="PM";
						break;
						case "AM":
						default:
						$infos[7]="AM";
						break;
					}
				}
				if(isset($_POST['allday']))
				{
					switch($_POST['allday'])
					{
						case "yes":
						$infos[8]=1;
						break;
						case "no":
						default:
						$infos[8]=0;
						break;
					}
				}
				if($infos[8] != 1)
				{
					if(isset($_POST['dmod']))
					{
						switch($_POST['dmod'])
						{
							case "min":
							$infos[9]=60;
							break;
							case "day":
							$infos[9]=24*60*60;
							break;
							case "hr":
							default:
							$infos[9]=60*60;
							break;
						}
					}
					else
					{
						$infos[9]=60*60;
					}
					if(isset($_POST['duration']) && is_numeric($_POST['duration']))
					{
						$infos[10]=$_POST['duration'];
					}
				}
				if(isset($_POST['public']))
				{
					switch($_POST['public'])
					{
						case "yes":
						$infos[12]=1;
						break;
						case "no":
						default:
						$infos[12]=0;
						break;
					}
				}
				if(isset($_POST['participants']))
				{
					$participants_raw=explode(",",str_replace(" ","",$_POST['participants']));
					$participants=array();
					foreach($participants_raw as $p)
					{
						if(count(get_user($db,preg_replace("/[^A-Za-z0-9]/","",$p))) == 5)
						{
							$participants[]=preg_replace("/[^A-Za-z0-9]/","",$p);
						}
					}
					$infos[13]=implode(",",$participants);
				}
				if(isset($_POST['location']))
				{
					$infos[14]=strip_tags(filter_var($_POST['location'],FILTER_SANITIZE_URL));
				}
				if(isset($_POST['description']))
				{
					$infos[15]=filter_var($_POST['description'],FILTER_SANITIZE_STRING);
				}
				if(isset($_POST['repeat']) && $_POST['repeat'] == "yes")
				{
					if(isset($_POST['frequency']))
					{
						$infos[11]=preg_replace("/[^0-9]/","",$_POST['frequency']);
					}
					if(isset($_POST['rmonth']))
					{
						$infos[17]=preg_replace("/[^A-Za-z0-9]/","",$_POST['rmonth']);
					}
					if(isset($_POST['rday']))
					{
						$infos[18]=preg_replace("/[^0-9]/","",$_POST['rday']);
					}
					if(isset($_POST['ryear']))
					{
						$infos[19]=preg_replace("/[^0-9]/","",$_POST['ryear']);
					}
				}
				
				for($i=0;$i<count($infos);$i++)
				{
					if($infos[$i] === "")
					{
						$infos[$i]=false;
					}
				}
				
				if(check_if_valid($infos[0],$infos[2],$infos[3],$infos[4],$infos[5],$infos[6],$infos[7],$infos[8],$infos[10],$infos[9],$infos[12]) === true && $infos[1] !== false && $infos[16] !== false && check_valid_repeat($infos[11],$infos[17],$infos[18],$infos[19]) !== false)
				{
					$stime=strtotime($infos[2] . " " . $infos[3] . ", " . $infos[4] . " " . $infos[5] . ":" . $infos[6] . " " . $infos[7]);
					if($infos[11] !== false)
					{
						$repeat=$infos[11] . "," . strtotime($infos[17] . " " . $infos[18] . " " . $infos[19]);
					}
					else
					{
						$repeat="";
					}
					if($infos[8] != 1)
					{
						$etime=$stime+($infos[10]*$infos[9]);
					}
					else
					{
						$etime=0;
					}
					$private=0;
					if($infos[12] == 0)
					{
						$private=1;
					}
					$debug=update_event($db,$infos[16],$infos[0],$infos[1],$infos[14],$stime,$etime,$infos[8],$infos[15],$infos[13],$private,$repeat);
					if($debug === true)
					{
						echo ("<script type=\"text/javascript\">window.location = \"index.php?ede=yes\"</script>");
					}
					else
					{
						echo ("<script type=\"text/javascript\">window.location = \"index.php?ede=no\"</script>");
					}
				}
				else
				{
					trigger_error("Check your details. You probably missed something very important.",E_USER_WARNING);
					$users=get_all_users($db);
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
  ?>
  </p>
  <h1>Low End Calendar-Edit Event</h1>
  <form method="post" action="editevent.php">
  <input type="hidden" name="s" value="<?php if(isset($id)) { echo $id; } else { echo -1; } ?>">
  Name: <input type="text" name="name" value="<?php if(isset($_POST['name'])) { echo stripcslashes($_POST['name']); } elseif(isset($event[0])) { echo stripcslashes($event[0]); } ?>"><br>
  Owner: <select name="owner" <?php if(!isset($user[2]) || (isset($user[2]) && $user[2] < 3)) { echo "disabled=\"disabled\""; } ?>>
  <?php
	if(count($users) > 0)
	{
		foreach($users as $userdata)
		{
			if(isset($user[0]) && $user[0] != "" && isset($user[1]) && $user[1] != "")
			{
				echo ("<option value=\"" . $userdata[0] . "\" ");
				$sel=false;
				if(isset($_POST['owner']) && $_POST['owner'] == $userdata[0])
				{
					echo "selected=\"selected\"";
				}
				if(isset($event) && $event[1] == $userdata[0] && !isset($_POST['owner']))
				{
					echo "selected=\"selected\"";
				}
				echo (">" . $userdata[0] . " (" . $userdata[1] . ")</option>\r\n");
			}
		}
	}
  ?>
  </select><br>
  Date: <input maxlength="9" size="9" type="text" name="month" value="<?php if(isset($_POST['month'])) { echo $_POST['month']; } elseif(isset($month)) { echo $month; } else { echo date("F"); } ?>"><input maxlength="2" size="2" type="text" name="day" value="<?php if(isset($_POST['day'])) { echo $_POST['day']; } elseif(isset($day)) { echo $day; } else { echo date("j"); } ?>"><input maxlength="4" size="4" type="text" name="year" value="<?php if(isset($_POST['year'])) { echo $_POST['year']; } elseif(isset($year)) { echo $year; } else { echo date("Y"); } ?>"> (month MUST be in text format!)<br>
  Start time: <input maxlength="2" size="2" type="text" name="shour" value="<?php if(isset($_POST['shour'])) { echo $_POST['shour']; } elseif(isset($shour)) { echo $shour; } else { echo date("g"); } ?>"><input maxlength="2" size="2" type="text" name="sminute" value="<?php if(isset($_POST['sminute'])) { echo $_POST['sminute']; } elseif(isset($sminute)) { echo $sminute; } else { echo date("i"); } ?>"><select name="smerid">
  <option value="AM" <?php if(isset($_POST['smerid']) && $_POST['smerid'] == "AM") { echo "selected=\"selected\""; } elseif(isset($smerid) && $smerid == "AM") { echo "selected=\"selected\""; } elseif(date("A") == "AM") { echo "selected=\"selected\""; } ?>>AM</option>
  <option value="PM" <?php if(isset($_POST['smerid']) && $_POST['smerid'] == "PM") { echo "selected=\"selected\""; } elseif(isset($smerid) && $smerid == "PM") { echo "selected=\"selected\""; } elseif(date("A") == "PM") { echo "selected=\"selected\""; } ?>>PM</option>
  </select><br>
  Duration: <input type="checkbox" name="allday" value="yes" <?php if(isset($_POST['allday']) && $_POST['allday'] == "yes") { echo "checked=\"checked\""; } elseif(isset($event[4]) && $event[4] == 1) { echo "checked=\"checked\""; } ?>> All day or <input type="text" size="4" name="duration" value="<?php if(isset($_POST['duration'])) { echo $_POST['duration']; } elseif(isset($duration)) { echo $duration; } ?>"><select name="dmod">
  <option value="min" <?php if(isset($_POST['dmod']) && $_POST['dmod'] == "min") { echo "selected=\"selected\""; } ?>>Minutes</option>
  <option value="hr" <?php if(isset($_POST['dmod']) && $_POST['dmod'] == "hr") { echo "selected=\"selected\""; } ?>>Hours</option>
  <option value="day" <?php if(isset($_POST['dmod']) && $_POST['dmod'] == "day") { echo "selected=\"selected\""; } ?>>Days</option>
  </select><br>
  <input type="checkbox" name="repeat" value="yes" <?php if(isset($_POST['repeat']) && $_POST['repeat'] == "yes") { echo "checked=\"checked\""; } elseif(isset($repeat) && $repeat == "yes") { echo "checked=\"checked\""; } ?>> Repeat every <input type="text" name="frequency" value="<?php if(isset($_POST['frequency'])) { echo $_POST['frequency']; } elseif(isset($frequency)) { echo $frequency; } else { echo "1"; } ?>"> week(s) until <input maxlength="9" size="9" type="text" name="rmonth" value="<?php if(isset($_POST['rmonth'])) { echo $_POST['rmonth']; } elseif(isset($rmonth)) { echo $rmonth; } else { echo date("F"); } ?>"><input maxlength="2" size="2" type="text" name="rday" value="<?php if(isset($_POST['rday'])) { echo $_POST['rday']; } elseif(isset($rday)) { echo $rday; } else { echo date("j"); } ?>"><input maxlength="4" size="4" type="text" name="ryear" value="<?php if(isset($_POST['ryear'])) { echo $_POST['ryear']; } elseif(isset($ryear)) { echo $ryear; } else { echo date("Y"); } ?>"> (month MUST be in text format!)<br>
  Display publicly: <input type="radio" name="public" value="yes" <?php if(isset($_POST['public']) && $_POST['public'] == "yes") { echo "checked=\"checked\""; } elseif(isset($event) && $event[7] == 0) { echo "checked=\"checked\""; } ?>>Yes | <input type="radio" name="public" value="no" <?php if((!isset($_POST['public']) && !isset($event)) || (isset($event) && $event[7] == 1)) { echo "checked=\"checked\""; } elseif((!isset($_POST['public']) && !isset($event)) || (isset($_POST['public']) && $_POST['public'] != "yes")) { echo "checked=\"checked\""; } ?>>No<br>
  Participants: <input type="text" name="participants" value="<?php if(isset($_POST['participants'])) { echo $_POST['participants']; } elseif(isset($event[6])) { echo stripcslashes($event[6]); } ?>"><br>
  You can choose from the following list:<br>
  <?php
	if(count($users) > 0)
	{
		foreach($users as $user)
		{
			if(isset($user[0]) && $user[0] != "" && isset($user[1]) && $user[1] != "")
			{
				if(!isset($_SESSION['username']) || (isset($_SESSION['username']) && $_SESSION['username'] != $user[0]))
				{
					echo ("-" . $user[0] . " (" . $user[1] . ")<br>\r\n");
				}
			}
		}
	}
  ?>
  List must be comma separated usernames and can contain no spaces! A sample format would be: auser,xuser,zuser.<br>
  Location: <input type="text" name="location" value="<?php if(isset($_POST['location'])) { echo $_POST['location']; } elseif(isset($event[8])) { echo stripcslashes($event[8]); } ?>"> (Usually a URL, spaces will be removed)<br>
  Description:<br>
  <textarea rows="10" cols="50" name="description"><?php if(isset($_POST['description'])) { echo stripcslashes($_POST['description']); } elseif(isset($event[5])) { echo stripcslashes($event[5]); } ?></textarea><br>
  <input type="submit" value="Change event"> or <input type="button" value="Cancel" onclick="window.location.href='index.php'">
  </form>
  </body>
</html>