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
    <title>Low End Calendar-View Support Tickets</title>
    
  </head>
  <body>
  <p>
  <?php
	include("functions.php");
	//Make sure user is signed in
	if(isset($_SESSION['username']) && $_SESSION['username'] != "")
	{
		$db=open_db("db/calendar.sqlite",SQLITE3_OPEN_READONLY);
		$user=get_user($db,$_SESSION['username']);
		if(!isset($user[2]))
		{
			die("<script type=\"text/javascript\">window.location = \"index.php?bad=yes\"</script>");
		}
		$raw_tickets=get_tickets($db);
		$tickets=array();
		switch($user[2])
		{
			case 3:
			case 4:
			$tickets=$raw_tickets;
			break;
			case 0:
			case 1:
			case 2:
			default:
			if(count($raw_tickets) > 0)
			{
				foreach($raw_tickets as $ticket)
				{
					if($ticket[1] == $user[0])
					{
						$tickets[]=$ticket;
					}
				}
			}
			break;
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
  ?>
  </p>
  <h1>Low End Calendar-View Tickets</h1>
  <table width="100%">
  <tr>
  <th>Submitted By</th>
  <th>Type</th>
  <th>Details</th>
  <th>Ticket Status</th>
  <th>Answered By</th>
  <th>Response</th>
  <th>Actions</th>
  </tr>
  <?php
	if(count($tickets) > 0)
	{
		$db=open_db("db/calendar.sqlite",SQLITE3_OPEN_READONLY);
		foreach($tickets as $ticket)
		{
			//FORMAT: id,submitter,responder,type,details,status,response
			$suser=get_user($db,$ticket[1]);
			$suser=$suser[1];
			if($ticket[2] != NULL && $ticket[2] >= 0)
			{
				$ruser=get_user($db,$ticket[2]);
				$ruser=$ruser[1];
			}
			else
			{
				$ruser="";
			}
			echo("<tr>\r\n");
			echo("<td>$suser</td>\r\n
			<td>" . stripcslashes($ticket[3]) . "</td>\r\n
			<td>" . stripcslashes($ticket[4]) . "</td>\r\n
			<td>");
			switch($ticket[5])
			{
				case 0:
				echo("Unseen");
				break;
				case 1:
				echo("Active");
				break;
				case 2:
				echo("Closed");
				break;
				case 3:
				echo("Resolved");
				break;
				default:
				echo("<b>G P X!!!</b>");
				break;
			}
			echo("</td>\r\n
			<td>$ruser</td>\r\n
			<td>" . stripcslashes($ticket[6]) . "</td>\r\n
			<td><a href=\"delticket.php?id=" . $ticket[0] . "\">Delete ticket</a>\r\n");
			switch($ticket[5])
			{
				case 0:
				echo("<br><a href=\"closeticket.php?id=" . $ticket[0] . "\">Close ticket</a>\r\n");
				if($user[2] >= 3)
				{
					echo("<br><a href=\"activeticket.php?id=" . $ticket[0] . "\">Mark active</a>\r\n
					<br><a href=\"resolveticket.php?id=" . $ticket[0] . "\">Resolve ticket</a>\r\n");
				}
				break;
				case 1:
				echo("<br><a href=\"closeticket.php?id=" . $ticket[0] . "\">Close ticket</a>\r\n");
				if($user[2] >= 3)
				{
					echo("<br><a href=\"resolveticket.php?id=" . $ticket[0] . "\">Resolve ticket</a>\r\n");
				}
				break;
				case 2:
				case 3:
				break;
				default:
				echo("Microwavez");
				break;
			}
			echo("</td>\r\n");
			echo("</tr>\r\n");
		}
		$debug=close_db($db);
		if($debug === false)
		{
			trigger_error("The server has caused a criticality accident and the database became irradiated.",E_USER_WARNING);
		}
	}
  ?>
  </table>
  <p><a href="index.php">Go back</a></p>
  </body>
</html>