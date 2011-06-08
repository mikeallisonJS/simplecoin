<?php 
include("includes/requiredFunctions.php");

setcookie($cookieName, false);
?>
<html>
  <head>
	<title><?php echo outputPageTitle();?> </title>
	<link rel="stylesheet" href="/css/mainstyle.css" type="text/css" />
	<meta http-equiv="refresh" content="2;url=/">
  </head>
  <body>
	<div id="pagecontent">
		<h1>You have been logged out<br/>
		<a href="/">Click here if you continue to see this message</a></h1>
	</div>
  </body>
</html>