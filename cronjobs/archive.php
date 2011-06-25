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
  
//Check that script is run locally
$ip = $_SERVER['REMOTE_ADDR'];
if ($ip != "127.0.0.1") {
	echo "cronjobs can only be run locally.";
	exit;
}
 
//Set page starter variables//
$includeDirectory = "/var/www/includes/";

//Include hashing functions
include($includeDirectory."requiredFunctions.php");

//get counted shares by user id and move to shares_counted
$sql = "SELECT DISTINCT p.associatedUserId, blockNumber, sum(s.valid) as valid, IFNULL(sum(si.invalid),0) as invalid, max(maxId) as maxId FROM ". 
		"(SELECT DISTINCT username, max(blockNumber) as blockNumber, count(id) as valid, max(id) as maxId FROM shares_history WHERE counted='1' AND our_result='Y' GROUP BY username) s LEFT JOIN ".
		"(SELECT DISTINCT username, count(id) as invalid FROM shares_history WHERE counted='1' AND our_result='N' GROUP BY username) si ON s.username=si.username ". 
		"INNER JOIN pool_worker p ON p.username = s.username ".
		"GROUP BY associatedUserId";	
$sharesQ = mysql_query($sql);
$i = 0;
$maxId = 0;
$shareInputSql = "";

while ($sharesR = mysql_fetch_object($sharesQ)) {	
	if ($sharesR->maxId > $maxId)
		$maxId = $sharesR->maxId;
	if ($i == 0) {
		$shareInputSql = "INSERT INTO shares_counted (blockNumber, userId, count, invalid) VALUES ";
	}
	if ($i > 0) {
		$shareInputSql .= ",";
	}				
	$i++;
	$shareInputSql .= "(".$sharesR->blockNumber.",".$sharesR->associatedUserId.",".$sharesR->valid.",".$sharesR->invalid.")";
	if ($i > 20)
	{		
		mysql_query($shareInputSql);
		$shareInputSql = "";
		$i = 0;
	}		
}
if (strlen($shareInputSql) > 0)
	mysql_query($shareInputSql);

//Remove counted shares from shares_history
mysql_query("DELETE FROM shares_history WHERE counted = '1' AND id <=".$maxId);	
?>