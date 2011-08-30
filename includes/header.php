<?php
//    Copyright (C) 2011  Mike Allison <dj.mikeallison@gmail.com>
//
//    This program is free software: you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 3 of the License, or
//    (at your option) any later version.
//
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    You should have received a copy of the GNU General Public License
//    along with this program.  If not, see <http://www.gnu.org/licenses/>.

// 	  BTC Donations: 163Pv9cUDJTNUbadV4HMRQSSj3ipwLURRc

//Set page starter variables//
$cookieValid	= false;
$activeMiners = false;

include("requiredFunctions.php");

include('includes/stats.php');
$stats = new Stats();

include("universalChecklogin.php");

if (!isset($pageTitle)) $pageTitle = outputPageTitle();
else $pageTitle = outputPageTitle(). " ". $pageTitle;

?>
<!DOCTYPE unspecified PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title><?php echo $pageTitle;?></title>
		<!--This is the main style sheet-->
		<link rel="stylesheet" href="css/mainstyle.css" type="text/css" />
		<script type="text/javascript" src="/js/EnhanceJS/enhance.js"></script>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js" type="text/javascript"></script>	
		<script type="text/javascript">
			// Run capabilities test
			enhance({
				loadScripts: [
					'/js/excanvas.js',
					'/js/visualize.jQuery.js',
					'/js/ozcoin_graphs.js'
				],
				loadStyles: [
					'/css/visualize.css',
					'/css/visualize-light.css'
				]
			});
    	</script>
		<link rel="shortcut icon" href="/images/favicon.png" />
		<?php
			//If user isn't logged in load the login.js
			if(!$cookieValid){
		?>
			<script src="/js/login.js"></script>
		<?php
			}
		?>
	</head>
	<body>
		<div id="header">
			<div id="logo">
				<table width="100%" cellspacing="0" cellpadding="0" border="0">
					<tr>
					<td rowspan="2"><img src="images/logo.jpg"></td>
					<td valign="top"><?php include ("menu.php"); ?></td>
					</tr>
					<tr>
					<td align="left" valign="bottom" id="currentRates">
					</td>
				</tr>
			</table>
			</div>
		</div>		
		<div style="width:100%; background-color:#172322; text-align:left;color: #FFF; padding-left: 1.5em">	
			<table cellspacing="0" border="0" cellpadding="0" width="100%" style="width:100%; background-color:#172322; text-align:left;color: #FFF; left-margin: 5px"></body>
				<tr>
					<td>Pool</td>
					<td>Hashrate: <?php print round($stats->currenthashrate()/1000,1); ?> GH/s</td>
					<td>Workers: <?php print $stats->currentworkers(); ?></td>
					<?php if ($cookieValid) {	?>
					<td>Round Shares: <?php echo $stats->currentshares();?></td>
					<?php } ?>
					<td>Server Load: <?php print $stats->get_server_load(); ?></td>
					<td><a href="http://www.mtgox.com" target="_blank" style="color: #FFF">MtGox (USD):</a> $<?php print $stats->mtgoxlast(); ?></td>					
				</tr>
				<?php if ($cookieValid) {	?>				
				<tr>					
					<td><?php echo $userInfo->username; ?> <a href="/logout.php" style="color: #FFF"><span style="font-size:small">(logout)</span></a></td>
					<td>Hashrate: <?php print $stats->userhashrate($userInfo->username); ?> MH/s</td>
					<td>Workers: <?php echo count($stats->workers($userInfo->id)); ?></td>
					<td>Round Shares: <?php echo $totalUserShares; ?> (<?php echo round(($stats->userstalecount($userId) / $totalUserShares * 100),1); ?>% stale)</td>
					<td>Estimate: <?php echo sprintf("%.8f", $userRoundEstimate); ?> BTC</td>
					<td>Balance: <?php echo $currentBalance; ?> BTC</td>					
				</tr>
				<?php } else { ?>
				<form action="/login.php" method="post" id="loginForm">
				<tr>													
					<td colspan="5">Login: 
					<input type="text" name="username" onclick="this.value='';" onfocus="this.select()" onblur="this.value=!this.value?'Username':this.value;" value="username" /> 
					<input type="password" name="password" onclick="this.value='';" onfocus="this.select()" onblur="this.value=!this.value?'password':this.value;" value="password" />
					<input type="submit" value="LOGIN">
					<a href="/lostpassword.php" style="color: #FFF"><span style="font-size: small">Lost Password</span></a>
					</td>	
				</tr>
				</form>			
				<?php } ?>
			</table>
		</div>		
		

		
		<div id="content">
