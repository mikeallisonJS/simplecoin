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
include($includeDirectory."stats.php");
$stats = new Stats();

class User {
	var $confirmed_rewards = null;
	var $hashrate = null;	
	var $payout_history = null;
	var $estimated_rewards = null;
	var $workers = array();		
}

class Worker {	
	var $alive = null;
	var $hashrate = null;	
}
	
$apikey = mysql_real_escape_string($_GET["api_key"]);

$user = new User();
$userid = 0;
$resultU = mysql_query_cache("SELECT u.id, b.balance, b.paid, u.username from webUsers u, accountBalance b WHERE u.id = b.userId AND u.api_key = '$apikey'", 300);
if (count($resultU) > 0) {
	$userobj = $resultU[0];
	$username = $userobj->username;
	$userid = $userobj->id;
	$user->confirmed_rewards = $userobj->balance;
	$user->hashrate = $stats->userhashrate($username);
	$user->estimated_rewards = 0;
	$user->payout_history = $userobj->paid;
	
	if (is_numeric($settings->getsetting("sitepercent")))
		$sitePercent = $settings->getsetting("sitepercent");
	
	$totalUserShares = $stats->usersharecount($userid);
		
	//Get current round share information, estimated total earnings
	$totalOverallShares = $stats->currentshares();
		
	//Prevent divide by zero
	if($totalUserShares > 0 && $totalOverallShares > 0){
		//Get site percentage
		$sitePercent = 0;
		if (is_numeric($settings->getsetting('sitepercent')))
			$sitePercent = $settings->getsetting('sitepercent')/100;						
		
		if ($totalOverallShares > $bitcoinDifficulty)
			$estimatedTotalEarnings = $totalUserShares/$totalOverallShares;
		else
			$estimatedTotalEarnings = $totalUserShares/$bitcoinDifficulty;
		
		$estimatedTotalEarnings *= $bonusCoins*(1-$sitePercent); //The expected BTC to be givin out
		$user->estimated_rewards = round($estimatedTotalEarnings, 8);
		}
	
}
$resultW = mysql_query_cache("SELECT username FROM pool_worker WHERE associatedUserId = $userid", 300);
foreach ($resultW as $workerobj) {
	$worker = new Worker();
	$worker->alive = ($stats->workerhashrate($workerobj->username) > 0);
	$worker->hashrate = $stats->workerhashrate($workerobj->username);
	$user->workers[$workerobj->username] = $worker;
} 

echo json_encode($user);

?>