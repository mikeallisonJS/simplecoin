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
		<input type="text" name="username" value="username" id="userForm" onMouseDown="clearUsername();">
		<input type="password" name="password" value="password" id="passForm" onMouseDown="clearPassword();">
		<input type="submit" value="LOGIN">
	</form><br/>
	<form action="/login.php" method="post" login="lostForm" id="lostPassForm">
		<input type="submit" name="act" value="Lost Password">
	</form>
</div>
<?php
	}else 	if($cookieValid){
		//Valid cookie YES! Show this user stats//
?>
<div id="leftsidebar">
	<span>
		<?php
			echo "Welcome Back, <i><b>".$userInfo->username."</b></i><br/><hr size='1' width='100%' /><br/>";
			echo "Current Hashrate: <i><b>".$currentUserHashrate." MH/s</b></i><br/>";
			echo "Lifetime Shares: <i><b>".$lifetimeUserShares."</b></i><br/>";
			echo "Lifetime Invalid: <i><b>".$lifetimeUserInvalidShares."</b></i><br/>";
			echo "Valid This Round: <b><i>".$totalUserShares."</i> shares</b><br/>";
			echo "Round Shares: <b><i>".$totalOverallShares."</i> shares</b><br/>";
			echo "Est. Earnings: <b><i>".$userRoundEstimate."</i> BTC</b>";
			echo "<hr size='1' width='225'>";
			echo "Current Balance: <b><i>".$currentBalance." </i>BTC</b><br/>";
		?>		
		<i>(Updated every 10 minutes)</i><br/>
		<a href="logout.php" style="color: blue">Logout</a>
	</span>
</div>
<?php
	}
?>