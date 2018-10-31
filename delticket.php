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
    <title>Low End Calendar-Delete Ticket</title>
    
  </head>
  <body>
  <p>
  <?php
	
	if(isset($_POST['s']) && $_POST['s'] == "y" && isset($_POST['id']) && isset($_SESSION['username']) && $_SESSION['username'] != "")
	{
		$id=preg_replace("/[^0-9]/","",$_POST['id']);
		if($id != "" && $id > 0)
		{
			$db=open_db("db/calendar.sqlite",SQLITE3_OPEN_READWRITE);
			$debug=delete_ticket($db,$id);
			if($debug === true)
			{
				echo ("<script type=\"text/javascript\">window.location = \"index.php?dtk=yes\"</script>");
			}
			else
			{
				echo ("<script type=\"text/javascript\">window.location = \"index.php?dtk=no\"</script>");
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
	elseif(isset($_GET['id']) && isset($_SESSION['username']) && $_SESSION['username'] != "")
	{
		$id=preg_replace("/[^0-9]/","",$_GET['id']);
	}
	else
	{
		//Automatically deny access
		die ("<script type=\"text/javascript\">window.location = \"index.php?bad=yes\"</script>");
	}
  ?>
  </p>
  <h1>Low End Calendar-Delete Ticket</h1>
  <form method="post" action="delticket.php">
  <input type="hidden" name="s" value="y">
  <input type="hidden" name="id" value="<?php echo $id; ?>">
  Are you sure you want to delete this ticket?<br>
  <input type="submit" value="Yes, dump it"> or <input type="button" value="Cancel" onclick="window.location.href='index.php'">
  </form>
  </body>
</html>