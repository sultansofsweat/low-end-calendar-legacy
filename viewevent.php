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
    <title>Low End Calendar-View Event</title>
    
  </head>
  <body>
  <?php
	include("functions.php");
	
	if(isset($_GET['id']))
	{
		$id=preg_replace("/[^0-9]/","",$_GET['id']);
		if($id != "" && $id > 0)
		{
			$db=open_db("db/calendar.sqlite",SQLITE3_OPEN_READONLY);
			if(isset($_SESSION['username']) && $_SESSION['username'] != "")
			{
				$user=get_user($db,$_SESSION['username']);
			}
			elseif(get_setting($db,"openviewing") == "yes")
			{
				$user=array("guest","Anonymous",1,"America/Toronto","default");
			}
			else
			{
				$user=array("guest","Anonymous",0,"America/Toronto","default");
			}
			if(isset($user[2]) && $user[2] < 1)
			{
				die("<script type=\"text/javascript\">window.location = \"index.php?bad=yes\"</script>");
			}
			$event=get_event($db,$id);
			if($event === false)
			{
				die("<script type=\"text/javascript\">window.location = \"index.php?ved=bad\"</script>");
			}
			//FORMAT: Name,Owner,Start,End,Allday,Description,Invitees,Private,Location,Created,Repeat
			if($event[7] == 1 && $user[2] < 3 && $event[1] != $user[0] && !in_array($user[0],explode(",",$event[6])))
			{
				die("<script type=\"text/javascript\">window.location = \"index.php?bad=yes\"</script>");
			}
			$name=$event[0];
			$odetails=get_user($db,$event[1]);
			$owner=$odetails[1];
			if($event[4] == 1)
			{
				$start=date("l F j, o",$event[2]);
				$end="This is an all-day event.";
			}
			else
			{
				$start=date("l F j, o",$event[2]) . " at " . date("g:i A",$event[2]);
				$end=date("l F j, o",$event[3]) . " at " . date("g:i A",$event[3]);
			}
			$description=$event[5];
			if(strpos($event[8],"http") !== false)
			{
				$location="<a href=\"" . $event[8] . "\">" . $event[8] . "</a>";
			}
			else
			{
				$location=$event[8];
			}
			if($event[7] == 1)
			{
				$visibility="Owner and participants only";
			}
			else
			{
				$visibility="Everyone";
			}
			$participants="";
			$plist=explode(",",$event[6]);
			if(count($plist) > 0 && !(count($plist) == 1 && $plist[0] == ""))
			{
				foreach($plist as $p)
				{
					$pinfo=get_user($db,$p);
					$participants.=$pinfo[1] . ", ";
				}
				$participants=substr($participants,0,-2);
			}
			else
			{
				$participants="No one else";
			}
			if($event[9] > 0)
			{
				$created=date("l F j, o",$event[9]) . " at " . date("g:i A",$event[9]);
			}
			else
			{
				$created=$start;
			}
			if($event[10] != "")
			{
				$rinfo=explode(",",$event[10]);
				$repeat="Every " . $rinfo[0] . " week(s) until " . date("F j, o",$rinfo[1]);
			}
			else
			{
				$repeat="Never";
			}
			$debug=close_db($db);
			if($debug === false)
			{
				trigger_error("The server has caused a criticality accident and the database became irradiated.",E_USER_WARNING);
			}
		}
		else
		{
			die ("<script type=\"text/javascript\">window.location = \"index.php?ved=bad\"</script>");
		}
	}
	else
	{
		die ("<script type=\"text/javascript\">window.location = \"index.php?ved=bad\"</script>");
	}
  ?>
  </p>
  <h1>View Event: <?php echo stripcslashes($name); ?></h1>
  <p><b>Event created</b>: <?php echo $created; ?> by <?php echo $owner; ?><br>
  <b>Event is visible to</b>: <?php echo $visibility; ?><br>
  <b>Participants</b>: <?php echo $participants; ?><br>
  <b>Event starts</b>: <?php echo $start; ?><br>
  <b>Event ends</b>: <?php echo $end; ?><br>
  <b>Event repeats</b>: <?php echo $repeat; ?><br>
  <b>Event location</b>: <?php echo $location; ?><br>
  <b><u>More details</u></b>:<br>
  <?php echo stripcslashes($description); ?></p>
  <hr>
  <p>
  <?php
	if($user[2] >= 3 || $event[1] == $user[0])
	{
		echo ("<a href=\"editevent.php?id=$id\">Edit this event</a><br>\r\n
		<a href=\"delevent.php?id=$id\">Delete this event</a><br>\r\n");
	}
  ?>
  <a href="viewlist.php">Go back to event list</a><br>
  <a href="index.php">Go back to main page</a></p>
  </body>
</html>