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
    <title>Low End Calendar-Log Out</title>
    
  </head>
  <body>
  <p>
  <?php
	$disable=false;
	if(isset($_POST['s']) && $_POST['s'] == "y")
	{
		//Begin submission
		unset($_SESSION['username']);
		unset($_SESSION["name"]);
		unset($_SESSION["style"]);
		unset($_SESSION["timezone"]);
		//$debug=session_destroy();
		if(!isset($_SESSION['username']) && !isset($_SESSION["name"]) && !isset($_SESSION["style"]) && !isset($_SESSION["timezone"]))
		{
			echo ("<script type=\"text/javascript\">window.location = \"index.php?out=yes\"</script>");
		}
		else
		{
			echo ("<script type=\"text/javascript\">window.location = \"index.php?out=no\"</script>");
		}
	}
	else
	{
		//Check if already logged out
		if(!isset($_SESSION['username']) || $_SESSION['username'] == "")
		{
			trigger_error("What are you doing here? You are already logged out! Get out before a GPX clock radio hits you!");
			$disable=true;
		}
	}
  ?>
  </p>
  <h1>Low End Calendar-Log Out</h1>
  <form method="post" action="logout.php">
  <input type="hidden" name="s" value="y">
  Are you sure you want to log out?<br>
  <input type="submit" value="Yes" <?php if($disable === true) { echo ("disabled=\"disabled\""); } ?>> or <input type="button" value="No" onclick="window.location.href='index.php'">
  </form>
  </body>
</html>