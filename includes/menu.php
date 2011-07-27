<div id="menuBar">
	<img style="float:left;" alt="" src="/images/menu_left.png"/>
	<ul id="menu">
		<li><a href="/index.php">Home</a></li>
		<?php if (!$cookieValid) {	?>
		<li><a href="/register.php">Register</a></li>	
		<?php } else if ($cookieValid) { ?>
		<li><a href="/accountdetails.php">Account Details</a></li>
		<li><a href="/my_stats.php">My Stats</a></li>
		<?php if ($isAdmin) { ?>
		<li><a href="/adminPanel.php">(Admin Panel)</a></li>
		<?php }
		} ?>
		<li><a href="/stats.php">Pool Stats</a></li>
		<li><a href="/gettingstarted.php">Getting Started</a></li>
		<li><a href="/chat.php">Chat</a></li>
		<li><a href="http://simplecoin.lefora.com" target="_blank">Forum</a></li>
		<li><a href="about.php">About</a></li>
	</ul>
	<img style="float:left;" alt="" src="/images/menu_right.png"/>
</div>
<div style="float:none; clear:both;"></div>
