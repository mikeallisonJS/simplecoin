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

if (!isset($_GET["api_key"]))
	exit;
	
$includeDirectory = "/var/www/includes/";

include($includeDirectory."requiredFunctions.php");

class User {
	var $confirmed_rewards = null;
	var $hashrate = null;	
	var $payout_history = null;
	var $workers = array();		
}

class Worker {	
	var $alive = null;
	var $hashrate = null;	
}
	
connectToDb();
$apikey = $_GET["api_key"];

$user = new User();

$resultU = mysql_query("SELECT u.id, u.hashrate, b.balance, b.paid from webUsers u, accountBalance b WHERE u.id = b.userId AND u.api_key='".$apikey."'");
if ($userobj = mysql_fetch_object($resultU)){
	$userid = $userobj->id;
	$user->confirmed_rewards = $userobj->balance;
	$user->hashrate = $userobj->hashrate;
	$user->payout_history = $userobj->paid;
}
$resultW = mysql_query("SELECT username, hashrate, active FROM pool_worker WHERE associatedUserId=".$userid);
while ($workerobj = mysql_fetch_object($resultW)) {
	$worker = new Worker();
	$worker->alive = $workerobj->active;
	$worker->hashrate = $workerobj->hashrate;
	$user->workers[$workerobj->username] = $worker;
} 

echo json_encode($user);
//echo json_encode($workers);


?>