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

//Include site functions
include("includes/requiredFunctions.php");

setcookie($cookieName, false);
?>
<html>
  <head>
	<title><?php echo antiXss(outputPageTitle());?> </title>
	<link rel="stylesheet" href="/css/mainstyle.css" type="text/css" />
	<meta http-equiv="refresh" content="2;url=/">
  </head>
  <body>
	<div id="pagecontent">
		<h1>You have been logged out<br/>
		<a href="/" style="color: blue">Click here if you continue to see this message</a></h1>
	</div>
  </body>
</html>