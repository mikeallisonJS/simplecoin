<?php
/*
Copyright (C) Copyright (C) 41a240b48fb7c10c68ae4820ac54c0f32a214056bfcfe1c2e7ab4d3fb53187a0 Name Year (sha256)

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
Website Reference:http://www.gnu.org/licenses/gpl-2.0.html

*/

//RPC Bitcoind Client Information
$rpcType = "http"; // http or https
$rpcUsername = "pool"; // username
$rpcPassword = "pass"; // password
$rpcHost = "localhost";


//Login to Mysql with the following
$dbHost = "localhost";
$dbUsername = "pushpool";
$dbPassword = "pass";
$dbPort = "3306";
$dbDatabasename = "simplecoin";

//Cookie settings | More Info @ http://us.php.net/manual/en/function.setcookie.php
$cookieName = "simplecoinus"; //Set this to what ever you want "Cheesin?"
$cookiePath = "/";	//Choose your path!
$cookieDomain = ""; //Set this to your domain

include("bitcoinController/bitcoin.inc.php");

//Encrypt settings
$salt = "123483jd7Dg6h5s92k"; //Just type a random series of numbers and letters; set it to anything or any length you want. "You can never have enough salt."
$cookieValid = false; //Don't touch leave as: false

connectToDb();
include('settings.php');

$settings = new Settings();

/////////////////////////////////////////////////////////////////////NO NEED TO MESS WITH THE FOLLOWING | FOR DEVELOPERS ONLY///////////////////////////////////////////////////////////////////
function connectToDb(){
	//Set variables to global retireve outside of the scope
	global $dbHost, $dbUsername, $dbPassword, $dbDatabasename;
	
	//Connect to database
	mysql_connect($dbHost, $dbUsername, $dbPassword)or die(mysql_error());
	mysql_select_db($dbDatabasename);
}

class checkLogin
{
	function checkCookie($input, $ipaddress){	
		global $salt;		
		connectToDb();		
		/*$input comes in the following format userId-passwordhash
		
		/*Validate that the cookie hash meets the following criteria:
			Cookie Ip: matches $ipaddres;
			Cookie Timeout: Is still greater then the current time();
			Cookie Secret: matches the mysql database secret;
		*/
			
		//Split cookie into 2 mmmmm!
		$cookieInfo = explode("-", $input);
		
		$validCookie = false;
		
		//Get "secret" from MySql database
		$tempId = mysql_real_escape_string($cookieInfo[0]);
		if (!is_numeric($tempId)) {
			$tempId = 0;	
			return false;
		}
		$getSecretQ	= mysql_query("SELECT secret, pass, sessionTimeoutStamp FROM webUsers WHERE id = $tempId LIMIT 0,1");
		if ($getSecret = mysql_fetch_object($getSecretQ)) {
			$password	= $getSecret->pass;
			$secret	= $getSecret->secret;
			$timeoutStamp	= $getSecret->sessionTimeoutStamp;
			
			//Create a variable to test the cookie hash against
			$hashTest = hash("sha256", $secret.$password.$ipaddress.$timeoutStamp.$salt);
			
			//Test if $hashTest = $cookieInfo[1] hash value; return results
			if($hashTest == $cookieInfo[1]){		
				$validCookie = true;
			}				
		}
		return $validCookie;
	}
	
	function returnUserId($input){
		//Just split the cookie to get the userId
		$cookieInfo = explode("-", $input);
			
		return $cookieInfo[0];			
	}
}



function outputPageTitle(){
	if (!isset($settings))
	{
		connectToDb();	
		$settings = new Settings();
	}
	//Get page title
	return $settings->getsetting("pagetitle");;
}

function outputHeaderTitle(){	
	if (!isset($settings))
	{
		connectToDb();	
		$settings = new Settings();
	}
	return $settings->getsetting("websitename");
}

//Helpfull functions
function genRandomString($length=10) {
    $characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $string = "";    

    for ($p = 0; $p < $length; $p++) {
        $string .= $characters[mt_rand(0, strlen($characters) - 1)];
    }

    return $string;
}


function antiXss($input) {
	//strip HTML tags from input data
	return htmlentities(strip_tags($input), ENT_QUOTES);
}
?>
