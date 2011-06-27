<div id="menuBar">
	<div class="menuBtn">
		<a href="/index.php" class="menu">Home</a>
	</div>
	<?php
		if(!$cookieValid){
		//Display this menu if the user isn't logged in
	?>
	<div class="menuBtn">
		<a href="/register.php" class="menu">Register</a>
	</div>
	<?php
	} else if($cookieValid){
	?>
	<div class="menuBtn">
		<a href="/accountdetails.php" class="menu">Account Details</a>
	</div>
	<div class="menuBtn">
		<a href="/my_stats.php" class="menu">My Stats</a>
	</div>
	<?php
	//If this user is an admin show the adminPanel.php link
	if($isAdmin){
	?>
	<div class="menuBtn">
		<a href="/adminPanel.php" class="menu">(Admin Panel)</a>
	</div>
	<?php
		}
	}
	?>
	<div class="menuBtn">
		<a href="/stats.php" class="menu">Pool Stats</a>
	</div>

	<div class="menuBtn">
		<a href="/gettingstarted.php" class="menu">Getting Started</a>
	</div>
	<div class="menuBtn">
		<a href="http://forum.bitcoin.org/index.php?topic=11186.0.php" target="_blank" class="menu">Forum</a>
	</div>
	<div class="menuBtn">
		<a href="about.php" class="menu">About</a>
	</div>
</div>