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
$rpcPort = 8332;


//Login to Mysql with the following
$dbHost = "localhost";
$dbUsername = "pushpool";
$dbPassword = "pass";
$dbPort = "3306";
$dbDatabasename = "simplecoin";

//Replicated Database calls for read intensive queries (set to above if only 1 database)
$readOnlyDbHost = "1.1.1.1";
$readOnlyDbUsername = "pushpool";
$readOnlyDbPassword = "pass";
$readOnlyDbPort = "3306";
$readOnlyDbName = "simplecoin";

//Cookie settings | More Info @ http://us.php.net/manual/en/function.setcookie.php
$cookieName = "simplecoinus"; //Set this to what ever you want "Cheesin?"
$cookiePath = "/";	//Choose your path!
$cookieDomain = ""; //Set this to your domain

//Number of bonus coins to award
$bonusCoins = 50;

//Include bitcoind controller
include("bitcoinController/bitcoin.inc.php");

//Setup Memcached 
global $memcache;
$memcache = new Memcached();
$memcache->addServer("localhost",11212);

//Encrypt settings
$salt = "123483jd7Dg6h5s92k"; //Just type a random series of numbers and letters; set it to anything or any length you want. "You can never have enough salt."

/////////////////////////////////////////////////////////////////////NO NEED TO MESS WITH THE FOLLOWING | FOR DEVELOPERS ONLY///////////////////////////////////////////////////////////////////

$cookieValid = false; //Don't touch leave as: false

//Connect to Main Db
connectToDb();

//New PDO connection for readaccess (fallback to local if unavailable)
try {
	$read_only_db = new PDO('mysql:dbname='.$readOnlyDbName.';host='.$readOnlyDbHost.';port='.$readOnlyDbPort, $readOnlyDbUsername, $readOnlyDbPassword);
} catch (Exception $e) {
	$read_only_db = new PDO('mysql:dbname='.$dbDatabasename.';host='.$dbHost.';port='.$dbPort, $dbUsername, $dbPassword);
}

include('settings.php');
$settings = new Settings();

//Open a bitcoind connection	
$bitcoinController = new BitcoinClient($rpcType, $rpcUsername, $rpcPassword, $rpcHost, $rpcPort);

//setup bitcoinDifficulty cache object
$bitcoinDifficulty = GetCachedBitcoinDifficulty();

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

function sqlerr($file = '', $line = '')
{
  print("<table border=0 bgcolor=blue align=left cellspacing=0 cellpadding=10 style='background: blue'>" .
    "<tr><td class=embedded><font color=white><h1>SQL Error</h1>\n" .
  "<b>" . mysql_error() . ($file != '' && $line != '' ? "<p>in $file, line $line</p>" : "") . "</b></font></td></tr></table>");
  die;
}

$_current_lock = null;

function islocked($name) {
	$result = mysql_query("SELECT locked FROM locks WHERE name ='$name' and locked=1 LIMIT 1");
	if (!$result || mysql_numrows($result) == 0)
		return false;
	return true;
}

function unlock() {
	global $_current_lock;
	mysql_query("UNLOCK TABLES");
	$sql = "UPDATE locks SET locked = 0 WHERE name = '" . mysql_real_escape_string($_current_lock) . "'";
	mysql_query($sql);
}

function lock($name) {
	global $_current_lock;
	mysql_query("LOCK TABLES locks WRITE");
	$q = mysql_query("SELECT locked FROM locks WHERE name = '" . mysql_real_escape_string($name) . "'");

	$lock = mysql_fetch_object($q);
	if ($lock === false) {
		mysql_query("INSERT INTO locks (name, locked) VALUES ('".mysql_real_escape_string($name)."', 1)");
	} elseif ($lock->locked) {
		echo("Lock already held, exiting. (".$name.")");
		mysql_query("UNLOCK TABLES");
		exit();
		return;
	} else {		
		mysql_query("UPDATE locks SET locked = 1 WHERE name = '" . mysql_real_escape_string($name) . "'");
	}
	
	//mysql_query("UNLOCK TABLES");
	$_current_lock = $name;
	register_shutdown_function('unlock');
}

function ScriptIsRunLocally() {
	if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] != "127.0.0.1") {
		echo "This script can only be run locally.";
		exit;
	}
}

//Cache functions

# Gets key / value pair into memcache ... called by mysql_query_cache()
function getCache($key) {
    global $memcache;
    return ($memcache) ? $memcache->get($key) : false;
}

# Puts key / value pair into memcache ... called by mysql_query_cache()
function setCache($key, $object, $timeout = 600) {
    global $memcache;
    return ($memcache) ? $memcache->set($key, $object, $timeout) : false;
}

function removeCache($key) {
	global $memcache;
	$memcache->delete($key);
}

function removeSqlCache($key) {
	global $memcache;
	$memcache->delete(md5("mysql_query".$key));
}

# Caching version of mysql_query()
function mysql_query_cache($sql, $timeout = 600) {	
	if($objResultset = unserialize(getCache(md5("mysql_query".$sql))))  {		
    	return $objResultset;
  	}
    $objResultSet = mysql_query($sql); 
    $objarray = Array();
    while ($row = mysql_fetch_object($objResultSet)) {
    	$objarray[] = $row;
    }   
    setCache(md5("mysql_query".$sql), serialize($objarray), $timeout);
    return $objarray;
}

function GetCachedBitcoinDifficulty() {
	global $bitcoinController;
	$difficulty = 0;
	if (!($difficulty = getCache("bitcoinDifficulty"))) {	
		$difficulty = $bitcoinController->query("getdifficulty");
		setCache("bitcoinDifficulty", $difficulty, 60);	
	}	
	return $difficulty;
}

?>
