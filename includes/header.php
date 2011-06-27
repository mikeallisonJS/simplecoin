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
		<script type="text/javascript">
			// Run capabilities test
			enhance({
				loadScripts: [
					'js/excanvas.js',
					'js/jquery-1.6.1.min.js',
					'js/visualize.jQuery.js',
					'js/ozcoin_graphs.js'
				],
				loadStyles: [
					'css/visualize.css',
					'css/visualize-light.css'
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
					<td><img src="images/logo.jpg"></td>
					<td align="right" valign="top" id="currentRates">
						<table border="0" cellspacing="1">
						<tr><td style="color: #FFF; text-align: right"><a href="http://www.mtgox.com" target="_blank" style="color: #FFF">MtGox (USD):</a></td><td style="color: #FFF">$<?php print $settings->getsetting('mtgoxlast'); ?></td></tr>
						<tr><td style="color: #FFF; text-align: right">Current Hashrate:</td><td style="color: #FFF"><?php print round($settings->getsetting('currenthashrate')/1000,1); ?> GH/s</td></tr>
						<tr><td style="color: #FFF; text-align: right">Current Workers:</td><td style="color: #FFF"><?php print $settings->getsetting('currentworkers'); ?></td></tr>
						</table>
					</td>
				</tr>
			</table>
			</div>
		</div>
		<?php include ("menu.php"); ?>
		<?php include ("leftsidebar.php"); ?>
		<div id="content">
