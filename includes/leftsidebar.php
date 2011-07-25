<?php
/*
Copyright (C)  41a240b48fb7c10c68ae4820ac54c0f32a214056bfcfe1c2e7ab4d3fb53187a0 Name Year (sha256)

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

Note From Author: Please donate at the following address: 1Fc2ScswXAHPUgj3qzmbRmwWJSLL2yv8Q
*/

if(!$cookieValid){
//No valid cookie show login//
?>
<!--Login Input Field-->
<div id="leftsidebar">
	<form action="/login.php" method="post" id="loginForm">
		Login:<br>
		<input type="text" name="username" onclick="this.value='';" onfocus="this.select()" onblur="this.value=!this.value?'Username':this.value;" value="username" /><br/>
		<input type="password" name="password" onclick="this.value='';" onfocus="this.select()" onblur="this.value=!this.value?'password':this.value;" value="password" />
		<input type="submit" value="LOGIN">
	</form><br/>
		<a class="fancy_button top_spacing" href="lostpassword.php">
		  <span style="background-color: #070;">Lost Password</span>
		</a>
</div>
<?php
}else 	if($cookieValid){
//Valid cookie YES! Show this user stats//
?>
<div id="leftsidebar">
	<span>
		<?php
		echo "Welcome Back, <i><b>".$userInfo->username."</b></i><br/><hr size='1' width='100%' /><br/>";
		echo "<table><tr><td>Hashrate: </td><td><b><i><nobr>".$currentUserHashrate." MH/s</nobr></b></i></td></tr>";
		echo "<tr><td><nobr>Past Shares: </nobr></td><td><b><i>".$lifetimeUserShares."</b></i></td></tr>";
		echo "<tr><td><nobr>Past Invalid: </nobr></td><td><b><i>".$lifetimeUserInvalidShares."</b></i></td></tr>";
		echo "<tr><td><nobr>Current Shares: </nobr></td><td><b><i>".$totalUserShares."</i></b></td></tr>";
		echo "<tr><td><nobr>Round Shares: </nobr></td><td><b><i>".$totalOverallShares."</i></b></td></tr>";
		echo "<tr><td><nobr>Est. Earnings: </nobr></td><td><b><i><nobr>".sprintf("%.8f", $userRoundEstimate)."</i> BTC</nobr></b><br/></td></tr></table><br />";
		echo "<hr size='1' width='225'>";
		echo "Current Balance: <b><i>".$currentBalance." </i>BTC</b>";
		echo "<hr size='1' width='225'><br/>";
		echo "Last Updated: ";
		echo "".date("H:i:s", $settings->getsetting('statstime'))." WST+8";
		?>
		<br />
		<a class="fancy_button top_spacing" href="my_stats.php">
		  <span style="background-color: #070;">Stats</span>
		</a>
		<a class="fancy_button top_spacing" href="logout.php">
		  <span style="background-color: #070;">Logout</span>
		</a>
	</span>
</div>
<?php
}
?>