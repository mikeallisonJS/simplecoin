<?php
//    Copyright (C) 2011  Mike Allison
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
if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] != "127.0.0.1") {
	echo "cronjobs can only be run locally.";
	exit;
}

$includeDirectory = "/var/www/includes/";

include($includeDirectory."requiredFunctions.php");

//Hashrate by worker
$sql =  "SELECT IFNULL(sum(a.id),0) as id, p.username FROM pool_worker p LEFT JOIN ".
			"((SELECT count(id) as id, username ". 
			"FROM shares ". 
			"WHERE time > DATE_SUB(now(), INTERVAL 10 MINUTE) ".
			"GROUP BY username) ".
		"UNION ". 
			"(SELECT count(id) as id, username ". 
			"FROM shares_history ". 
			"WHERE time > DATE_SUB(now(), INTERVAL 10 MINUTE) ". 
			"GROUP BY username)) a ".
		"ON p.username=a.username ".
		"GROUP BY username";
$result = mysql_query($sql);
while ($resultrow = mysql_fetch_object($result)) {
	$hashrate = $resultrow->id;
	$hashrate = round((($hashrate*4294967296)/600)/1000000, 0);
	mysql_query("UPDATE pool_worker SET hashrate = $hashrate WHERE username = '$resultrow->username'");
}

//Total Hashrate (more exact than adding)
$sql =  "SELECT sum(a.id) as id FROM ".
			"((SELECT count(id) as id FROM shares WHERE time > DATE_SUB(now(), INTERVAL 10 MINUTE)) ".
		"UNION ". 
			"(SELECT count(id) as id FROM shares_history WHERE time > DATE_SUB(now(), INTERVAL 10 MINUTE)) ". 
			") a ";
$result = mysql_query($sql);
if ($resultrow = mysql_fetch_object($result)) {
	$hashrate = $resultrow->id;
	$hashrate = round((($hashrate*4294967296)/600)/1000000, 0);	
	mysql_query("UPDATE settings SET value = '$hashrate' WHERE setting='currenthashrate'");
}

//Hashrate by user
$sql = "SELECT u.id, IFNULL(sum(p.hashrate),0) as hashrate ".
		"FROM webUsers u LEFT JOIN pool_worker p ". 
		"ON p.associatedUserId = u.id ".
		"GROUP BY id";
$result = mysql_query($sql);
while ($resultrow = mysql_fetch_object($result)) {
	mysql_query("UPDATE webUsers SET hashrate = $resultrow->hashrate WHERE id = $resultrow->id");
	mysql_query("INSERT INTO userHashrates (userId, hashrate) VALUES ($resultrow->id, $resultrow->hashrate)");
}

$currentTime = time();
mysql_query("update settings set value='$currentTime' where setting='statstime'");
	
?>