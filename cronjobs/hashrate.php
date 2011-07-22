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

$includeDirectory = "/var/www/includes/";

include($includeDirectory."requiredFunctions.php");

//Check that script is run locally
ScriptIsRunLocally();

//Hashrate by worker	
removeCache("worker_hashrates");	

//Total Hashrate (more exact than adding) (just flush stats so it is rebuilt)
removeCache("pool_hashrate");


//Hashrate by user
removeCache("user_hashrates");
$sql = "INSERT INTO userHashrates (userId, hashrate) ".
	 	"SELECT u.id as userId, IFNULL(sum(p.hashrate),0) as hashrate ".
		"FROM webUsers u LEFT JOIN pool_worker p ". 
		"ON p.associatedUserId = u.id ".
		"GROUP BY u.id";
mysql_query($sql);

$currentTime = time();
$settings->setsetting("statstime", $currentTime);
	
?>