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

$pageTitle = "- Getting Started";
include ("includes/header.php");
	
?>

<b><u>Get a Bitcoin Address</u></b><br/>
Sign-up for an online wallet. For example: <a href="https://www.mybitcoin.com/" target="_blank"  style="color: blue">mybitcoin.com</a><br/>
Or download the client to your PC from: <a href="http://www.bitcoin.org//" target="_blank"  style="color: blue">bitcoin.org</a><br/><br/>

<b><u>Setup a bitcoin miner</u></b><br/><br/>

<b><u>ATI/AMD Users</u></b><br/>
You will need to download and install the AMD Stream SDK from  <a href="http://developer.amd.com/SDKS/AMDAPPSDK/DOWNLOADS/Pages/default.aspx" target="_blank" style="color: blue">HERE</a> before you can begin mining<br/><br/> 

	<b><u>Windows</u></b><br/>
	Download GUIMiner: <a href="https://forum.bitcoin.org/index.php?topic=3878.0" target="_blank">HERE</a><br>
	<img src="/images/guiminer.jpg"><br/>
	<u>GUIMiner options</u><br/>
	Server: Other<br />
	Host: simplecoin.us<br />
	Port: 8337<br />
	Username: &lt;your user name&gt;.&lt;miner name (default is 1)&gt;<br/>
	Password: &lt;your miner password (default is 'x')&gt;<br/>
	Device: Select the graphics card/cpu you would like to use<br/>
	Extra Flags: Can be blank, but I find "-v -w128 -f 60" to work well<br/><br/>
	
	<b><u>Linux</u></b><br/>
	Download hashkill: <a href="https://forum.bitcoin.org/index.php?topic=6819.0" target="_blank">HERE</a><br/>
	Decompress (tar zxvf hashkill-0.2.4-x86.tar.gz)<br/>
	Change directory to hashkill<br/>
	Run: sudo ./install.sh<br/>
	Run hashkill: hashkill-gpu -p bitcoin minername:password:simplecoin.us:8337
	
	
<?php include("includes/footer.php"); ?>

